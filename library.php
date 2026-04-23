<?php
require_once 'functions.php';

define('BOOK_CATEGORIES', ['Textbook' => 5, 'Journal' => 3, 'Reference' => 10]);
define('FINE_THRESHOLD', 200);

function initLibraryData() {
    if (!isset($_SESSION['library_data'])) {
        $_SESSION['library_data'] = [
            'users' => [], 'books' => [], 'transactions' => [], 'total_fines_collected' => 0
        ];
    }
}

function calculateFine($return_date, $due_date, $daily_rate) {
    $return = new DateTime($return_date);
    $due = new DateTime($due_date);
    if ($return <= $due) return 0;
    return $return->diff($due)->days * $daily_rate;
}

function borrowBook($user_id, $user_name, $book_title, $category, $days_to_borrow) {
    if (!isset($_SESSION['library_data']['users'][$user_id])) {
        $_SESSION['library_data']['users'][$user_id] = [
            'name' => $user_name, 'user_id' => $user_id, 'outstanding_fine' => 0,
            'active_loans' => [], 'borrowing_history' => []
        ];
    }
    
    $user = &$_SESSION['library_data']['users'][$user_id];
    
    if ($user['outstanding_fine'] > FINE_THRESHOLD) {
        return ['success' => false, 'message' => "Cannot borrow: Outstanding fine R" . number_format($user['outstanding_fine'], 2) . " exceeds R" . FINE_THRESHOLD];
    }
    
    $book_id = uniqid('BOOK_');
    $borrow_date = date('Y-m-d');
    $due_date = date('Y-m-d', strtotime("+$days_to_borrow days"));
    $daily_rate = BOOK_CATEGORIES[$category];
    
    $book = [
        'book_id' => $book_id, 'title' => $book_title, 'category' => $category,
        'daily_rate' => $daily_rate, 'borrow_date' => $borrow_date, 'due_date' => $due_date,
        'status' => 'borrowed', 'borrowed_by' => $user_id, 'fine_paid' => false
    ];
    
    $_SESSION['library_data']['books'][$book_id] = $book;
    $user['active_loans'][$book_id] = $book;
    $_SESSION['library_data']['transactions'][] = [
        'type' => 'borrow', 'user_id' => $user_id, 'user_name' => $user_name,
        'book_title' => $book_title, 'due_date' => $due_date
    ];
    
    return ['success' => true, 'message' => "Book '$book_title' borrowed. Due: $due_date. Fine: R$daily_rate/day"];
}

function returnBook($book_id) {
    if (!isset($_SESSION['library_data']['books'][$book_id])) {
        return ['success' => false, 'message' => "Book not found."];
    }
    
    $book = &$_SESSION['library_data']['books'][$book_id];
    if ($book['status'] === 'returned') {
        return ['success' => false, 'message' => "Book already returned."];
    }
    
    $return_date = date('Y-m-d');
    $user = &$_SESSION['library_data']['users'][$book['borrowed_by']];
    $fine = calculateFine($return_date, $book['due_date'], $book['daily_rate']);
    
    $book['status'] = 'returned';
    $book['return_date'] = $return_date;
    $book['fine_amount'] = $fine;
    unset($user['active_loans'][$book_id]);
    
    $user['borrowing_history'][] = [
        'book_title' => $book['title'], 'borrow_date' => $book['borrow_date'],
        'due_date' => $book['due_date'], 'return_date' => $return_date,
        'fine_amount' => $fine, 'fine_paid' => false
    ];
    
    if ($fine > 0) {
        $user['outstanding_fine'] += $fine;
        $_SESSION['library_data']['total_fines_collected'] += $fine;
        $days_late = (new DateTime($return_date))->diff(new DateTime($book['due_date']))->days;
        return ['success' => true, 'message' => "Book returned. Late by $days_late days. Fine: R" . number_format($fine, 2)];
    }
    
    return ['success' => true, 'message' => "Book returned on time. No fine."];
}

function payFine($user_id, $amount) {
    if (!isset($_SESSION['library_data']['users'][$user_id])) {
        return ['success' => false, 'message' => "User not found."];
    }
    
    $user = &$_SESSION['library_data']['users'][$user_id];
    if ($amount <= 0) return ['success' => false, 'message' => "Invalid amount."];
    
    if ($amount > $user['outstanding_fine']) $amount = $user['outstanding_fine'];
    $user['outstanding_fine'] -= $amount;
    
    return ['success' => true, 'message' => "Payment of R" . number_format($amount, 2) . " received. Remaining: R" . number_format($user['outstanding_fine'], 2)];
}

function printUserSummary($user_id) {
    if (!isset($_SESSION['library_data']['users'][$user_id])) return "<p>User not found.</p>";
    
    $user = $_SESSION['library_data']['users'][$user_id];
    $html = "<h3>User: {$user['name']} (ID: {$user['user_id']})</h3>";
    $html .= "<p>Outstanding Fine: R" . number_format($user['outstanding_fine'], 2) . " | Active Loans: " . count($user['active_loans']) . "</p>";
    
    if ($user['outstanding_fine'] > FINE_THRESHOLD) {
        $html .= "<p style='color:red'> Cannot borrow new books until fines are cleared.</p>";
    }
    
    if (!empty($user['borrowing_history'])) {
        $html .= "<table border='1' cellpadding='5' cellspacing='0' style='width:100%; border-collapse:collapse;'>";
        $html .= "<tr><th>Book</th><th>Borrowed</th><th>Due</th><th>Returned</th><th>Fine</th></tr>";
        foreach ($user['borrowing_history'] as $h) {
            $fine = $h['fine_amount'] > 0 ? "R" . number_format($h['fine_amount'], 2) : "None";
            $html .= "<tr><td>{$h['book_title']}</td><td>{$h['borrow_date']}</td><td>{$h['due_date']}</td><td>{$h['return_date']}</td><td>$fine</td></tr>";
        }
        $html .= "</table>";
    }
    return $html;
}

