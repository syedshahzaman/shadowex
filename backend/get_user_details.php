<?php
session_start();

// Assuming user is logged in and phone number is stored in session
$phone = $_SESSION['user_phone'] ?? '';
$walletFile = 'user_wallet.csv';

if (empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

// Fetch user details from wallet
if (($file = fopen($walletFile, 'r')) !== false) {
    while (($row = fgetcsv($file)) !== false) {
        if ($row[0] === $phone) {
            echo json_encode([
                'success' => true,
                'phone' => $row[0],
                'balance' => $row[1],
            ]);
            fclose($file);
            exit;
        }
    }
    fclose($file);
}

echo json_encode(['success' => false, 'message' => 'User not found.']);
?>
