<?php
// app/models/AttendanceModel.php (Updated)

require_once dirname(__DIR__) . '/config/database.php';

class AttendanceModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function logAttendance($sessionId, $staffId, $status, $score = null, $remarks = null, $absenceReason = null)
    {
        // Updated query handling ON CONFLICT updates for absence reasons
        $sql = "INSERT INTO attendance (session_id, staff_id, status, score, remarks, absence_reason) 
                VALUES (:session_id, :staff_id, :status, :score, :remarks, :absence_reason)
                ON CONFLICT (session_id, staff_id) 
                DO UPDATE SET status = EXCLUDED.status, 
                              score = EXCLUDED.score, 
                              remarks = EXCLUDED.remarks,
                              absence_reason = EXCLUDED.absence_reason";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_INT);
        $stmt->bindParam(':staff_id', $staffId, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        
        if ($score === null || $score === '') {
            $stmt->bindValue(':score', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':score', $score, PDO::PARAM_INT);
        }
        
        $stmt->bindParam(':remarks', $remarks, PDO::PARAM_STR);
        $stmt->bindParam(':absence_reason', $absenceReason, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    public function getAttendanceBySession($sessionId)
    {
        $sql = "SELECT att.id as attendance_id, s.id as staff_id, s.staff_id as unique_code, 
                       s.first_name, s.last_name, s.department,
                       COALESCE(att.status, 'Absent') as status, att.score, att.remarks, att.absence_reason
                FROM staff s
                LEFT JOIN attendance att ON s.id = att.staff_id AND att.session_id = :session_id
                WHERE s.status = 'Active'
                ORDER BY s.last_name ASC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}