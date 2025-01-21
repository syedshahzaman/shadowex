<?php
session_start();

$userWalletFile = 'user_wallet.csv';

function getUserData($phone) {
    global $userWalletFile;

    if (!file_exists($userWalletFile)) {
        return null; // Return null if file doesn't exist
    }

    $users = array_map('str_getcsv', file($userWalletFile));
    foreach ($users as $user) {
        if ($user[0] === $phone) {
            return [
                'phone' => $user[0],
                'balance' => floatval($user[1]),
                'tokens' => floatval($user[2]),
                'avg_buy_price' => floatval($user[3]),
            ];
        }
    }

    return null;
}

function updateUserData($userData) {
    global $userWalletFile;

    $users = array_map('str_getcsv', file($userWalletFile));
    $updatedUsers = [];
    foreach ($users as $user) {
        if ($user[0] === $userData['phone']) {
            $updatedUsers[] = [
                $userData['phone'],
                $userData['balance'],
                $userData['tokens'],
                $userData['avg_buy_price']
            ];
        } else {
            $updatedUsers[] = $user;
        }
    }

    $file = fopen($userWalletFile, 'w');
    foreach ($updatedUsers as $user) {
        fputcsv($file, $user);
    }
    fclose($file);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['phone'])) {
        echo json_encode(['success' => false, 'message' => 'User session not found. Please log in.']);
        exit;
    }

    $phone = $_SESSION['phone'];
    $action = $_POST['action'] ?? '';
    $currentPrice = floatval($_POST['currentPrice'] ?? 0);

    $user = getUserData($phone);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    if ($action === 'buy') {
        $investment = floatval($_POST['investment'] ?? 0);

        if ($investment > 0 && $investment <= $user['balance']) {
            $tokensBought = $investment / $currentPrice;
            $user['tokens'] += $tokensBought;
            $user['balance'] -= $investment;

            if ($user['avg_buy_price'] == 0) {
                $user['avg_buy_price'] = $currentPrice;
            } else {
                $user['avg_buy_price'] =
                    ($user['avg_buy_price'] * ($user['tokens'] - $tokensBought) + $currentPrice * $tokensBought)
                    / $user['tokens'];
            }

            updateUserData($user);
            echo json_encode(['success' => true, 'balance' => $user['balance'], 'tokens' => $user['tokens']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Insufficient balance or invalid investment amount.']);
        }
    } elseif ($action === 'sell') {
        $tokensToSell = floatval($_POST['tokens'] ?? 0);

        if ($tokensToSell > 0 && $tokensToSell <= $user['tokens']) {
            $saleAmount = $tokensToSell * $currentPrice;
            $profit = $saleAmount - ($tokensToSell * $user['avg_buy_price']);
            $tax = ($profit > 0) ? $profit * 0.35 : 0;

            $user['balance'] += ($saleAmount - $tax);
            $user['tokens'] -= $tokensToSell;

            if ($user['tokens'] == 0) {
                $user['avg_buy_price'] = 0;
            }

            updateUserData($user);
            echo json_encode([
                'success' => true,
                'balance' => $user['balance'],
                'tokens' => $user['tokens'],
                'tax_collected' => $tax
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Insufficient tokens or invalid sale amount.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
}

// ... existing code ...

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_SESSION['phone'])) {
        echo json_encode(['success' => false, 'message' => 'User session not found. Please log in.']);
        exit;
    }

    $phone = $_SESSION['phone'];
    $user = getUserData($phone);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
    } else {
        echo json_encode([
            'success' => true,
            'balance' => $user['balance'],
            'tokens' => $user['tokens'],
            'avg_buy_price' => $user['avg_buy_price']
        ]);
    }
}


?>
