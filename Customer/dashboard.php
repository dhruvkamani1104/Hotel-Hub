<?php
session_start();
if (!isset($_SESSION['username'])) { header('Location: login.php'); exit(); }
$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Customer Dashboard | Hotel Hub</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
        <style>
            * {
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Segoe UI',sans-serif
            }

            body {
            background:#f4f7fc;
            color:#333
            }

            .main {
            width:100%;
            padding:25px 40px
            }

            .navbar {
            background:#fff;
            padding:18px 25px;
            border-radius:14px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            box-shadow:0 4px 15px #00000012
            }

            .navbar h3 {
            font-size:22px;
            color:#17324d
            }

            .profile-area {
            display:flex;
            align-items:center;
            gap:20px
            }

            .profile {
            font-weight:600;
            color:#0d6efd
            }

            .logout {
            background:#dc3545;
            color:#fff;
            padding:10px 18px;
            border-radius:8px;
            text-decoration:none;
            font-weight:600
            }

            .logout:hover {
            background:#bb2d3b
            }

            .slider {
            position:relative;
            height:390px;
            margin-top:28px;
            border-radius:20px;
            overflow:hidden;
            box-shadow:0 8px 25px #0003
            }

            .slide {
            display:none;
            width:100%;
            height:100%;
            position:relative
            }

            .slide.active {
            display:block
            }

            .slide img {
            width:100%;
            height:100%;
            object-fit:cover
            }

            .slide-overlay {
            position:absolute;
            inset:0;
            background:linear-gradient(90deg,#000b,#0001);
            display:flex;
            flex-direction:column;
            justify-content:center;
            padding:45px;
            color:white
            }

            .slide-overlay h1 {
            font-size:42px;
            margin-bottom:12px
            }

            .slide-overlay p {
            font-size:18px;
            max-width:520px
            }

            .slider-btn {
            position:absolute;
            top:50%;
            transform:translateY(-50%);
            border:0;
            background:#ffffffb5;
            color:#17324d;
            width:42px;
            height:42px;
            border-radius:50%;
            cursor:pointer;
            font-size:20px
            }

            .prev {
            left:18px
            }

            .next {
            right:18px
            }

            .dots {
            position:absolute;
            bottom:18px;
            left:50%;
            transform:translateX(-50%);
            display:flex;
            gap:8px
            }

            .dot {
            width:11px;
            height:11px;
            background:#ffffff99;
            border-radius:50%;
            cursor:pointer
            }

            .dot.active {
            background:#fff
            }

            .welcome {
            margin-top:30px;
            background:linear-gradient(90deg,#0d6efd,#4facfe);
            color:#fff;
            padding:30px;
            border-radius:16px;
            box-shadow:0 8px 20px #0d6efd33
            }

            .welcome h1 {
            margin-bottom:10px;
            font-size:32px
            }

            .cards {
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
            gap:25px;
            margin-top:30px
            }

            .card-link {
            text-decoration:none;
            color:inherit
            }

            .card {
            background:#fff;
            padding:28px;
            border-radius:16px;
            min-height:190px;
            box-shadow:0 5px 18px #0000001a;
            transition:.3s
            }

            .card:hover {
            transform:translateY(-7px);
            box-shadow:0 12px 25px #00000026
            }

            .card i {
            font-size:42px;
            color:#0d6efd;
            margin-bottom:18px
            }

            .card h2 {
            margin-bottom:8px
            }

            .card p {
            color:#777;
            font-size:15px
            }

            .bottom-logout {
            margin-top:35px;
            background:#fff;
            padding:20px;
            border-radius:12px;
            box-shadow:0 4px 15px #00000014;
            color:#666
            }

            @media(max-width:700px) {
            .main {
            padding:15px
            }

            .navbar {
            flex-direction:column;
            gap:15px;
            text-align:center
            }

            .profile-area {
            flex-direction:column;
            gap:10px
            }

            .slider {
            height:300px
            }

            .slide-overlay {
            padding:25px
            }

            .slide-overlay h1 {
            font-size:28px
            }

            .slide-overlay p {
            font-size:14px
            }

            .welcome h1 {
            font-size:25px
            }

            }
        </style>
    </head>
    <body>
        <div class="main">
            <div class="navbar">
                <h3>
                    <i class="fa-solid fa-hotel">
                </i> Customer Dashboard</h3>
                <div class="profile-area">
                    <div class="profile">
                        <i class="fa-solid fa-user">
                    </i> Welcome, <?php echo $username; ?>
                </div>
                <a href="login.php" class="logout">
                    <i class="fa-solid fa-right-from-bracket">
                </i> Logout</a>
            </div>
        </div>
        <div class="slider">
            <div class="slide active">
                <img src="../assets/images/hotel-photo1.jpg" alt="Hotel Photo 1">
                <div class="slide-overlay">
                    <h1>Welcome to Hotel Hub</h1>
                    <p>Experience comfort, luxury and unforgettable hospitality.</p>
                </div>
            </div>
            <div class="slide">
                <img src="../assets/images/hotel-photo2.jpg" alt="Hotel Photo 2">
                <div class="slide-overlay">
                    <h1>Relax in Comfort</h1>
                    <p>Choose the perfect room for your memorable stay.</p>
                </div>
            </div>
            <div class="slide">
                <img src="../assets/images/hotel-photo3.jpg" alt="Hotel Photo 3">
                <div class="slide-overlay">
                    <h1>Book Your Stay</h1>
                    <p>Explore available rooms and reserve your favourite room easily.</p>
                </div>
            </div>
            <button class="slider-btn prev" onclick="changeSlide(-1)">&#10094;</button>
            <button class="slider-btn next" onclick="changeSlide(1)">&#10095;</button>
            <div class="dots">
                <span class="dot active" onclick="showSlide(0)">
                </span>
                <span class="dot" onclick="showSlide(1)">
                </span>
                <span class="dot" onclick="showSlide(2)">
                </span>
            </div>
        </div>
        <div class="welcome">
            <h1>Hello <?php echo $username; ?> 👋</h1>
            <p>Welcome to the Hotel Booking Management System. Book your favorite room quickly and easily.</p>
        </div>
        <div class="cards">
            <a href="rooms.php" class="card-link">
                <div class="card">
                    <i class="fa-solid fa-bed">
                </i>
                <h2>Rooms</h2>
                <p>View available rooms and explore hotel facilities.</p>
            </div>
        </a>
    <a href="my_booking.php" class="card-link">
        <div class="card">
            <i class="fa-solid fa-book-open">
        </i>
        <h2>My Bookings</h2>
        <p>View your previous and current bookings.</p>
    </div>
</a>
<a href="profile.php" class="card-link">
    <div class="card">
        <i class="fa-solid fa-user">
    </i>
    <h2>Profile</h2>
    <p>Manage your personal account information.</p>
</div>
</a>
 <a href="feedback.php" class="card-link">
        <div class="card">
            <i class="fa-solid fa-comments">
        </i>
        <h2>Feedback</h2>
        <p>Enter your Feedback</p>
    </div>
</a>
</div>
<div class="bottom-logout">
    <p>
        <i class="fa-solid fa-circle-info">
    </i> Thank you for using Hotel Hub.</p>
</div>
</div>
<script>
    let currentSlide=0;

    const slides=document.querySelectorAll('.slide'),dots=document.querySelectorAll('.dot');

    function showSlide(index){
    currentSlide=(index+slides.length)%slides.length;
    slides.forEach((s,i)=>s.classList.toggle('active',i===currentSlide));
    dots.forEach((d,i)=>d.classList.toggle('active',i===currentSlide));

    }

    function changeSlide(direction){
    showSlide(currentSlide+direction)
    }
    setInterval(()=>changeSlide(1),5000);
</script>
</body>
</html>
