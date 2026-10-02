<?php

include '../Config/connection.php';

if(isset($_POST['register'])){

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    if($password != $confirm_password){

        echo "<script>alert('Passwords do not match');</script>";

    }else{

        $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' OR email='$email'");

        if(mysqli_num_rows($check) > 0){

            echo "<script>alert('Username or Email already exists');</script>";

        }else{

            $sql = "INSERT INTO users(username,email,password,role)
                    VALUES('$username','$email','$password','$role')";

            if(mysqli_query($conn, $sql)){

                echo "<script>
                        alert('Registration Successful');
                        window.location='login.php';
                      </script>";

            }else{

                echo "<script>alert('Registration Failed');</script>";
            }

        }

    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Register</title>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Segoe UI,sans-serif;
}

body{

display:flex;
justify-content:center;
align-items:center;
height:100vh;
background:linear-gradient(135deg,#0F2027,#203A43,#2C5364);

}

.register-box{

width:420px;
background:rgba(255,255,255,.12);
backdrop-filter:blur(12px);
padding:35px;
border-radius:15px;
box-shadow:0 10px 30px rgba(0,0,0,.4);

}

.register-box h2{

text-align:center;
color:white;
margin-bottom:25px;

}

.input-box{

position:relative;
margin-bottom:18px;

}

.input-box i{

position:absolute;
left:15px;
top:15px;
color:#777;

}

.input-box input{

width:100%;
padding:14px 14px 14px 45px;
border:none;
border-radius:30px;
outline:none;
font-size:15px;

}

button{

width:100%;
padding:14px;
border:none;
border-radius:30px;
background:#00BCD4;
color:white;
font-size:17px;
cursor:pointer;
transition:.3s;

}

button:hover{

background:#0097A7;

}

.login-link{

text-align:center;
margin-top:18px;
color:white;

}

.login-link a{

color:#00BCD4;
text-decoration:none;

}

.login-link a:hover{

text-decoration:underline;

}

</style>

</head>

<body>

<div class="register-box">

<h2><i class="fas fa-hotel"></i> Hotel Registration</h2>

<form method="POST">

<div class="input-box">
<i class="fas fa-user"></i>
<input type="text" name="username" placeholder="Username" required>
</div>

<div class="input-box">
<i class="fas fa-envelope"></i>
<input type="email" name="email" placeholder="Email Address" required>
</div>

<div class="input-box">
<i class="fas fa-lock"></i>
<input type="password" name="password" placeholder="Password" required>
</div>

<div class="input-box">
<i class="fas fa-lock"></i>
<input type="password" name="confirm_password" placeholder="Confirm Password" required>
</div>

<button type="submit" name="register">
<i class="fas fa-user-plus"></i> Register
</button>

</form>

<div class="login-link">

Already have an account?

<a href="login.php">Login Here</a>

</div>

</div>

</body>

</html>