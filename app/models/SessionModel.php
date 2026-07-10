<?php
// app/models/SessionModel.php

require_once dirname(__DIR__) . '/config/database.php';

class SessionModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Create a new training session event
     */
    public function createSession($catalogueId, $trainerName, $sessionDate, $sessionTime, $locationOrLink, $notes)
    {
        $sql = "INSERT INTO training_sessions (catalogue_id, trainer_name, session_date, session_time, location_or_link, notes) 
                VALUES (:catalogue_id, :trainer_name, :session_date, :session_time, :location_or_link, :notes)";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':catalogue_id', $catalogueId, PDO::PARAM_INT);
        $stmt->bindParam(':trainer_name', $trainerName, PDO::PARAM_STR);
        $stmt->bindParam(':session_date', $sessionDate, PDO::PARAM_STR);
        $stmt->bindParam(':session_time', $sessionTime, PDO::PARAM_STR);
        $stmt->bindParam(':location_or_link', $locationOrLink, PDO::PARAM_STR);
        $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            // Return the auto-incremented ID of the created session so we can immediately log attendance for it
            return $this->db->connect()->lastInsertId();
        }
        return false;
    }

    /**
     * Fetch all scheduled sessions alongside their mapped system names
     */
    public function getAllSessions()
    {
        $sql = "SELECT ts.id, tc.system_name, ts.trainer_name, ts.session_date, ts.session_time, ts.location_or_link 
                FROM training_sessions ts
                JOIN training_catalogue tc ON ts.catalogue_id = tc.id
                ORDER BY ts.session_date DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}