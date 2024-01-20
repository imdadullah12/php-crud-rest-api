<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST");

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
    if (isset($_POST['destination'])) {
        if (isset($_POST['validation'])) {
            $response = [];
            $allowedExtensions = explode(',', $_POST['validation']);

            // Loop through all the FILES available in the request
            foreach ($_FILES as $key => $fileData) {
                // Get the file extension from the uploaded file
                $fileExt = pathinfo($fileData['name'], PATHINFO_EXTENSION);
                // Create a filename with the combination of current date and some random numbers
                $fileName = date('Ymd') . rand(11111, 99999) . '.' . $fileExt;

                // Check the extension to the validation array
                if (in_array($fileExt, $allowedExtensions)) {
                    // Create a directory if it is already not exist
                    if (!is_dir($_POST['destination'])) {
                        mkdir($_POST['destination'], 0777, true);
                    }
                    // Prepare the destination of the file
                    $destination = $_POST['destination'] . '/' . $fileName;
                    // Add the key of the file with the destination path
                    $response[$key] = $destination;
                    // Move the uploaded file to the destination
                    move_uploaded_file($fileData['tmp_name'], $destination);
                } else {
                    // Return an error if the 'validation' parameter is missing
                    http_response_code(400); // Bad Request
                    echo json_encode(['status' => 'error', 'message' => 'File must be in ' . $_POST['validation']]);
                    return;
                }
            }

            http_response_code(201); // File uploaded
            echo json_encode($response);
        } else {
            // Return an error if the 'validation' parameter is missing
            http_response_code(400); // Bad Request
            echo json_encode(['status' => 'error', 'message' => 'Missing required parameter: validation']);
        }
    } else {
        // Return an error if the 'data' parameter is missing
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameter: destination']);
    }
} else {
    // Return an error if the request method is not POST
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
