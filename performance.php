<?php
require_once 'functions.php';

//session_destroy(); 
// Initialize with 4 students, each with 8 marks
function initPerformanceData() {
    $default_students = [
        ['id' => 'STU001', 'name' => 'Mike Ndlovu', 'course' => 'Computer Science',
         'marks' => [85, 78, 92, 88, 79, 91, 84, 87],
         'subjects' => ['Maths', 'Physics', 'Programming', 'Databases', 'Networks', 'Web Dev', 'AI', 'Security']],
        ['id' => 'STU002', 'name' => 'John Snow', 'course' => 'Business Administration',
         'marks' => [65, 72, 58, 70, 68, 75, 71, 69],
         'subjects' => ['Economics', 'Accounting', 'Marketing', 'Management', 'Finance', 'Business Law', 'Statistics', 'HR']],
        ['id' => 'STU003', 'name' => 'Jacob Ndlovu', 'course' => 'Engineering',
         'marks' => [95, 92, 88, 94, 96, 91, 93, 90],
         'subjects' => ['Maths', 'Thermodynamics', 'Fluid Mechanics', 'Structures', 'Electronics', 'Materials', 'Dynamics', 'CAD']],
        ['id' => 'STU004', 'name' => 'Tom Brown', 'course' => 'Information Technology',
         'marks' => [45, 52, 48, 55, 42, 50, 47, 53],
         'subjects' => ['Programming', 'Databases', 'Web Tech', 'OS', 'Networks', 'Security', 'Cloud', 'Project Mgmt']]
    ];
    
    if (!isset($_SESSION['performance_data']) || empty($_SESSION['performance_data']['students'])) {
        $_SESSION['performance_data'] = ['students' => $default_students];
    }
}

// Calculate average of marks
function calculateAverage($marks) {
    if (!is_array($marks) || empty($marks)) return 0;
    $valid = array_filter($marks, fn($m) => is_numeric($m) && $m >= 0 && $m <= 100);
    return empty($valid) ? 0 : array_sum($valid) / count($valid);
}

// Determine result: Distinction (75+), Pass (50-74), Fail (<50)
function determineResult($average) {
    if ($average >= 75) return 'Distinction';
    if ($average >= 50) return 'Pass';
    return 'Fail';
}

// Add mark to student
function addMark($student_id, $subject, $mark) {
    if ($mark < 0 || $mark > 100) {
        return ['success' => false, 'message' => "Invalid mark. Must be 0-100."];
    }
    
    foreach ($_SESSION['performance_data']['students'] as &$student) {
        if ($student['id'] === $student_id) {
            $student['marks'][] = (float)$mark;
            $student['subjects'][] = htmlspecialchars($subject);
            return ['success' => true, 'message' => "Added $mark% for {$student['name']} in $subject"];
        }
    }
    return ['success' => false, 'message' => "Student not found."];
}

// Add new student
function addStudent($id, $name, $course) {
    foreach ($_SESSION['performance_data']['students'] as $student) {
        if ($student['id'] === $id) {
            return ['success' => false, 'message' => "Student ID already exists."];
        }
    }
    
    $_SESSION['performance_data']['students'][] = [
        'id' => $id, 'name' => htmlspecialchars($name), 'course' => htmlspecialchars($course),
        'marks' => [], 'subjects' => []
    ];
    return ['success' => true, 'message' => "Student $name added."];
}

// Get top performing student
function getTopStudent() {
    $students = $_SESSION['performance_data']['students'] ?? [];
    if (empty($students)) return null;
    
    $top = null;
    $highest = -1;
    foreach ($students as $s) {
        $avg = calculateAverage($s['marks']);
        if ($avg > $highest) {
            $highest = $avg;
            $top = $s;
            $top['average'] = $avg;
        }
    }
    return $top;
}

// Calculate class statistics
function getClassStatistics() {
    $students = $_SESSION['performance_data']['students'] ?? [];
    if (empty($students)) {
        return ['class_avg' => 0, 'highest' => 0, 'lowest' => 0, 'distinction' => 0, 'pass' => 0, 'fail' => 0, 'total' => 0, 'total_marks' => 0];
    }
    
    $averages = [];
    $dist = $pass = $fail = $total_marks = 0;
    
    foreach ($students as $s) {
        $avg = calculateAverage($s['marks']);
        $averages[] = $avg;
        $total_marks += count($s['marks']);
        
        $result = determineResult($avg);
        if ($result === 'Distinction') $dist++;
        elseif ($result === 'Pass') $pass++;
        else $fail++;
    }
    
    return [
        'class_avg' => array_sum($averages) / count($averages),
        'highest' => max($averages),
        'lowest' => min($averages),
        'distinction' => $dist,
        'pass' => $pass,
        'fail' => $fail,
        'total' => count($students),
        'total_marks' => $total_marks
    ];
}

