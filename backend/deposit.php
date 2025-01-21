<?php
session_start();

// CSV file paths
$userWalletFile = 'user_wallet.csv';
$depositHistoryFile = 'deposit_history.csv';

// Helper function to get user data by phone
function getUserData($phone) {
    global $userWalletFile;
    $users = array_map('str_getcsv', file($userWalletFile));
    foreach ($users as $user) {
        if ($user[0] === $phone) { // Match by phone
            return [
                'phone' => $user[0],
                'balance' => floatval($user[1]),
                'tokens' => floatval($user[2]),
                'avg_buy_price' => floatval($user[3]),
            ];
        }
    }
    return null; // User not found
}

// Helper function to update user wallet balance
function updateUserWallet($phone, $newBalance) {
    global $userWalletFile;
    $users = array_map('str_getcsv', file($userWalletFile));
    $updatedUsers = [];
    foreach ($users as $user) {
        if ($user[0] === $phone) {
            $user[1] = $newBalance; // Update balance
        }
        $updatedUsers[] = $user;
    }
    $file = fopen($userWalletFile, 'w');
    foreach ($updatedUsers as $user) {
        fputcsv($file, $user);
    }
    fclose($file);
}

// Handle Deposit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transactionId = $_POST['transaction-id'] ?? '';
    $amount = floatval($_POST['amount'] ?? 0);
    $phone = $_SESSION['phone'] ?? null;

    if (!$transactionId || $amount <= 0 || !$phone) {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        exit;
    }

    // Optional: Verify the transaction with GPay API
    // Example: Call payment gateway API to verify the transaction
    // $isVerified = verifyTransactionWithGPayAPI($transactionId, $amount);
    // if (!$isVerified) {
    //     echo json_encode(['success' => false, 'message' => 'Transaction verification failed.']);
    //     exit;
    // }

    // Update user wallet
    $user = getUserData($phone);
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    $newBalance = $user['balance'] + $amount;
    updateUserWallet($phone, $newBalance);

    // Append to deposit history
    $file = fopen($depositHistoryFile, 'a');
    fputcsv($file, [$transactionId, $amount, 'Completed', date('Y-m-d H:i:s')]);
    fclose($file);

    echo json_encode(['success' => true, 'balance' => $newBalance]);
}
?>
