<?php
session_start(); // Start the session

$file_path = 'users.csv';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($phone) || empty($password)) {
        echo "Phone number and password are required.";
        exit;
    }

    if (!file_exists($file_path)) {
        echo "No users registered yet.";
        exit;
    }

    if (($handle = fopen($file_path, 'r')) !== false) {
        $userFound = false;

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $storedPhone = $data[0] ?? '';
            $storedPasswordHash = $data[1] ?? '';

            if ($storedPhone === $phone && password_verify($password, $storedPasswordHash)) {
                $userFound = true;
                $_SESSION['phone'] = $storedPhone; // Store phone in session
                break;
            }
        }

        fclose($handle);

        if ($userFound) {
            echo "Login successful!";
        } else {
            echo "Invalid phone number or password.";
        }
    } else {
        echo "Unable to access user data.";
    }
} else {
    echo "Invalid request method.";
}
?>
