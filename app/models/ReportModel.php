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
     * Get a comprehensive timeline view of all training sessions executed
     */
    public function getGlobalTrainingLog()
    {
        $sql = "SELECT ts.session_date, tc.system_name, ts.trainer_name,
                       COUNT(CASE WHEN att.status = 'Attended' THEN 1 END) as attended_count,
                       ROUND(AVG(att.score), 1) as average_score
                FROM training_sessions ts
                JOIN training_catalogue tc ON ts.catalogue_id = tc.id
                LEFT JOIN attendance att ON ts.id = att.session_id
                GROUP BY ts.id, ts.session_date, tc.system_name, ts.trainer_name
                ORDER BY ts.session_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Look up an individual staff member's historical lifetime training matrix
     */
    public function getStaffTrainingHistory($staffTableId)
    {
        $sql = "SELECT tc.system_name, ts.session_date, ts.trainer_name, 
                       att.status, att.score, att.remarks
                FROM attendance att
                JOIN training_sessions ts ON att.session_id = ts.id
                JOIN training_catalogue tc ON ts.catalogue_id = tc.id
                WHERE att.staff_id = :staff_id
                ORDER BY ts.session_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':staff_id', $staffTableId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}