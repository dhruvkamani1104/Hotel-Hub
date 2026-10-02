<?php
session_start();
include("../Config/connection.php");

if(!isset($_SESSION['username']))
{
    header("Location: ../Customer/login.php");
    exit();
}

if($_SESSION['role'] != "Admin")
{
    header("Location: ../Customer/login.php");
    exit();
}


// Delete Customer
if(isset($_GET['delete']))
{
    $id = $_GET['delete'];

    mysqli_query($conn,"DELETE FROM users WHERE user_id='$id'");

    echo "<script>alert('Customer Deleted Successfully');</script>";
    echo "<script>window.location='customers.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Customer Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>

body{
    background:#f4f6f9;
}

.container{
    margin-top:30px;
}

.card{
    border:none;
    border-radius:15px;
    box-shadow:0px 5px 15px rgba(0,0,0,.15);
}

.card-header{
    border-radius:15px 15px 0 0 !important;
}

.table th{
    background:#0d6efd;
    color:white;
    text-align:center;
}

.table td{
    vertical-align:middle;
    text-align:center;
}

.btn{
    border-radius:20px;
}

</style>

</head>

<body>
<div style="text-align: right; margin-bottom: 20px; margin-right: 30px; margin-Top: 40px;">

    <a href="dashboard.php" class="btn btn-primary">
        <i class="fa fa-arrow-left"></i>
        Back to Dashboard
    </a>

</div>

<div class="container">

<div class="card">

<div class="card-header bg-primary text-white">

<h3>
<i class="fa fa-users"></i>
 Customer Management
</h3>

</div>


<div class="card-body">

<table class="table table-bordered table-hover">

<thead>

<tr>

<th>ID</th>
<th>Username</th>
<th>Email</th>
<th>Role</th>
<th>Created Date</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php

$result = mysqli_query($conn,"SELECT * FROM users ORDER BY user_id DESC");

while($row = mysqli_fetch_assoc($result))
{

?>

<tr>

<td><?php echo $row['user_id']; ?></td>

<td><?php echo $row['username']; ?></td>

<td><?php echo $row['email']; ?></td>

<td>

<?php

if($row['role']=="Admin")
{
    echo "<span class='badge bg-danger'>Admin</span>";
}
else
{
    echo "<span class='badge bg-success'>User</span>";
}

?>

</td>

<td><?php echo $row['created_at']; ?></td>

<td>

<?php
if($row['role']!="Admin")
{
?>

<a href="customers.php?delete=<?php echo $row['user_id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Are you sure you want to delete this customer?')">

<i class="fa fa-trash"></i> Delete

</a>

<?php
}
else
{
    echo "<span class='text-muted'>Protected</span>";
}
?>

</td>

</tr>

<?php
}
?>

</tbody>

</table>

</div>

</div>

<br>

</div>

</body>
</html>