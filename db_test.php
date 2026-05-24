<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DB Connection Test</title>
</head>
<body>
    <?php
        $conn = mysqli_connect("localhost", "root", "", "techhive_db");

        if ($conn) {
            echo "<h1>Database connection successful!</h1>";
        } else {
            echo "<h1>Connection failed: " . mysqli_connect_error() . "</h1>";
        }
    ?>
</body>
</html>
