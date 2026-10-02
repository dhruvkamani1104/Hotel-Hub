<?php

session_start();

include("../Config/connection.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];


/* =========================================
   CURRENT USER
========================================= */

$userQuery = mysqli_query(
    $conn,
    "SELECT * FROM users WHERE username='$username'"
);

$user = mysqli_fetch_assoc($userQuery);

if (!$user) {
    die("User Not Found.");
}

$email = $user['email'];


/* =========================================
   CUSTOMER ID
========================================= */

$customerQuery = mysqli_query(
    $conn,
    "SELECT * FROM customers WHERE email='$email'"
);

$customer = mysqli_fetch_assoc($customerQuery);

if (!$customer) {
    die("No Booking Found.");
}

$customer_id = $customer['customer_id'];


/* =========================================
   AUTO COMPLETE EXPIRED BOOKINGS
========================================= */

$completeQuery = mysqli_query(
    $conn,
    "UPDATE bookings
     SET booking_status='Completed'
     WHERE customer_id='$customer_id'
     AND booking_status='Confirmed'
     AND check_out <= NOW()"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Bookings</title>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">


    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">


    <style>

        body {
            background: #f4f6f9;
        }

        .container {
            margin-top: 40px;
        }

        .card {
            box-shadow: 0 0 15px rgba(0,0,0,.1);
            border: none;
            border-radius: 10px;
        }

        table th {
            background: #0d6efd !important;
            color: white !important;
            text-align: center;
            white-space: nowrap;
        }

        table td {
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }

        .badge {
            font-size: 14px;
            padding: 8px 12px;
        }

        .table-responsive {
            overflow-x: auto;
        }

    </style>

</head>


<body>


<div class="container">


    <div class="card">


        <!-- HEADER -->

        <div class="card-header bg-primary text-white">

            <h3 class="mb-0">

                <i class="fa fa-calendar-check"></i>

                My Bookings

            </h3>

        </div>



        <div class="card-body">


            <div class="table-responsive">


                <table class="table table-bordered table-hover">


                    <thead>

                        <tr>

                            <th>Booking ID</th>

                            <th>Room No</th>

                            <th>Room Type</th>

                            <th>Check In</th>

                            <th>Check Out</th>

                            <th>Total Amount</th>

                            <th>Booking Status</th>

                            <th>Payment Status</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    /* =========================================
                       FETCH BOOKINGS WITH PAYMENT STATUS
                    ========================================= */

                    $sql = "SELECT
                                bookings.*,
                                rooms.room_number,
                                rooms.room_type,
                                payments.payment_status

                            FROM bookings

                            INNER JOIN rooms
                            ON bookings.room_id = rooms.room_id

                            LEFT JOIN payments
                            ON bookings.booking_id = payments.booking_id

                            WHERE bookings.customer_id = '$customer_id'

                            ORDER BY bookings.booking_id DESC";


                    $result = mysqli_query($conn, $sql);


                    if ($result && mysqli_num_rows($result) > 0) {


                        while ($row = mysqli_fetch_assoc($result)) {

                    ?>


                        <tr>


                            <!-- BOOKING ID -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row['booking_id']
                                );

                                ?>

                            </td>



                            <!-- ROOM NUMBER -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row['room_number']
                                );

                                ?>

                            </td>



                            <!-- ROOM TYPE -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row['room_type']
                                );

                                ?>

                            </td>



                            <!-- CHECK IN -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row['check_in']
                                );

                                ?>

                            </td>



                            <!-- CHECK OUT -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $row['check_out']
                                );

                                ?>

                            </td>



                            <!-- TOTAL AMOUNT -->

                            <td>

                                ₹

                                <?php

                                echo number_format(
                                    (float) $row['total_amount'],
                                    2
                                );

                                ?>

                            </td>



                            <!-- BOOKING STATUS -->

                            <td>


                                <?php

                                $booking_status =
                                    $row['booking_status'];


                                if ($booking_status == "Pending") {

                                    echo "<span class='badge bg-warning text-dark'>
                                            <i class='fa fa-clock'></i>
                                            Pending
                                          </span>";

                                }

                                elseif ($booking_status == "Confirmed") {

                                    echo "<span class='badge bg-success'>
                                            <i class='fa fa-check-circle'></i>
                                            Confirmed
                                          </span>";

                                }

                                elseif ($booking_status == "Completed") {

                                    echo "<span class='badge bg-primary'>
                                            <i class='fa fa-check-double'></i>
                                            Completed
                                          </span>";

                                }

                                elseif ($booking_status == "Cancelled") {

                                    echo "<span class='badge bg-danger'>
                                            <i class='fa fa-times-circle'></i>
                                            Cancelled
                                          </span>";

                                }

                                else {

                                    echo "<span class='badge bg-secondary'>"
                                            . htmlspecialchars($booking_status) .
                                         "</span>";

                                }

                                ?>


                            </td>



                            <!-- PAYMENT STATUS -->

                            <td>


                                <?php

                                $payment_status =
                                    $row['payment_status'] ?? null;


                                if ($payment_status == "Paid") {

                                    echo "<span class='badge bg-success'>
                                            <i class='fa fa-check-circle'></i>
                                            Paid
                                          </span>";

                                }

                                elseif ($payment_status == "Failed") {

                                    echo "<span class='badge bg-danger'>
                                            <i class='fa fa-times-circle'></i>
                                            Failed
                                          </span>";

                                }

                                elseif ($payment_status == "Pending") {

                                    echo "<span class='badge bg-warning text-dark'>
                                            <i class='fa fa-clock'></i>
                                            Pending
                                          </span>";

                                }

                                elseif ($payment_status == "Refunded") {

                                    echo "<span class='badge bg-info text-dark'>
                                            <i class='fa fa-rotate-left'></i>
                                            Refunded
                                          </span>";

                                }

                                elseif ($payment_status == "Cancelled") {

                                    echo "<span class='badge bg-danger'>
                                            <i class='fa fa-ban'></i>
                                            Cancelled
                                          </span>";

                                }

                                else {

                                    echo "<span class='badge bg-secondary'>
                                            N/A
                                          </span>";

                                }

                                ?>


                            </td>


                        </tr>


                    <?php

                        }

                    }

                    else {

                    ?>


                        <tr>

                            <td colspan="8">

                                No Booking Found

                            </td>

                        </tr>


                    <?php

                    }

                    ?>


                    </tbody>


                </table>


            </div>


            <br>


            <!-- BACK TO DASHBOARD -->

            <a href="dashboard.php"
               class="btn btn-primary">

                <i class="fa fa-arrow-left"></i>

                Back to Dashboard

            </a>


        </div>


    </div>


</div>


</body>

</html>