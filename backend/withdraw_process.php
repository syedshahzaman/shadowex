<?php
// File paths
$walletFile = 'user_wallet.csv';
$withdrawalFile = 'withdrawal_requests.csv';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Extract input data
    $method = $input['method'] ?? '';
    $idPhone = $input['id_phone'] ?? ''; // New ID Phone Number field
    $phone = $input['phone_number'] ?? ''; // For "Pay to Phone Number" method
    $bankAccount = $input['bank_account'] ?? ''; // For "Bank Transfer" method
    $accountHolder = $input['account_holder'] ?? '';
    $ifscCode = $input['ifsc_code'] ?? '';
    $amount = (float)($input['amount'] ?? 0);
    $timestamp = date('Y-m-d H:i:s');
    $status = 'Pending';

    // Validate inputs
    if (
        empty($idPhone) || 
        empty($amount) || 
        $amount <= 0 || 
        ($method === 'bank-transfer' && (empty($bankAccount) || empty($accountHolder) || empty($ifscCode))) || 
        ($method === 'pay-to-number' && empty($phone))
    ) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
        exit;
    }

    // Read user wallet to check balance
    $walletRows = [];
    $sufficientBalance = false;

    if (($file = fopen($walletFile, 'r')) !== false) {
        while (($row = fgetcsv($file)) !== false) {
            if ($row[0] === $idPhone) {
                $balance = (float)$row[1];
                if ($amount > $balance) {
                    echo json_encode(['success' => false, 'message' => 'Insufficient wallet balance.']);
                    fclose($file);
                    exit;
                }
                if ($amount > $balance * 0.75) {
                    echo json_encode(['success' => false, 'message' => 'Withdrawal exceeds 75% of balance.']);
                    fclose($file);
                    exit;
                }
                $sufficientBalance = true;
                $row[1] = $balance - $amount; // Deduct amount
            }
            $walletRows[] = $row;
        }
        fclose($file);
    }

    if (!$sufficientBalance) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    // Write updated wallet balance
    if (($file = fopen($walletFile, 'w')) !== false) {
        foreach ($walletRows as $row) {
            fputcsv($file, $row);
        }
        fclose($file);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unable to update wallet file.']);
        exit;
    }

    // Add withdrawal request to the file
    $data = [
        $timestamp,
        $method,
        $idPhone,
        $bankAccount,
        $accountHolder,
        $ifscCode,
        $phone,
        $amount,
        $status
    ];

    if (($file = fopen($withdrawalFile, 'a')) !== false) {
        if (fputcsv($file, $data) !== false) {
            fclose($file);
            echo json_encode(['success' => true, 'message' => 'Withdrawal request submitted successfully.']);
        } else {
            fclose($file);
            echo json_encode(['success' => false, 'message' => 'Failed to write withdrawal request.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Unable to open withdrawal request file.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
