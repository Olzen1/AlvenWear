<?php
include "db_connect.php";
session_start();

//to see if it the admin or not
if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] != "admin") {
    die("Access Denied");
}

//for the user to
$id = $_GET["id"];

// this comm is the collect the product from its id
$result = $conn->query("SELECT * FROM products WHERE id=$id");
$row = $result->fetch_assoc();

if (isset($_POST["update"])) {
    $name = $_POST["name"];
    $price = $_POST["price"];
    $image = $_POST["image"];
    $sizes = $_POST["size"];
    $description = $_POST["description"];

    $sql = "UPDATE products SET name='$name', price='$price', image='$image', sizes='$sizes', description='$description' WHERE id=$id";

    $conn->query($sql);

    header("location: adminMenu.php#products");
    exit();
}
?>
<html>
<head>
    <title>Edit Product</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* Added styles to make the form fill the page */
        body {
            background-color: #f5f5f5;
            font-family: sans-serif;
            margin: 0;
            padding: 40px 20px;
        }
        .edit-container {
            max-width: 900px; /* Increased width to fill the page */
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-top: 0;
            margin-bottom: 30px;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            box-sizing: border-box; /* Ensures padding doesn't break width */
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .btn-cart {
            background-color: #000;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        .btn-cart:hover {
            background-color: #333;
        }
        .cancel-link {
            display: inline-block;
            margin-top: 20px;
            margin-left: 15px;
            color: #666;
            text-decoration: none;
            font-size: 15px;
        }
        .cancel-link:hover {
            text-decoration: underline;
            color: #000;
        }
    </style>
</head>

<body>
    <div class="edit-container">
        <h2>Edit Product</h2>
        <form method="post">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" value="<?php echo $row["name"]; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Price:</label>
                <input type="text" name="price" value="<?php echo $row["price"]; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Image:</label>
                <input type="text" name="image" value="<?php echo $row["image"]; ?>" required>
            </div>

            <div class="form-group">
                <label>Description:</label>
                <!-- Description Input -->
                <textarea name="description" rows="4"><?php echo $row["description"]; ?></textarea>
            </div>

            <div class="form-group">
                <label>Sizes:</label>
                <input type="text" name="size" value="<?php echo $row["sizes"]; ?>" required>
            </div>

            <br>
            <button type="submit" name="update" class="btn-cart">Update Product</button>
            <a href="adminMenu.php#products" class="cancel-link">Cancel</a>
        </form>
    </div>
</body>
</html>