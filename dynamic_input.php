<?php
// PHP Variables - Dynamic Data
$storeName = "TechHive";
$product = "Gaming Laptop";
$price = 85000;
$inStock = true;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dynamic Input - TechHive</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; }
        h1 { color: #333; }
        .product-info { margin-bottom: 30px; }
        .input-section { background: #f5f5f5; padding: 20px; border-radius: 8px; max-width: 500px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; font-size: 1rem; }
        #live-preview { margin-top: 15px; padding: 15px; background: #fff; border-left: 4px solid #0066cc; display: none; }
        #preview-name { font-weight: bold; color: #0066cc; font-size: 1.2rem; }
        .label { font-weight: bold; margin-top: 10px; display: block; }
    </style>
</head>
<body>

    <!-- PHP Dynamic Output -->
    <h1>Welcome to <?php echo $storeName; ?></h1>

    <div class="product-info">
        <p>Product: <?php echo $product; ?></p>
        <p>Price: KES <?php echo number_format($price); ?></p>
        <p>In Stock: <?php echo $inStock ? "Yes" : "No"; ?></p>
    </div>

    <!-- DOM Manipulation Section -->
    <div class="input-section">
        <h2>Live User Input Demo</h2>
        <span class="label">Type your name below:</span>
        <input type="text" id="username-input" placeholder="Enter your name here...">

        <!-- This updates live as user types -->
        <div id="live-preview">
            <p>Hello, <span id="preview-name"></span>! Welcome to TechHive.</p>
            <p>You are browsing: <strong><?php echo $product; ?></strong></p>
        </div>
    </div>

    <script>
        // DOM Manipulation - Live text preview
        const input = document.getElementById('username-input');
        const preview = document.getElementById('live-preview');
        const previewName = document.getElementById('preview-name');

        input.addEventListener('keyup', function() {
            if (this.value.trim() !== '') {
                // Show preview div
                preview.style.display = 'block';
                // Update content dynamically
                previewName.textContent = this.value;
            } else {
                // Hide if empty
                preview.style.display = 'none';
            }
        });
    </script>

</body>
</html>