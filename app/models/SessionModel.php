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
     * Creates a new training event record
     */
    public function createSession($trainingId, $sessionDate, $venue, $trainer, $notes, $attachmentPath = null)
    {
        $sql = "INSERT INTO training_sessions (training_id, session_date, venue, trainer, notes, attachment_path) 
                VALUES (:training_id, :session_date, :venue, :trainer, :notes, :attachment_path) 
                RETURNING session_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':training_id', $trainingId, PDO::PARAM_INT);
        $stmt->bindParam(':session_date', $sessionDate, PDO::PARAM_STR);
        $stmt->bindParam(':venue', $venue, PDO::PARAM_STR);
        $stmt->bindParam(':trainer', $trainer, PDO::PARAM_STR);
        $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
        $stmt->bindParam(':attachment_path', $attachmentPath, PDO::PARAM_STR);
        
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['session_id'] : false;
    }

    /**
     * Logs an individual staff attendance status matching the database constraints: PRESENT or ABSENT
     */
    public function logAttendance($sessionId, $staffId, $status, $absenceReason = '')
    {
        // Explicitly map inputs to uppercase database constraint values
        $dbStatus = (strtoupper($status) === 'ATTENDED' || strtoupper($status) === 'PRESENT') ? 'PRESENT' : 'ABSENT';

        $sql = "INSERT INTO attendance (session_id, staff_id, status, absence_reason) 
                VALUES (:session_id, :staff_id, :status, :absence_reason)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_INT);
        $stmt->bindParam(':staff_id', $staffId, PDO::PARAM_STR);
        $stmt->bindParam(':status', $dbStatus, PDO::PARAM_STR);
        
        // Convert empty reason strings to null for clean database tracking
        $reason = !empty($absenceReason) ? trim($absenceReason) : null;
        $stmt->bindParam(':absence_reason', $reason, PDO::PARAM_STR);
        
        return $stmt->execute();
    }
}