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

// Check if the request method is POST and content type is JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] !== 'application/json') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method or content type']);
    exit;
}

// Disable autocommit for database transactions
$conn->autocommit(false);

try {
    // Decode JSON data from request body
    $requestData = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);

    // Check if 'table' parameter exists in the request data
    if (!isset($requestData['table'])) {
        throw new Exception('Missing required parameters: table');
    }

    // Check if 'data' parameter exists in the request data
    if (!isset($requestData['data'])) {
        throw new Exception('Missing required parameters: data');
    }

    // Perform validation if 'validation' parameter exists in the request data
    if (isset($requestData['validation'])) {
        $validationResult = validateData($requestData['data'][0], $requestData['validation'][0], $conn, $requestData['table']);
        if ($validationResult !== null) {
            throw new Exception($validationResult);
        }
    }

    // Prepare SQL query for insertion
    $table = $requestData['table'];
    $columns = implode(',', array_keys($requestData['data'][0]));
    $values = [];
    foreach ($requestData['data'] as $item) {
        $values[] = "'" . implode("','", $item) . "'";
    }
    $valuesString = implode('),(', $values);
    $sql = "INSERT INTO $table ($columns) VALUES ($valuesString)";

    // Execute the SQL query
    $result = $conn->query($sql);

    // If query executed successfully, commit transaction and return success response
    if ($result) {
        $conn->commit();
        http_response_code(201);
        echo json_encode(['status' => 'success', 'message' => 'Data inserted successfully']);
    } else {
        // If query failed, throw an exception
        throw new Exception('Query failed');
    }
} catch (Exception $e) {
    // Rollback transaction in case of exception and return error response
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    // Close database connection
    $conn->close();
}
