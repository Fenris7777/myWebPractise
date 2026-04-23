<?php
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Campus Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            width: 90%;
            max-width: 800px;
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .modules {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .module-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            text-decoration: none;
            color: #333;
            transition: 0.3s;
            display: block;
        }
        
        .module-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .module-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 10px;
            font-size: 14px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>  Campus Management System</h1>
        <p>Private Higher Education Institution</p>
        
        <div class="modules">
            <a href="parking.php" class="module-card">
                <div class="module-icon">🅿️</div>
                <div class="module-title">Parking Permits</div>
                <div class="module-desc">Manage parking permits for students, staff, and visitors</div>
            </a>
            
            <a href="library.php" class="module-card">
                <div class="module-icon">📚</div>
                <div class="module-title">Library Management</div>
                <div class="module-desc">Borrow and return books with fine system</div>
            </a>
            
            <a href="performance.php" class="module-card">
                <div class="module-icon">📊</div>
                <div class="module-title">Student Performance</div>
                <div class="module-desc">Track grades, averages, and academic progress</div>
            </a>
        </div>
        
        <div class="info">
            Data stored in session memory | All modules share data through functions.php
        </div>
    </div>
</body>
</html>