<?php
session_start();
include("connect.php");

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate form data
    $firstName = mysqli_real_escape_string($connect, trim($_POST['firstName']));
    $lastName = mysqli_real_escape_string($connect, trim($_POST['lastName']));
    $jobTitle = mysqli_real_escape_string($connect, trim($_POST['jobTitle']));
    $email = mysqli_real_escape_string($connect, trim($_POST['email']));
    $company = mysqli_real_escape_string($connect, trim($_POST['company']));
    $employees = mysqli_real_escape_string($connect, trim($_POST['employees']));
    $country = mysqli_real_escape_string($connect, trim($_POST['country']));
    $phone = mysqli_real_escape_string($connect, trim($_POST['phone']));

    // Insert query with exact column names from your table
    $sql = "INSERT INTO registrations (First_name, Last_name, Job_title, Email, Company, Phone, Employees, Country)
            VALUES ('$firstName', '$lastName', '$jobTitle', '$email', '$company', '$phone', '$employees', '$country')";
    
    if (mysqli_query($connect, $sql)) {
        
    } else {
        echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($connect)]);
    }
    
    // Close the database connection
    mysqli_close($connect);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}