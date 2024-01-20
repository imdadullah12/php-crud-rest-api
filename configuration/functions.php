<?php
/*
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
!! WARNING: Do not modify this file! Any changes may cause errors !!  
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

*******************************************************************
* This script was developed by Imdadullah Babu                    *
* Website: https://imdos.in                                       *  
* Organization: Pen Programmer (https://penprogrammer.com)        *
*******************************************************************
*/

// Function to build the WHERE clause of the SQL query
function buildWhereClause($whereConditions)
{
    $where = 'WHERE ';
    $conditions = array();

    foreach ($whereConditions as $condition) {
        $on = $condition['on'];
        $type = $condition['type'];
        $value = $condition['value'];

        // Handle IN condition separately
        if (strtoupper($type) === 'IN') {
            $conditions[] = "$on $type $value";
        } else {
            $conditions[] = "$on $type '$value'";
        }
    }

    return $where . implode(' AND ', $conditions);
}

// Function to validate data
function validateData($data, $validationRules, $conn, $table)
{
    foreach ($validationRules as $key => $rules) {
        $value = isset($data[$key]) ? $data[$key] : null;
        $rules = explode('|', $rules);

        foreach ($rules as $rule) {
            if ($rule === 'required' && empty($value)) {
                return "Field '$key' is required.";
            } elseif ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return "Invalid email format for field '$key'.";
            } elseif ($rule === 'numeric' && !is_numeric($value)) {
                return "Field '$key' must be numeric.";
            } elseif (strpos($rule, 'min-length:') === 0) {
                if (preg_match('/min-length:(\d+)/', $rule, $matches)) {
                    $minLength = intval($matches[1]);
                    if (strlen($value) < $minLength) {
                        return "Field '$key' must be at least $minLength characters long.";
                    }
                }
            } elseif (strpos($rule, 'max-length:') === 0) {
                if (preg_match('/max-length:(\d+)/', $rule, $matches)) {
                    $maxLength = intval($matches[1]);
                    if (strlen($value) > $maxLength) {
                        return "Field '$key' must be at most $maxLength characters long.";
                    }
                }
            } elseif (strpos($rule, 'length:') === 0) {
                if (preg_match('/length:(\d+)/', $rule, $matches)) {
                    $exactLength = intval($matches[1]);
                    if (strlen($value) !== $exactLength) {
                        return "Field '$key' must be exactly $exactLength characters long.";
                    }
                }
            } elseif ($rule === 'unique') {
                $result = $conn->query("SELECT $key FROM $table WHERE $key = '$value'");
                if ($result && $result->num_rows > 0) {
                    return "Field '$key' must be unique.";
                }
            }
        }
    }
    return null;
}
