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
    header("Location: ../Customer/dashboard.php");
    exit();
}

// Total Users
$user_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
$user = mysqli_fetch_assoc($user_query)['total'];

// Total Rooms
$room_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM rooms");
$room = mysqli_fetch_assoc($room_query)['total'];

// Total Bookings
$booking_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings");
$booking = mysqli_fetch_assoc($booking_query)['total'];

// Total Payments / Revenue
$payment_query = mysqli_query($conn, "SELECT IFNULL(SUM(amount),0) AS total FROM payments");
$payment = mysqli_fetch_assoc($payment_query)['total'];
?>

<!DOCTYPE html>
<html>
<head>

<title>Admin Dashboard</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial,sans-serif;
}

body{
    background:#f4f6f9;
}

/* Main Container */
.main{
    width:100%;
    padding:25px;
}

/* Top Header */
.top{
    background:white;
    padding:20px 25px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,.1);

    display:flex;
    justify-content:space-between;
    align-items:center;
}

/* Header Title */
.top h2{
    color:#111;
}

/* Welcome + Logout */
.top-right{
    display:flex;
    align-items:center;
    gap:20px;
}

.top-right h3{
    margin:0;
}

/* Logout Button */
.logout-btn{
    background:#dc3545;
    color:white;
    text-decoration:none;
    padding:10px 18px;
    border-radius:6px;
    font-size:15px;
    transition:0.3s;
}

.logout-btn:hover{
    background:#b02a37;
}

.logout-btn i{
    margin-right:6px;
}

/* Cards */
.cards{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
    margin-top:30px;
}

/* Card Link */
.card-link{
    text-decoration:none;
    color:inherit;
}

/* Card */
.card{
    background:white;
    padding:30px 20px;
    border-radius:10px;
    text-align:center;
    box-shadow:0 0 10px rgba(0,0,0,.1);

    transition:0.3s;
    cursor:pointer;
}

/* Card Hover */
.card:hover{
    transform:translateY(-5px);
    box-shadow:0 8px 20px rgba(0,0,0,.18);
}

/* Icons */
.card i{
    font-size:45px;
    margin-bottom:15px;
    color:#1e3c72;
}

/* Card Heading */
.card h3{
    margin:8px 0;
    color:#111;
}

/* Number */
.card h1{
    margin-top:10px;
    font-size:32px;
    color:#000;
}

/* Responsive */
@media(max-width:1000px){

    .cards{
        grid-template-columns:repeat(2,1fr);
    }

}

@media(max-width:600px){

    .top{
        flex-direction:column;
        gap:15px;
        text-align:center;
    }

    .top-right{
        flex-direction:column;
        gap:10px;
    }

    .cards{
        grid-template-columns:1fr;
    }

}

</style>

</head>

<body>

<div class="main">

    <!-- Top Header -->
    <div class="top">

        <h2>Admin Dashboard</h2>

        <div class="top-right">

            <h3>
                Welcome <?php echo htmlspecialchars($_SESSION['username']); ?>
            </h3>

            <a href="../Customer/login.php" class="logout-btn">
                <i class="fa fa-sign-out-alt"></i>
                Logout
            </a>

        </div>

    </div>


    <!-- Dashboard Cards -->
    <div class="cards">

        <!-- Total Users -->
        <a href="customers.php" class="card-link">

            <div class="card">

                <i class="fa fa-users"></i>

                <h3>Total Users</h3>

                <h1>
                    <?php echo $user; ?>
                </h1>

            </div>

        </a>


        <!-- Total Rooms -->
        <a href="rooms.php" class="card-link">

            <div class="card">

                <i class="fa fa-bed"></i>

                <h3>Total Rooms</h3>

                <h1>
                    <?php echo $room; ?>
                </h1>

            </div>

        </a>

        <!-- Total Bookings -->
        <a href="bookings.php" class="card-link">

            <div class="card">

                <i class="fa fa-calendar-check"></i>

                <h3>Total Bookings</h3>

                <h1>
                    <?php echo $booking; ?>
                </h1>

            </div>

        </a>


        <!-- Total Revenue -->
        <a href="payment.php" class="card-link">

            <div class="card">

                <i class="fa fa-money-bill"></i>

                <h3>Total Revenue</h3>

                <h1>
                    ₹ <?php echo number_format($payment, 2); ?>
                </h1>

            </div>

        </a>
         <!-- Total Feedback -->
        <a href="admin_feedback.php" class="card-link">

            <div class="card">

                <i class="fa fa-comments"></i>

                <h3>Total Feedback</h3>

            </div>

        </a>

    </div>

</div>

</body>
</html>