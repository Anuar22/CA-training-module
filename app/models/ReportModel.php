<?php
// app/models/ReportModel.php

require_once dirname(__DIR__) . '/config/database.php';

class ReportModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Retrieves all active staff members along with a count of their missing mandatory trainings
     */
    public function getComplianceRoster()
    {
        // 1. Dynamically scan table columns to find the exact primary key and mandatory flags
        $colsStmt = $this->db->prepare("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = 'training_definitions'
        ");
        $colsStmt->execute();
        $tdColumns = $colsStmt->fetchAll(PDO::FETCH_COLUMN);

        // Find correct ID column (training_id or id)
        $idCol = in_array('training_id', $tdColumns) ? 'training_id' : 'id';

        // Find correct mandatory tracking column (is_mandatory or mandatory)
        $mandatoryCol = null;
        foreach (['is_mandatory', 'mandatory'] as $candidate) {
            if (in_array($candidate, $tdColumns)) {
                $mandatoryCol = $candidate;
                break;
            }
        }

        // Build the where clause based on what column actually exists
        $whereClause = "1=1";
        if ($mandatoryCol) {
            $whereClause = "tc.$mandatoryCol = TRUE";
        } elseif (in_array('requirement_type', $tdColumns)) {
            // Fallback: Use requirement type if no boolean column exists
            $whereClause = "tc.requirement_type = 'Mandatory'";
        }

        // 2. Execute the dynamic query with verified column names
        $sql = "SELECT 
                    s.staff_id AS unique_code,
                    s.department,
                    (
                        SELECT COUNT(*) 
                        FROM training_definitions tc 
                        WHERE $whereClause
                        AND tc.$idCol NOT IN (
                            SELECT ts.training_id 
                            FROM attendance att
                            JOIN training_sessions ts ON att.session_id = ts.session_id
                            WHERE att.staff_id = s.staff_id 
                              AND UPPER(att.status) = 'PRESENT'
                        )
                    ) AS missing_mandatory_count
                FROM staff s
                WHERE s.status = 'Active'
                ORDER BY missing_mandatory_count DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Retrieves complete chronological historical profile data for an individual staff member
     */
    public function getStaffTrainingHistory($staffId)
    {
        // 1. Dynamically scan table columns
        $colsStmt = $this->db->prepare("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = 'training_definitions'
        ");
        $colsStmt->execute();
        $tdColumns = $colsStmt->fetchAll(PDO::FETCH_COLUMN);

        // Find correct ID column
        $idCol = in_array('training_id', $tdColumns) ? 'training_id' : 'id';

        // Find correct name column (training_name, name, title, etc.)
        $nameColumn = 'system_name';
        foreach (['training_name', 'name', 'title', 'system_name'] as $candidate) {
            if (in_array($candidate, $tdColumns)) {
                $nameColumn = $candidate;
                break;
            }
        }

        // 2. Execute dynamic history fetch
        $sql = "SELECT 
                    tc.$nameColumn AS system_name, 
                    ts.session_date, 
                    ts.venue, 
                    att.status, 
                    att.absence_reason, 
                    ts.attachment_path
                FROM attendance att
                JOIN training_sessions ts ON att.session_id = ts.session_id
                JOIN training_definitions tc ON ts.training_id = tc.$idCol
                WHERE att.staff_id = :staff_id
                ORDER BY ts.session_date DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':staff_id', $staffId, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}