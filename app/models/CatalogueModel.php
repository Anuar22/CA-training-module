<?php
// app/models/CatalogueModel.php

require_once dirname(__DIR__) . '/config/database.php';

class CatalogueModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Fetch all registered corporate systems
     */
    public function getAllSystems()
    {
        $sql = "SELECT id, system_name, description, version_tracked, created_at 
                FROM training_catalogue 
                ORDER BY system_name ASC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find a specific system by its ID
     */
    public function getSystemById($id)
    {
        $sql = "SELECT id, system_name, description, version_tracked, created_at 
                FROM training_catalogue 
                WHERE id = :id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Add a new corporate system to the training registry
     */
    public function addSystem($systemName, $description, $versionTracked)
    {
        $sql = "INSERT INTO training_catalogue (system_name, description, version_tracked) 
                VALUES (:system_name, :description, :version_tracked)";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':system_name', $systemName, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':version_tracked', $versionTracked, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    /**
     * Update an existing system's details
     */
    public function updateSystem($id, $systemName, $description, $versionTracked)
    {
        $sql = "UPDATE training_catalogue 
                SET system_name = :system_name, description = :description, version_tracked = :version_tracked 
                WHERE id = :id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':system_name', $systemName, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':version_tracked', $versionTracked, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    /**
     * Delete a system from the registry
     */
    public function deleteSystem($id)
    {
        $sql = "DELETE FROM training_catalogue WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}