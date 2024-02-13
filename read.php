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

    // Check if 'select' parameter exists in the request data
    if (!isset($requestData['select'])) {
        throw new Exception('Missing required parameters: select');
    }

    // Prepare SQL query for selection
    $table = $requestData['table'];
    $select = implode(',', $requestData['select']);
    $join = '';
    if (isset($requestData['join'])) {
        foreach ($requestData['join'] as $joinClause) {
            $joinTable = $joinClause['table'];
            $joinOn = $joinClause['on'];
            $joinType = strtoupper($joinClause['type']);
            $join .= " $joinType JOIN $joinTable ON $joinOn[0] = $joinOn[1]";
        }
    }

    // Check if both conditions passed, build where conditions
    $conditions = isset($requestData['conditions']) ? buildWhereClause($requestData['conditions']) : '';
    $rawConditions = isset($requestData['rawConditions']) ? implode(' ', $requestData['rawConditions']) : '';

    // Check if both conditions and rawConditions are passed in the request
    if ($conditions && $rawConditions) {
        throw new Exception('You cannot pass both conditions or rawConditions in the request, use only one of them');
    }

    // Check if limit and order passed in the request or not
    $limit = isset($requestData['limit']) ? 'LIMIT ' . $requestData['limit'] : '';
    $order = isset($requestData['order']) ? 'ORDER BY ' . $requestData['order']['on'] . ' ' . strtoupper($requestData['order']['type']) : '';

    $sql = "SELECT $select FROM $table $join $conditions $rawConditions $order $limit";

    // Execute the query
    $result = $conn->query($sql);

    // If query executed successfully, commit transaction and return success response
    if ($result) {
        // Fetch and return the data as JSON
        $data = $result->fetch_all(MYSQLI_ASSOC);
        http_response_code(200); // OK
        echo json_encode(['status' => 'success', 'data' => $data]);

        // Commit the transaction if everything is successful
        $conn->commit();
    } else {
        // If query failed, throw an exception
        throw new Exception('Query failed');
    }
} catch (Exception $e) {
    // Rollback the transaction on any exception
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    // Close the database connection
    $conn->close();
}
