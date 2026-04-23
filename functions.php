<?php
session_start();

// Initialize session data arrays for all modules
function initData() {
    if (!isset($_SESSION['parking_permit_data'])) {
        $_SESSION['parking_permit_data'] = [
            'permits_sold' => [],
            'student_count' => 0,
            'staff_count' => 0,
            'visitor_count' => 0,
            'total_revenue' => 0
        ];
    }
    
    if (!isset($_SESSION['library_data'])) {
        $_SESSION['library_data'] = [
            'borrowed_books' => [],
            'active_loans' => 0
        ];
    }
    
    if (!isset($_SESSION['performance_data'])) {
        $_SESSION['performance_data'] = [
            'students' => []
        ];
    }
}

// Validate age (minimum 18 years)
function validateAge($age) {
    return ($age >= 18);
}

// Sanitize user input
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Set notification message
function setMessage($type, $text) {
    $_SESSION['message'] = ['type' => $type, 'text' => $text];
}

// Display and clear notification message
function displayMessage() {
    if (isset($_SESSION['message'])) {
        echo "<div class='alert alert-{$_SESSION['message']['type']}'>{$_SESSION['message']['text']}</div>";
        unset($_SESSION['message']);
    }
}

// Convert percentage to letter grade
function getLetterGrade($percentage) {
    if ($percentage >= 90) return 'A+';
    if ($percentage >= 80) return 'A';
    if ($percentage >= 75) return 'B+';
    if ($percentage >= 70) return 'B';
    if ($percentage >= 60) return 'C';
    if ($percentage >= 50) return 'D';
    return 'F';
}

// Convert percentage to GPA (4.0 scale)
function getGPA($percentage) {
    if ($percentage >= 90) return 4.0;
    if ($percentage >= 85) return 3.7;
    if ($percentage >= 80) return 3.3;
    if ($percentage >= 75) return 3.0;
    if ($percentage >= 70) return 2.7;
    if ($percentage >= 65) return 2.3;
    if ($percentage >= 60) return 2.0;
    if ($percentage >= 55) return 1.7;
    if ($percentage >= 50) return 1.0;
    return 0.0;
}

initData();
?>