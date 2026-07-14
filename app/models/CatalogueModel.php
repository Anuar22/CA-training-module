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
     * Fetches all training modules dynamically
     */
    public function getAllSystems()
    {
        $sql = "SELECT * FROM training_definitions";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        
        $processed = [];
        foreach ($rows as $row) {
            $id = $row['training_id'] ?? array_values($row)[0];
            $name = $row['training_name'] ?? $row['name'] ?? $row['title'] ?? 'System Module #' . $id;
            
            $processed[] = [
                'id' => $id,
                'system_name' => $name,
                'is_mandatory' => $row['is_mandatory'] ?? false
            ];
        }
        
        return $processed;
    }

    /**
     * Extracts allowed values from a PostgreSQL CHECK constraint definition safely without buggy regexes
     */
    public function getAllowedConstraintValues($columnName)
    {
        try {
            $sql = "SELECT pg_get_constraintdef(pg_constraint.oid) AS cons_def 
                    FROM pg_constraint 
                    JOIN pg_class ON pg_class.oid = pg_constraint.conrelid 
                    WHERE pg_class.relname = 'training_definitions' 
                      AND pg_get_constraintdef(pg_constraint.oid) LIKE :col_match";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':col_match', '%' . $columnName . '%', PDO::PARAM_STR);
            $stmt->execute();
            $constraint = $stmt->fetch();

            if ($constraint) {
                $def = $constraint['cons_def'];
                // Clean up string: extract everything between quotes safely
                preg_match_all("/'([^']+)'/", $def, $matches);
                if (!empty($matches[1])) {
                    // Filter out the column name itself if it got matched
                    return array_filter($matches[1], function($val) use ($columnName) {
                        return $val !== $columnName;
                    });
                }
            }
        } catch (Exception $e) {
            // Fail silently and return fallbacks
        }
        
        // Solid fallbacks based on your project requirements
        return $columnName === 'category' 
            ? ['Technical/Operations', 'Compliance', 'Administration', 'HR & Payroll'] 
            : ['Mandatory', 'Recommended', 'Optional'];
    }

    /**
     * Registers a new system matching the table layout perfectly
     */
    public function registerSystem($systemName, $isMandatory, $category, $requirementType)
    {
        $colsStmt = $this->db->prepare("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = 'training_definitions'
        ");
        $colsStmt->execute();
        $columns = $colsStmt->fetchAll(PDO::FETCH_COLUMN);

        $nameColumn = 'system_name';
        foreach (['training_name', 'name', 'title', 'system_name'] as $candidate) {
            if (in_array($candidate, $columns)) {
                $nameColumn = $candidate;
                break;
            }
        }

        $mandatoryColumn = in_array('is_mandatory', $columns) ? 'is_mandatory' : (in_array('mandatory', $columns) ? 'mandatory' : null);

        // Build dynamically
        $insertCols = [$nameColumn];
        $insertVals = [':system_name'];

        if ($mandatoryColumn) {
            $insertCols[] = $mandatoryColumn;
            $insertVals[] = ':is_mandatory';
        }
        if (in_array('category', $columns)) {
            $insertCols[] = 'category';
            $insertVals[] = ':category';
        }
        if (in_array('requirement_type', $columns)) {
            $insertCols[] = 'requirement_type';
            $insertVals[] = ':requirement_type';
        }

        $sql = "INSERT INTO training_definitions (" . implode(', ', $insertCols) . ") 
                VALUES (" . implode(', ', $insertVals) . ")";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':system_name', $systemName, PDO::PARAM_STR);
        
        if ($mandatoryColumn) {
            $stmt->bindValue(':is_mandatory', $isMandatory ? true : false, PDO::PARAM_BOOL);
        }
        if (in_array('category', $columns)) {
            $stmt->bindValue(':category', $category, PDO::PARAM_STR);
        }
        if (in_array('requirement_type', $columns)) {
            $stmt->bindValue(':requirement_type', $requirementType, PDO::PARAM_STR);
        }

        return $stmt->execute();
    }
}