initLibraryData();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'borrow') {
        $result = borrowBook($_POST['user_id'], $_POST['user_name'], $_POST['book_title'], $_POST['category'], (int)$_POST['days']);
        setMessage($result['success'] ? 'success' : 'danger', $result['message']);
    } elseif ($action === 'return') {
        $result = returnBook($_POST['book_id']);
        setMessage($result['success'] ? 'success' : 'danger', $result['message']);
    } elseif ($action === 'pay_fine') {
        $result = payFine($_POST['user_id'], (float)$_POST['amount']);
        setMessage($result['success'] ? 'success' : 'danger', $result['message']);
    } elseif ($action === 'reset') {
        $_SESSION['library_data'] = ['users' => [], 'books' => [], 'transactions' => [], 'total_fines_collected' => 0];
        setMessage('info', 'Library data reset.');
    }
    header('Location: library.php');
    exit();
}

$data = $_SESSION['library_data'];
$active_books = array_filter($data['books'] ?? [], fn($b) => $b['status'] === 'borrowed');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Library Management</title>
    <style>
        body { font-family: Arial; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #38b2ac; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; }
        .back-btn { background: rgba(255,255,255,0.2); color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .card h2 { border-bottom: 2px solid #38b2ac; padding-bottom: 10px; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #38b2ac; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .reset-btn { background: #e53e3e; }
        .pay-btn { background: #48bb78; }
        .alert { padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .alert-success { background: #c6f6d5; color: #22543d; }
        .alert-danger { background: #fed7d7; color: #742a2a; }
        .alert-info { background: #bee3f8; color: #2c5282; }
        .loan-item { background: #f8f9fa; padding: 10px; margin-bottom: 10px; border-left: 4px solid #38b2ac; }
        .fine-warning { color: #e53e3e; font-weight: bold; }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px; }
        .stat-box { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 15px; border-radius: 10px; text-align: center; }
        .stat-number { font-size: 28px; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1> Library Management</h1>
        <a href="index.php" class="back-btn">← Back</a>
    </div>
    
    <?php displayMessage(); ?>
    
    <div class="stats-grid">
        <div class="stat-box"><div class="stat-number"><?php echo count($data['users'] ?? []); ?></div><div>Users</div></div>
        <div class="stat-box"><div class="stat-number"><?php echo count($active_books); ?></div><div>Active Loans</div></div>
        <div class="stat-box"><div class="stat-number">R<?php echo number_format($data['total_fines_collected'] ?? 0, 2); ?></div><div>Fines Collected</div></div>
    </div>
    
    <div class="grid">
        <div>
            <div class="card">
                <h2>Borrow Book</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="borrow">
                    <div class="form-group"><label>User ID:</label><input type="text" name="user_id" required></div>
                    <div class="form-group"><label>User Name:</label><input type="text" name="user_name" required></div>
                    <div class="form-group"><label>Book Title:</label><input type="text" name="book_title" required></div>
                    <div class="form-group">
                        <label>Category:</label>
                        <select name="category">
                            <option value="Textbook">Textbook (R5/day)</option>
                            <option value="Journal">Journal (R3/day)</option>
                            <option value="Reference">Reference (R10/day)</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Days to Borrow:</label><input type="number" name="days" value="14" min="1" required></div>
                    <button type="submit">Borrow</button>
                </form>
            </div>
            
            <div class="card">
                <h2>Return Book</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="return">
                    <div class="form-group">
                        <label>Book ID:</label>
                        <select name="book_id" required>
                            <option value="">Select Book</option>
                            <?php foreach ($active_books as $id => $book): ?>
                                <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($book['title']); ?> (<?php echo $book['borrowed_by']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit">Return</button>
                </form>
            </div>
            
            <div class="card">
                <h2>Pay Fine</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="pay_fine">
                    <div class="form-group"><label>User ID:</label><input type="text" name="user_id" required></div>
                    <div class="form-group"><label>Amount (R):</label><input type="number" name="amount" step="0.01" min="0.01" required></div>
                    <button type="submit" class="pay-btn">Pay</button>
                </form>
            </div>
        </div>
        
        <div>
            <div class="card">
                <h2>Active Loans</h2>
                <?php if (empty($active_books)): ?>
                    <p>No active loans.</p>
                <?php else: ?>
                    <?php foreach ($active_books as $book): ?>
                        <div class="loan-item">
                            <strong><?php echo htmlspecialchars($book['title']); ?></strong><br>
                            Category: <?php echo $book['category']; ?> (R<?php echo $book['daily_rate']; ?>/day)<br>
                            Borrower: <?php echo $book['borrowed_by']; ?><br>
                            Due: <?php echo $book['due_date']; ?>
                            <?php if (new DateTime() > new DateTime($book['due_date'])): ?>
                                <div class="fine-warning"> OVERDUE</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <h2>User Summary</h2>
                <form method="GET">
                    <div class="form-group"><label>User ID:</label><input type="text" name="view_user" value="<?php echo $_GET['view_user'] ?? ''; ?>"></div>
                    <button type="submit">View</button>
                </form>
                <?php if (isset($_GET['view_user']) && $_GET['view_user']): ?>
                    <?php echo printUserSummary($_GET['view_user']); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="card">
        <form method="POST"><input type="hidden" name="action" value="reset"><button type="submit" class="reset-btn">Reset All Data</button></form>
    </div>
</div>
</body>
</html>