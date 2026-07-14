<?php
// app/models/StaffModel.php

require_once dirname(__DIR__) . '/config/database.php';

class StaffModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Inserts or updates a staff record from the CSV bulk data array
     */
    public function upsertStaff($staffId, $fullName, $department, $status)
    {
        // Added full_name to satisfy the database NOT NULL constraint
        $sql = "INSERT INTO staff (staff_id, full_name, department, status) 
                VALUES (:staff_id, :full_name, :department, :status)
                ON CONFLICT (staff_id) 
                DO UPDATE SET 
                    full_name = EXCLUDED.full_name,
                    department = EXCLUDED.department,
                    status = EXCLUDED.status";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':staff_id', $staffId, PDO::PARAM_INT);
        $stmt->bindParam(':full_name', $fullName, PDO::PARAM_STR);
        $stmt->bindParam(':department', $department, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        return $stmt->execute();
    }
}