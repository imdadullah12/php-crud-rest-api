<?php
/*
  This file contains the configuration for connecting to the database.
  It assumes MySQL is being used with the user "root" and an empty password.
*/

// Database connection parameters
define('DB_SERVER', 'YOUR_HOSTNAME');
define('DB_USERNAME', 'YOUR_USERNAME');
define('DB_PASSWORD', 'YOUR_PASSWORD');
define('DB_NAME', 'YOUR_DATABASE');

// Try connecting to the Database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Get the current datetime in the 'Asia/Kolkata' timezone
date_default_timezone_set('Asia/Kolkata');

// Check the connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
