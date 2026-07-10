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
     * Get all staff members
     */
    public function getAllStaff()
    {
        $sql = "SELECT id, staff_id, first_name, last_name, email, department, date_joined, status 
                FROM staff 
                ORDER BY last_name ASC, first_name ASC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find a specific staff member by their internal database primary key ID
     */
    public function getStaffById($id)
    {
        $sql = "SELECT id, staff_id, first_name, last_name, email, department, date_joined, status 
                FROM staff 
                WHERE id = :id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Register a new staff member into the system
     */
    public function addStaff($staffId, $firstName, $lastName, $email, $department, $dateJoined)
    {
        $sql = "INSERT INTO staff (staff_id, first_name, last_name, email, department, date_joined) 
                VALUES (:staff_id, :first_name, :last_name, :email, :department, :date_joined)";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':staff_id', $staffId, PDO::PARAM_STR);
        $stmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':department', $department, PDO::PARAM_STR);
        $stmt->bindParam(':date_joined', $dateJoined, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    /**
     * Update an employee's details or structural status (Active/Inactive)
     */
    public function updateStaff($id, $staffId, $firstName, $lastName, $email, $department, $status)
    {
        $sql = "UPDATE staff 
                SET staff_id = :staff_id, first_name = :first_name, last_name = :last_name, 
                    email = :email, department = :department, status = :status 
                WHERE id = :id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':staff_id', $staffId, PDO::PARAM_STR);
        $stmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':department', $department, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        
        return $stmt->execute();
    }
}