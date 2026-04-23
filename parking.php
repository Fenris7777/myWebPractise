<?php
require_once 'functions.php';

define('PRICE_STUDENT', 450);
define('PRICE_STAFF', 750);
define('PRICE_VISITOR', 100);
define('MAX_PARKING_CAPACITY', 100);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'apply') {
        $name = sanitizeInput($_POST['name']);
        $age = (int)$_POST['age'];
        $permit_type = $_POST['permit_type'];
        
        if (!validateAge($age)) {
            setMessage('danger', 'Error: Individuals under 18 cannot be issued a permit.');
        } else {
            $total_sold = $_SESSION['parking_permit_data']['student_count'] + 
                          $_SESSION['parking_permit_data']['staff_count'] + 
                          $_SESSION['parking_permit_data']['visitor_count'];
            
            if ($total_sold >= MAX_PARKING_CAPACITY) {
                setMessage('danger', 'Error: Parking capacity full. Max ' . MAX_PARKING_CAPACITY . ' permits.');
            } else {
                switch ($permit_type) {
                    case 'student':
                        $price = PRICE_STUDENT;
                        $_SESSION['parking_permit_data']['student_count']++;
                        break;
                    case 'staff':
                        $price = PRICE_STAFF;
                        $_SESSION['parking_permit_data']['staff_count']++;
                        break;
                    case 'visitor':
                        $price = PRICE_VISITOR;
                        $_SESSION['parking_permit_data']['visitor_count']++;
                        break;
                }
                
                $_SESSION['parking_permit_data']['permits_sold'][] = [
                    'name' => $name, 'age' => $age, 'type' => $permit_type,
                    'price' => $price, 'date' => date('Y-m-d H:i:s')
                ];
                $_SESSION['parking_permit_data']['total_revenue'] += $price;
                
                setMessage('success', "Permit issued to $name ($permit_type) - R$price");
            }
        }
        header('Location: parking.php');
        exit();
    }
    
    if ($_POST['action'] === 'reset') {
        $_SESSION['parking_permit_data'] = [
            'permits_sold' => [], 'student_count' => 0, 'staff_count' => 0,
            'visitor_count' => 0, 'total_revenue' => 0
        ];
        setMessage('info', 'Parking permit data reset.');
        header('Location: parking.php');
        exit();
    }
}

$data = $_SESSION['parking_permit_data'];
$total_permits = $data['student_count'] + $data['staff_count'] + $data['visitor_count'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Parking Permit Module</title>
    <style>
        body { font-family: Arial; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; }
        .back-btn { background: rgba(255,255,255,0.2); color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .card h2 { border-bottom: 2px solid #667eea; padding-bottom: 10px; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px; }
        .reset-btn { background: #e53e3e; }
        .summary-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px; }
        .stat { background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center; }
        .stat h3 { font-size: 28px; color: #667eea; }
        .permit-list { max-height: 300px; overflow-y: auto; }
        .permit-item { background: #f8f9fa; padding: 10px; margin-bottom: 10px; border-left: 4px solid #667eea; }
        .alert { padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .alert-success { background: #c6f6d5; color: #22543d; }
        .alert-danger { background: #fed7d7; color: #742a2a; }
        .alert-info { background: #bee3f8; color: #2c5282; }
        .capacity-bar { background: #e2e8f0; border-radius: 10px; overflow: hidden; margin-top: 15px; }
        .capacity-fill { background: #48bb78; height: 20px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1> Parking Permit Management</h1>
        <a href="index.php" class="back-btn">← Back</a>
    </div>
    
    <?php displayMessage(); ?>
    
    <div class="grid">
        <div class="card">
            <h2>Apply for Permit</h2>
            <form method="POST">
                <input type="hidden" name="action" value="apply">
                <div class="form-group"><label>Full Name:</label><input type="text" name="name" required></div>
                <div class="form-group"><label>Age (18+):</label><input type="number" name="age" min="18" max="120" required></div>
                <div class="form-group">
                    <label>Permit Type:</label>
                    <select name="permit_type">
                        <option value="student">Student - R<?php echo PRICE_STUDENT; ?></option>
                        <option value="staff">Staff - R<?php echo PRICE_STAFF; ?></option>
                        <option value="visitor">Visitor - R<?php echo PRICE_VISITOR; ?></option>
                    </select>
                </div>
                <button type="submit">Issue Permit</button>
            </form>
            <form method="POST" style="margin-top: 15px;">
                <input type="hidden" name="action" value="reset">
                <button type="submit" class="reset-btn">Reset Data</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Permit Summary</h2>
            <div class="summary-stats">
                <div class="stat"><h3><?php echo $data['student_count']; ?></h3><p>Students</p></div>
                <div class="stat"><h3><?php echo $data['staff_count']; ?></h3><p>Staff</p></div>
                <div class="stat"><h3><?php echo $data['visitor_count']; ?></h3><p>Visitors</p></div>
            </div>
            <div class="stat"><h3>R<?php echo number_format($data['total_revenue'], 2); ?></h3><p>Total Revenue</p></div>
            <div class="capacity-bar"><div class="capacity-fill" style="width: <?php echo ($total_permits / MAX_PARKING_CAPACITY) * 100; ?>%"></div></div>
            <p style="text-align:center; margin-top:10px;">Capacity: <?php echo $total_permits; ?> / <?php echo MAX_PARKING_CAPACITY; ?></p>
        </div>
    </div>
    
    <div class="card">
        <h2>Recent Permits</h2>
        <?php if (empty($data['permits_sold'])): ?>
            <p>No permits issued yet.</p>
        <?php else: ?>
            <?php foreach (array_slice(array_reverse($data['permits_sold']), 0, 10) as $permit): ?>
                <div class="permit-item">
                    <strong><?php echo htmlspecialchars($permit['name']); ?></strong> - 
                    <?php echo ucfirst($permit['type']); ?> - R<?php echo $permit['price']; ?>
                    <small style="display:block; color:#888;"><?php echo $permit['date']; ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>