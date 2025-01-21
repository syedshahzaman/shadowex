<?php
// File paths for user data and wallet data
$csvFile = 'users.csv';
$walletFile = 'user_wallet.csv';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $phone = htmlspecialchars(trim($_POST['phone']));
    $password = htmlspecialchars(trim($_POST['password']));
    $confirmPassword = htmlspecialchars(trim($_POST['confirm-password']));

    // Error checking
    if (empty($phone) || empty($password) || empty($confirmPassword)) {
        echo "All fields are required.";
        exit;
    }

    if ($password !== $confirmPassword) {
        echo "Passwords do not match.";
        exit;
    }

    if (!is_numeric($phone) || strlen($phone) < 10) {
        echo "Invalid phone number.";
        exit;
    }

    // Hash the password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if users.csv exists; if not, create it and add a header row
    if (!file_exists($csvFile)) {
        $header = ["Phone", "Password"];
        $file = fopen($csvFile, 'w');
        fputcsv($file, $header);
        fclose($file);
    }

    // Check if the phone number is already registered
    $file = fopen($csvFile, 'r');
    while (($data = fgetcsv($file)) !== false) {
        if ($data[0] === $phone) {
            fclose($file);
            echo "Phone number already registered.";
            exit;
        }
    }
    fclose($file);

    // Save the new user to the users.csv file
    $file = fopen($csvFile, 'a');
    $newUser = [$phone, $hashedPassword];
    fputcsv($file, $newUser);
    fclose($file);

    // Check if user_wallet.csv exists; if not, create it and add a header row
    if (!file_exists($walletFile)) {
        $header = ["Phone", "Balance", "Tokens", "Avg_Buy_Price"];
        $walletFileHandle = fopen($walletFile, 'w');
        fputcsv($walletFileHandle, $header);
        fclose($walletFileHandle);
    }

    // Add the new user to the user_wallet.csv file with default values
    $walletFileHandle = fopen($walletFile, 'a');
    $newWalletEntry = [$phone, 0.0, 0.0, 0.0]; // Default balance, tokens, avg buy price
    fputcsv($walletFileHandle, $newWalletEntry);
    fclose($walletFileHandle);

    echo "Sign up successful! You can now <a href='../web/login.html'>log in</a>.";
    exit;
}
?>