// Get all students with calculated averages
function getAllStudents() {
    $students = $_SESSION['performance_data']['students'] ?? [];
    $result = [];
    foreach ($students as $s) {
        $avg = calculateAverage($s['marks']);
        $result[] = [
            'id' => $s['id'], 'name' => $s['name'], 'course' => $s['course'],
            'marks' => $s['marks'], 'subjects' => $s['subjects'] ?? [],
            'average' => $avg, 'result' => determineResult($avg)
        ];
    }
    return $result;
}

initPerformanceData();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_mark') {
        $result = addMark($_POST['student_id'], $_POST['subject'], (float)$_POST['mark']);
        setMessage($result['success'] ? 'success' : 'danger', $result['message']);
    } elseif ($action === 'add_student') {
        $result = addStudent($_POST['student_id'], $_POST['name'], $_POST['course']);
        setMessage($result['success'] ? 'success' : 'danger', $result['message']);
    } elseif ($action === 'reset') {
        initPerformanceData(); // Reset to default
        setMessage('info', 'Reset to 4 default students with 8 marks each.');
    }
    header('Location: performance.php');
    exit();
}

$students = getAllStudents();
$top = getTopStudent();
$stats = getClassStatistics();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Performance Analytics</title>
    <style>
        body { font-family: Arial; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #805ad5, #6b46c0); color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; }
        .back-btn { background: rgba(255,255,255,0.2); color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px; }
        .stat-card { background: white; padding: 15px; border-radius: 10px; text-align: center; }
        .stat-value { font-size: 28px; font-weight: bold; color: #805ad5; }
        .top-student { background: linear-gradient(135deg, #f6ad55, #ed8936); color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; }
        .grid { display: grid; grid-template-columns: 1fr 1.5fr; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .card h2 { border-bottom: 2px solid #805ad5; padding-bottom: 10px; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #805ad5; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .reset-btn { background: #e53e3e; }
        .alert { padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .alert-success { background: #c6f6d5; color: #22543d; }
        .alert-danger { background: #fed7d7; color: #742a2a; }
        .alert-info { background: #bee3f8; color: #2c5282; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f7fafc; }
        .mark-badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; margin: 2px; }
        .mark-good { background: #c6f6d5; color: #22543d; }
        .mark-bad { background: #fed7d7; color: #742a2a; }
        .result-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .marks-list { display: flex; flex-wrap: wrap; gap: 4px; }
        tr.clickable { cursor: pointer; }
        tr.clickable:hover { background: #f7fafc; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1> Student Performance Analytics</h1>
        <a href="index.php" class="back-btn">← Back</a>
    </div>
    
    <?php displayMessage(); ?>
    
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-value"><?php echo number_format($stats['class_avg'], 1); ?>%</div><div>Class Average</div></div>
        <div class="stat-card"><div class="stat-value"><?php echo number_format($stats['highest'], 1); ?>%</div><div>Highest Avg</div></div>
        <div class="stat-card"><div class="stat-value"><?php echo number_format($stats['lowest'], 1); ?>%</div><div>Lowest Avg</div></div>
        <div class="stat-card"><div class="stat-value"><?php echo $stats['total']; ?></div><div>Students</div></div>
    </div>
    
    <?php if ($top): ?>
    <div class="top-student">
        <div><strong> Top Student:</strong> <?php echo $top['name']; ?> (<?php echo $top['id']; ?>) - <?php echo number_format($top['average'], 1); ?>%</div>
        
    </div>
    <?php endif; ?>
    
    <div class="stats-grid">
        <div class="stat-card"> Distinction: <?php echo $stats['distinction']; ?></div>
        <div class="stat-card"> Pass: <?php echo $stats['pass']; ?></div>
        <div class="stat-card"> Fail: <?php echo $stats['fail']; ?></div>
        <div class="stat-card"> Pass Rate: <?php echo $stats['total'] > 0 ? round(($stats['distinction'] + $stats['pass']) / $stats['total'] * 100) : 0; ?>%</div>
    </div>
    
    <div class="grid">
        <div>
            <div class="card">
                <h2>Register Student</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_student">
                    <div class="form-group"><label>Student ID:</label><input type="text" name="student_id" required></div>
                    <div class="form-group"><label>Name:</label><input type="text" name="name" required></div>
                    <div class="form-group"><label>Course:</label><input type="text" name="course" required></div>
                    <button type="submit">Register</button>
                </form>
            </div>
            
            <div class="card">
                <h2>Add Mark</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_mark">
                    <div class="form-group">
                        <label>Student:</label>
                        <select name="student_id" required>
                            <option value="">Select</option>
                            <?php foreach ($students as $s): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo $s['name']; ?> (<?php echo $s['id']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Subject:</label><input type="text" name="subject" required></div>
                    <div class="form-group"><label>Mark (0-100):</label><input type="number" name="mark" step="0.01" min="0" max="100" required></div>
                    <button type="submit">Add Mark</button>
                </form>
            </div>
            
            <div class="card">
                <form method="POST"><input type="hidden" name="action" value="reset"><button type="submit" class="reset-btn">Reset to Default</button></form>
                <p style="font-size:12px; color:#888; margin-top:10px;">Reset: 4 students with 8 marks each</p>
            </div>
        </div>
        
        <div class="card">
            <h2>Student Records</h2>
            <?php if (empty($students)): ?>
                <p>No students. Click Reset to load sample data.</p>
            <?php else: ?>
                <table>
                    <thead><tr><th>ID</th><th>Name</th><th>Marks (first 6)</th><th>Avg</th><th>Result</th></tr></thead>
                    <tbody>
                        <?php foreach ($students as $s): ?>
                        <tr class="clickable" onclick="toggleDetails('<?php echo $s['id']; ?>')">
                            <td><?php echo $s['id']; ?> <?php echo ($top && $top['id'] === $s['id']) ? : ''; ?></td>
                            <td><?php echo $s['name']; ?></td>
                            <td><div class="marks-list"><?php foreach (array_slice($s['marks'], 0, 6) as $m): ?>
                                <span class="mark-badge <?php echo $m >= 50 ? 'mark-good' : 'mark-bad'; ?>"><?php echo $m; ?>%</span>
                            <?php endforeach; if (count($s['marks']) > 6) echo '<span>+ more</span>'; ?></div></td>
                            <td><strong><?php echo number_format($s['average'], 1); ?>%</strong></td>
                            <td><span class="result-badge" style="background:<?php echo $s['result']=='Distinction'?'#48bb7820':'#4299e120'; ?>"><?php echo $s['result']; ?></span></td>
                        </tr>
                        <tr id="detail-<?php echo $s['id']; ?>" style="display:none;">
                            <td colspan="5" style="background:#f8f9fa;">
                                <strong>All Marks:</strong><br>
                                <?php foreach ($s['marks'] as $i => $m): ?>
                                    <?php $subj = $s['subjects'][$i] ?? 'Subject'; ?>
                                    <span class="mark-badge <?php echo $m >= 50 ? 'mark-good' : 'mark-bad'; ?>"><?php echo $subj; ?>: <?php echo $m; ?>%</span>
                                <?php endforeach; ?>
                                <br><strong>Average:</strong> <?php echo number_format($s['average'], 1); ?>% | 
                                <strong>Total:</strong> <?php echo count($s['marks']); ?> subjects
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card">
        <h2>Class Statistics</h2>
        <table style="width:auto; display:inline-table; margin:5px;">
            <tr><td>Class Average:</td><td><strong><?php echo number_format($stats['class_avg'], 1); ?>%</strong></td></tr>
            <tr><td>Highest Average:</td><td><strong><?php echo number_format($stats['highest'], 1); ?>%</strong></td></tr>
            <tr><td>Lowest Average:</td><td><strong><?php echo number_format($stats['lowest'], 1); ?>%</strong></td></tr>
        </table>
        <table style="width:auto; display:inline-table; margin:5px;">
            <tr><td>Distinction:</td><td><strong><?php echo $stats['distinction']; ?></strong></td></tr>
            <tr><td>Pass:</td><td><strong><?php echo $stats['pass']; ?></strong></td></tr>
            <tr><td>Fail:</td><td><strong><?php echo $stats['fail']; ?></strong></td></tr>
        </table>
    </div>
</div>

<script>
function toggleDetails(id) {
    var row = document.getElementById('detail-' + id);
    if (row) row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
</script>
</body>
</html>