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
        $limit = isset($requestData['limit']) ? 'LIMIT ' . $requestData['limit'] : '';
        $order = isset($requestData['order']) ? 'ORDER BY ' . $requestData['order']['on'] . ' ' . strtoupper($requestData['order']['type']) : '';

        $sql = "SELECT $select FROM $table $join $conditions $order $limit";

        // Execute the query
        $result = $conn->query($sql);

        // Check if the query was successful
        if ($result) {
            // Fetch and return the data as JSON
            $data = $result->fetch_all(MYSQLI_ASSOC);
            http_response_code(200); // OK
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            // Return an error message
            http_response_code(500); // Internal Server Error
            echo json_encode(['status' => 'error', 'message' => 'Query failed']);
        }

        // Close the database connection
        $conn->close();
    } else {
        // Return an error if the 'table' parameter is missing
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameter: table']);
    }
} else {
    // Return an error if the request method is not POST
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
