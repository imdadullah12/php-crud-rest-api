<?php
require('configuration/connection.php');
require('configuration/functions.php');
require('configuration/custom-functions.php');

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

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Begin a database transaction
    $conn->autocommit(false);

    try {
        // Get the JSON data from the request body
        $requestData = json_decode(file_get_contents('php://input'), true);

        // Check if the required parameters are present
        if (isset($requestData['table'])) {
            // Build the SQL query based on the request parameters
            $table = $requestData['table'];
            $select = isset($requestData['select']) ? implode(',', $requestData['select']) : '*';

            // Handle JOIN clauses
            $join = '';
            if (isset($requestData['join'])) {
                foreach ($requestData['join'] as $joinClause) {
                    $joinTable = $joinClause['table'];
                    $joinOn = $joinClause['on'];
                    $joinType = strtoupper($joinClause['type']);
                    $join .= " $joinType JOIN $joinTable ON $joinOn[0] = $joinOn[1]";
                }
            }

            $conditions = isset($requestData['conditions']) ? buildWhereClause($requestData['conditions']) : '';
            $rawConditions = isset($requestData['rawConditions']) ? implode(' ', $requestData['rawConditions']) : '';

            if ($conditions && $rawConditions) {
                throw new Exception('You cannot pass both conditions or rawConditions in the request, use only one of them');
            }

            $limit = isset($requestData['limit']) ? 'LIMIT ' . $requestData['limit'] : '';
            $order = isset($requestData['order']) ? 'ORDER BY ' . $requestData['order']['on'] . ' ' . strtoupper($requestData['order']['type']) : '';

            $sql = "SELECT $select FROM $table $join $conditions $rawConditions $order $limit";

            // Execute the query
            $result = $conn->query($sql);

            // Check if the query was successful
            if ($result) {
                // Fetch and return the data as JSON
                $data = $result->fetch_all(MYSQLI_ASSOC);
                http_response_code(200); // OK
                echo json_encode(['status' => 'success', 'data' => $data]);

                // Commit the transaction if everything is successful
                $conn->commit();
            } else {
                // Return an error message
                throw new Exception('Query failed');
            }
        } else {
            // Return an error if the 'table' parameter is missing
            throw new Exception('Missing required parameter: table');
        }
    } catch (Exception $e) {
        // Rollback the transaction on any exception
        $conn->rollback();
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Internal Server Error: ' . $e->getMessage()]);
    } finally {
        // Enable autocommit after the try-catch block
        $conn->autocommit(true);
        // Close the database connection
        $conn->close();
    }
} else {
    // Return an error if the request method is not POST
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
