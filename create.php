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
        // Validate data if validation rules are provided
        if (isset($requestData['validation']) && isset($requestData['data'])) {
            $validationResult = validateData($requestData['data'][0], $requestData['validation'][0], $conn, $requestData['table']);
            if ($validationResult !== null) {
                http_response_code(400); // Bad Request
                echo json_encode(['status' => 'error', 'message' => $validationResult]);
                exit;
            }
        }

        // Insert data if 'data' parameter is present
        if (isset($requestData['data'])) {
            $table = $requestData['table'];
            $columns = implode(',', array_keys($requestData['data'][0]));
            $values = [];
            foreach ($requestData['data'] as $item) {
                $values[] = "'" . implode("','", $item) . "'";
            }
            $valuesString = implode('),(', $values);

            $sql = "INSERT INTO $table ($columns) VALUES ($valuesString)";

            // Execute the query
            $result = $conn->query($sql);

            // Check if the query was successful
            if ($result) {
                http_response_code(201); // Created
                echo json_encode(['status' => 'success', 'message' => 'Data inserted successfully']);
            } else {
                http_response_code(500); // Internal Server Error
                echo json_encode(['status' => 'error', 'message' => 'Query failed']);
            }
        } else {
            // Return an error if the 'data' parameter is missing
            http_response_code(400); // Bad Request
            echo json_encode(['status' => 'error', 'message' => 'Missing required parameter: data']);
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
