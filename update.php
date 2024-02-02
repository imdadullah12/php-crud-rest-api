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
                        // Commit the transaction if everything is successful
                        $conn->commit();
                        http_response_code(200); // OK
                        echo json_encode(['status' => 'success', 'message' => 'Data updated successfully']);
                    } else {
                        throw new Exception('Update query failed');
                    }

                    // Close the database connection
                    $conn->close();
                } else {
                    // Return an error if the 'conditions' parameter is missing
                    throw new Exception('Missing required parameter: conditions');
                }
            } else {
                // Return an error if the 'data' parameter is missing
                throw new Exception('Missing required parameter: data');
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
