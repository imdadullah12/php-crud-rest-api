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
        // Check if the 'data' parameter is present
        if (isset($requestData['data'])) {
            //Check if the 'conditions' parameter is present
            if (isset($requestData['conditions'])) {
                // Build the SQL query based on the request parameters
                $table = $requestData['table'];
                $dataToUpdate = $requestData['data'][0];
                $conditions = isset($requestData['conditions']) ? buildWhereClause($requestData['conditions']) : '';

                $updateValues = [];
                foreach ($dataToUpdate as $column => $value) {
                    $updateValues[] = "$column = '$value'";
                }

                $setValues = implode(', ', $updateValues);

                $sql = "UPDATE $table SET $setValues $conditions";

                // Execute the query
                $result = $conn->query($sql);

                // Check if the query was successful
                if ($result) {
                    http_response_code(200); // OK
                    echo json_encode(['status' => 'success', 'message' => 'Data updated successfully']);
                } else {
                    // Return an error message
                    http_response_code(500); // Internal Server Error
                    echo json_encode(['status' => 'error', 'message' => 'Update query failed']);
                }

                // Close the database connection
                $conn->close();
            } else {
                // Return an error if the 'conditions' parameter is missing
                http_response_code(400); // Bad Request
                echo json_encode(['status' => 'error', 'message' => 'Missing required parameter: conditions']);
            }
        } else {
            // Return an error if the 'data' parameter is missing
            http_response_code(400); // Bad Request
            echo json_encode(['status' => 'error', 'message' => 'Missing required parameter: data']);
        }
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
