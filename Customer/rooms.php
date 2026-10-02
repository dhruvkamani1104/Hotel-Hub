<?php
session_start();
include("../Config/connection.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Available Rooms</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>

        body {
            background: #f4f7fc;
        }

        .navbar {
            background: #0d6efd;
            padding: 14px 0;
        }

        .navbar-brand {
            color: white !important;
            font-size: 22px;
            font-weight: 600;
        }

        .back-dashboard {
            background: white;
            color: #0d6efd;
            padding: 9px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .back-dashboard:hover {
            background: #e8f0ff;
            color: #084298;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.15);
            transition: 0.3s;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .room-photo {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }

        .no-photo {
            width: 100%;
            height: 220px;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #777;
            font-size: 18px;
        }

        .price {
            font-size: 22px;
            color: #198754;
            font-weight: bold;
        }

        .badge {
            font-size: 14px;
        }

        .room-title {
            font-weight: 600;
            color: #212529;
        }

        @media (max-width: 576px) {

            .navbar-brand {
                font-size: 18px;
            }

            .back-dashboard {
                padding: 8px 12px;
                font-size: 14px;
            }

            .room-photo,
            .no-photo {
                height: 200px;
            }

        }

    </style>

</head>


<body>


<!-- HEADER -->

<nav class="navbar">

    <div class="container d-flex justify-content-between align-items-center">

        <a class="navbar-brand" href="dashboard.php">

            <i class="fa-solid fa-hotel"></i>

            Hotel Booking

        </a>


        <a href="dashboard.php" class="back-dashboard">

            <i class="fa-solid fa-arrow-left"></i>

            Back to Dashboard

        </a>

    </div>

</nav>



<!-- AVAILABLE ROOMS -->

<div class="container mt-5 mb-5">

    <h2 class="text-center mb-4">

        <i class="fa-solid fa-bed text-primary"></i>

        Available Rooms

    </h2>


    <div class="row">


        <?php

        $result = mysqli_query(
            $conn,
            "SELECT * FROM rooms WHERE status = 'Available'"
        );


        if ($result && mysqli_num_rows($result) > 0) {

            while ($row = mysqli_fetch_assoc($result)) {

        ?>


                <div class="col-md-4 mb-4">


                    <div class="card h-100">


                        <!-- ROOM PHOTO -->

                        <?php

                        if (!empty($row['room_image'])) {

                        ?>

                            <img
                                src="../<?php
                                echo htmlspecialchars(
                                    $row['room_image']
                                );
                                ?>"
                                alt="Room Photo"
                                class="room-photo">


                        <?php

                        } else {

                        ?>

                            <div class="no-photo">

                                <i class="fa-solid fa-bed fa-4x"></i>

                            </div>


                        <?php

                        }

                        ?>


                        <!-- ROOM DETAILS -->

                        <div class="card-body text-center">


                            <h4 class="room-title">

                                Room
                                <?php
                                echo htmlspecialchars(
                                    $row['room_number']
                                );
                                ?>

                            </h4>


                            <p>

                                <strong>Type:</strong>

                                <?php
                                echo htmlspecialchars(
                                    $row['room_type']
                                );
                                ?>

                            </p>


                            <p class="price">

                                ₹ <?php
                                echo number_format(
                                    (float) $row['price'],
                                    2
                                );
                                ?>

                            </p>


                            <p>

                                <strong>Capacity:</strong>

                                <?php
                                echo htmlspecialchars(
                                    $row['capacity']
                                );
                                ?>

                                Person(s)

                            </p>


                            <p>

                                <span class="badge bg-success">

                                    <i class="fa-solid fa-check"></i>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['status']
                                    );
                                    ?>

                                </span>

                            </p>


                            <a
                                href="book_room.php?id=<?php
                                echo (int) $row['room_id'];
                                ?>"
                                class="btn btn-primary">

                                <i class="fa-solid fa-calendar-check"></i>

                                Book Now

                            </a>


                        </div>


                    </div>


                </div>


        <?php

            }

        } else {

        ?>


            <div class="col-12">

                <div class="alert alert-danger text-center">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    No Rooms Available

                </div>

            </div>


        <?php

        }

        ?>


    </div>

</div>


</body>

</html>