<?php
include "db_connect.php";
//to remember who is logged in
session_start();
//to see if the user are admin or not (Security Check)
if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] != 'admin') {
    die("Access Denied");
}


if (isset($_POST["submit"])) {
    $name = $_POST["name"];
    $price = $_POST["price"];
    $image = $_POST["image"];
    $description = $_POST["description"];
    $sizes = $_POST["sizes"];
    $category = $_POST["category"]; // NEW: Capture category

    // Updated SQL includes category
    $sql = "INSERT INTO products (name, price, image, description, sizes, category) VALUES ('$name', '$price', '$image', '$description', '$sizes', '$category')";

    if ($conn->query($sql)) {
        echo "<p style='color:green; text-align:center;'>Product Added Successfully!</p>";
    } else {
        echo "<p style='color:red; text-align:center;'>Error: " . $conn->error . "</p>";
    }
}
?>
<!--  UI  -->
<html>

<head>
    <title>Add Product</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <div style="padding: 20px; max-width: 600px; margin: auto;">
        <h2>Add New Product</h2>
        <form method="post">
            <div class="form-group">
                <label>Product Name:</label>
                <input type="text" name="name" class="form-group input" required style="width:100%; padding:10px;">
            </div>
            <div class="form-group">
                <label>Price (RM):</label>
                <input type="text" name="price" class="form-group input" required style="width:100%; padding:10px;">
            </div>
            <div class="form-group">

                <label>Image File(e.g. shirt.jpg):</label>
                <input type="file" name="image" class="form-group input" required style="width:100%; padding:10px;" placeholder="Enter filename from /assets folder">
            </div>
            <div class="form-group">
                <label>Category:</label>
                <select name="category" required style="width:100%; padding:10px;">
                    <option value="">-- Select Category --</option>
                    <option value="Shirts">Shirts</option>
                    <option value="Pants">Pants</option>
                    <option value="Hoodies">Hoodies</option>
                    <option value="Accessories">Accessories</option>
                    <option value="Others">Others</option>
                </select>
            </div>

            <!--Product Details/Description -->
            <div class="form-group">
                <label>Product Details:</label>
                <textarea name="description" class="form-group input" rows="4" style="width:100%; padding:10px;" placeholder="Enter product description..."></textarea>
            </div>

            <!-- Product Sizes -->
            <div class="form-group">
                <label>Available Sizes (comma separated):</label>
                <input type="text" name="sizes" class="form-group input" required style="width:100%; padding:10px;" placeholder="e.g. S, M, L, XL">
            </div>

            <br>
            <button type="submit" name="submit" class="btn-cart">Add Product</button>
        </form>
        <br>
        <a href="adminMenu.php">Back to Admin Menu</a>
    </div>
</body>

</html>