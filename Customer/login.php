<?php
session_start();
include '../Config/connection.php';

if(isset($_POST['login']))
{
    $username = mysqli_real_escape_string($conn,$_POST['username']);
    $password = mysqli_real_escape_string($conn,$_POST['password']);

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn,$sql);

    if(mysqli_num_rows($result)==1)
    {
        $row = mysqli_fetch_assoc($result);

        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];

        // ==========================
        // Customer ID Session Create
        // ==========================

        $email = $row['email'];

        $customer = mysqli_query($conn,
        "SELECT customer_id FROM customers WHERE email='$email'");

        if(mysqli_num_rows($customer)>0)
        {
            $cust = mysqli_fetch_assoc($customer);
            $_SESSION['customer_id'] = $cust['customer_id'];
        }

        // ==========================

        if($row['role']=="Admin")
        {
            header("Location: ../Admin/dashboard.php");
            exit();
        }
        else
        {
            header("Location: dashboard.php");
            exit();
        }
    }
    else
    {
        echo "<script>alert('Invalid Username or Password');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Hotel Booking Login</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Arial;
}

body{
display:flex;
justify-content:center;
align-items:center;
height:100vh;
background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);
}

.login{
width:400px;
padding:40px;
background:white;
border-radius:15px;
box-shadow:0px 0px 20px rgba(0,0,0,.3);
}

.login h2{
text-align:center;
margin-bottom:25px;
color:#0f2027;
}

.input-box{
margin-bottom:20px;
}

.input-box input{
width:100%;
padding:12px;
font-size:16px;
border:1px solid #ccc;
border-radius:8px;
}

button{
width:100%;
padding:12px;
font-size:18px;
background:#007bff;
color:white;
border:none;
border-radius:8px;
cursor:pointer;
}

button:hover{
background:#0056b3;
}

p{
margin-top:15px;
text-align:center;
}

a{
text-decoration:none;
color:#007bff;
}

</style>

</head>

<body>

<div class="login">

<h2>
<i class="fa-solid fa-hotel"></i>
Hotel Booking Login
</h2>

<form method="POST">

<div class="input-box">
<input type="text" name="username" placeholder="Enter Username" required>
</div>

<div class="input-box">
<input type="password" name="password" placeholder="Enter Password" required>
</div>

<button type="submit" name="login">
Login
</button>

<p>
Don't have an account?
<a href="register.php">Register</a>
</p>

</form>

</div>

</body>
</html>