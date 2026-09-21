<?php
include 'db_connect.php';
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    die("Access Denied");
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $conn->query("DELETE FROM products WHERE id=$id");
    header("location: manageProducts.php");
}

// Handle Search
$search_result = "";
if (isset($_POST['search'])) {
    $search_term = $_POST['search_term'];
    $sql = "SELECT * FROM products WHERE name LIKE '%$search_term%'";
} else {
    $sql = "SELECT * FROM products";
}
$result = $conn->query($sql);
?>
<html>

<head>
    <title>Manage Products</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #333;
            color: white;
        }

        .action-btn {
            padding: 5px 10px;
            text-decoration: none;
            color: white;
            border-radius: 3px;
        }

        .edit-btn {
            background-color: #4CAF50;
        }

        .delete-btn {
            background-color: #f44336;
        }
    </style>
</head>

<body>
    <div style="padding: 20px;">
        <h2>Manage Products</h2>

        <!-- Search Form -->
        <form method="post" style="margin-bottom: 20px;">
            <input type="text" name="search_term" placeholder="Search product name..." style="padding:8px;">
            <button type="submit" name="search" class="btn-cart">Search</button>
        </form>

        <!-- Product Table -->
        <table>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Price (RM)</th>
                <th>Sizes </th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['image'] . "</td>";
                    echo "<td>" . $row['name'] . "</td>";
                    echo "<td>" . $row['price'] . "</td>";
                    echo "<td>" . $row['sizes'] . "</td>";
                    echo "<td>" . $row['description'] . "</td>";
                    echo "<td>
                            <a href='editProduct.php?id=" . $row['id'] . "' class='action-btn edit-btn'>Edit</a>
                            <a href='manageProducts.php?delete_id=" . $row['id'] . "' class='action-btn delete-btn' onclick=\"return confirm('Are you sure?')\">Delete</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No products found</td></tr>";
            }
            ?>
        </table>
        <br>
        <a href="adminMenu.php">Back to Admin Menu</a>
    </div>
</body>

</html>