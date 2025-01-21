<?php
// File paths
$depositFile = 'depositreq.csv';
$walletFile = 'user_wallet.csv';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Extract fields
    $transactionId = $input['transaction_id'] ?? '';
    $amount = $input['amount'] ?? '';
    $upiId = $input['upi_id'] ?? '';
    $phone = $input['phone'] ?? '';

    // Validate input
    if (empty($transactionId) || empty($amount) || empty($upiId) || empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    // Store the deposit request in the CSV file
    $file = fopen($depositFile, 'a');
    if ($file) {
        $timestamp = date('Y-m-d H:i:s');
        fputcsv($file, [$transactionId, $amount, $upiId, $phone, 'Pending', $timestamp]);
        fclose($file);

        echo json_encode(['success' => true, 'message' => 'Deposit request submitted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unable to save deposit request.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
