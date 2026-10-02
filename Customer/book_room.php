<?php
session_start();
include("../Config/connection.php");

// ======================================
// LOGIN CHECK
// ======================================

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}


// ======================================
// ROOM ID CHECK
// ======================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: rooms.php");
    exit();
}

$room_id = (int) $_GET['id'];


// ======================================
// GET ONLY AVAILABLE ROOM
// ======================================

$roomQuery = mysqli_prepare(
    $conn,
    "SELECT *
     FROM rooms
     WHERE room_id = ?
     AND status = 'Available'"
);

mysqli_stmt_bind_param(
    $roomQuery,
    "i",
    $room_id
);

mysqli_stmt_execute($roomQuery);

$roomResult = mysqli_stmt_get_result($roomQuery);

if (!$roomResult || mysqli_num_rows($roomResult) == 0) {

    echo "<script>
            alert('This room is not available.');
            window.location='rooms.php';
          </script>";

    exit();
}

$roomData = mysqli_fetch_assoc($roomResult);

mysqli_stmt_close($roomQuery);


// ======================================
// LOGIN USER DETAILS
// ======================================

$username = $_SESSION['username'];

$userQuery = mysqli_prepare(
    $conn,
    "SELECT *
     FROM users
     WHERE username = ?"
);

mysqli_stmt_bind_param(
    $userQuery,
    "s",
    $username
);

mysqli_stmt_execute($userQuery);

$userResult = mysqli_stmt_get_result($userQuery);

$user = mysqli_fetch_assoc($userResult);

mysqli_stmt_close($userQuery);

$user_email = $user['email'];


// ======================================
// BOOK ROOM
// ======================================

if (isset($_POST['book'])) {

    $first_name = mysqli_real_escape_string(
        $conn,
        $_POST['first_name']
    );

    $last_name = mysqli_real_escape_string(
        $conn,
        $_POST['last_name']
    );

    $gender = mysqli_real_escape_string(
        $conn,
        $_POST['gender']
    );

    $phone = mysqli_real_escape_string(
        $conn,
        $_POST['phone']
    );

    $email = mysqli_real_escape_string(
        $conn,
        $_POST['email']
    );

    $address = mysqli_real_escape_string(
        $conn,
        $_POST['address']
    );

    $id_proof = mysqli_real_escape_string(
        $conn,
        $_POST['id_proof']
    );

    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];


    // ======================================
    // DATE VALIDATION
    // ======================================

    if (empty($check_in) || empty($check_out)) {

        echo "<script>
                alert('Please select check-in and check-out dates.');
              </script>";

    } elseif ($check_out <= $check_in) {

        echo "<script>
                alert('Check-out date must be after check-in date.');
              </script>";

    } elseif ($check_in < date('Y-m-d')) {

        echo "<script>
                alert('Check-in date cannot be in the past.');
              </script>";

    } else {


        // ======================================
        // RECHECK ROOM AVAILABILITY
        // ======================================

        $availabilityQuery = mysqli_prepare(
            $conn,
            "SELECT status
             FROM rooms
             WHERE room_id = ?
             FOR UPDATE"
        );

        mysqli_stmt_bind_param(
            $availabilityQuery,
            "i",
            $room_id
        );

        mysqli_stmt_execute($availabilityQuery);

        $availabilityResult = mysqli_stmt_get_result(
            $availabilityQuery
        );

        $currentRoom = mysqli_fetch_assoc(
            $availabilityResult
        );

        mysqli_stmt_close($availabilityQuery);


        if (
            !$currentRoom ||
            strtolower($currentRoom['status']) != 'available'
        ) {

            echo "<script>
                    alert('Sorry, this room is already booked.');
                    window.location='rooms.php';
                  </script>";

            exit();

        }


        // ======================================
        // CUSTOMER EXISTS CHECK
        // ======================================

        $checkCustomer = mysqli_prepare(
            $conn,
            "SELECT customer_id
             FROM customers
             WHERE email = ?"
        );

        mysqli_stmt_bind_param(
            $checkCustomer,
            "s",
            $email
        );

        mysqli_stmt_execute($checkCustomer);

        $customerResult = mysqli_stmt_get_result(
            $checkCustomer
        );


        if (
            $customerResult &&
            mysqli_num_rows($customerResult) > 0
        ) {

            $cust = mysqli_fetch_assoc($customerResult);

            $customer_id = $cust['customer_id'];

        } else {

            // ======================================
            // INSERT NEW CUSTOMER
            // ======================================

            $customerQuery = mysqli_prepare(
                $conn,
                "INSERT INTO customers
                (
                    first_name,
                    last_name,
                    gender,
                    phone,
                    email,
                    address,
                    id_proof
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $customerQuery,
                "sssssss",
                $first_name,
                $last_name,
                $gender,
                $phone,
                $email,
                $address,
                $id_proof
            );

            if (mysqli_stmt_execute($customerQuery)) {

                $customer_id = mysqli_insert_id($conn);

            } else {

                die(mysqli_error($conn));
            }

            mysqli_stmt_close($customerQuery);
        }

        mysqli_stmt_close($checkCustomer);


        $_SESSION['customer_id'] = $customer_id;


        // ======================================
        // TOTAL AMOUNT
        // ======================================

        $total_amount = (float) $roomData['price'];


        // ======================================
        // INSERT BOOKING
        // ======================================

        $bookingQuery = mysqli_prepare(
            $conn,
            "INSERT INTO bookings
            (
                customer_id,
                room_id,
                check_in,
                check_out,
                booking_date,
                total_amount,
                booking_status
            )
            VALUES (?, ?, ?, ?, NOW(), ?, 'Pending')"
        );

        mysqli_stmt_bind_param(
            $bookingQuery,
            "iissd",
            $customer_id,
            $room_id,
            $check_in,
            $check_out,
            $total_amount
        );


        if (mysqli_stmt_execute($bookingQuery)) {

            echo "<script>
                    alert('Room booked successfully. Waiting for admin confirmation.');
                    window.location='my_booking.php';
                  </script>";

            exit();

        } else {

            die(mysqli_error($conn));
        }

        mysqli_stmt_close($bookingQuery);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Book Room</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: #f5f5f5;
        }

        .card {
            margin-top: 30px;
            margin-bottom: 30px;
            border-radius: 15px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, .2);
        }

        .room-info {
            background: #f1f8f4;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .available {
            color: green;
            font-weight: bold;
        }

        .back-dashboard {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 18px;
            background: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }

        .back-dashboard:hover {
            background: #084298;
            color: white;
        }

    </style>

</head>


<body>

<div class="container">

    <div class="row justify-content-center">

        <div class="col-md-8">

            <div class="card">

                <div class="card-body">

                    <a href="dashboard.php" class="back-dashboard">
                        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                    </a>


                    <!-- ROOM DETAILS -->

                    <div class="room-info">

                        <h4>
                            Room No:
                            <?php
                            echo htmlspecialchars(
                                $roomData['room_number']
                            );
                            ?>
                        </h4>

                        <h5>
                            Room Type:
                            <?php
                            echo htmlspecialchars(
                                $roomData['room_type']
                            );
                            ?>
                        </h5>

                        <h5>
                            Price:
                            ₹<?php
                            echo number_format(
                                (float) $roomData['price'],
                                2
                            );
                            ?>
                        </h5>

                        <p class="available">
                            ● Available
                        </p>

                    </div>


                    <hr>


                    <!-- BOOKING FORM -->

                    <form method="POST">

                        <div class="row">


                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    First Name
                                </label>

                                <input
                                    type="text"
                                    name="first_name"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Last Name
                                </label>

                                <input
                                    type="text"
                                    name="last_name"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Gender
                                </label>

                                <select
                                    name="gender"
                                    class="form-control"
                                    required
                                >

                                    <option value="">
                                        Select
                                    </option>

                                    <option value="Male">
                                        Male
                                    </option>

                                    <option value="Female">
                                        Female
                                    </option>

                                    <option value="Other">
                                        Other
                                    </option>

                                </select>

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Phone
                                </label>

                                <input
                                    type="text"
                                    name="phone"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Email
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    value="<?php
                                    echo htmlspecialchars(
                                        $user_email
                                    );
                                    ?>"
                                    readonly
                                    required
                                >

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    ID Proof
                                </label>

                                <input
                                    type="text"
                                    name="id_proof"
                                    class="form-control"
                                    placeholder="Aadhar / PAN / Passport"
                                    required
                                >

                            </div>


                            <div class="col-md-12 mb-3">

                                <label class="form-label">
                                    Address
                                </label>

                                <textarea
                                    name="address"
                                    class="form-control"
                                    rows="3"
                                    required
                                ></textarea>

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Check In
                                </label>

                                <input
                                    type="date"
                                    name="check_in"
                                    class="form-control"
                                    min="<?php
                                    echo date('Y-m-d');
                                    ?>"
                                    required
                                >

                            </div>


                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Check Out
                                </label>

                                <input
                                    type="date"
                                    name="check_out"
                                    class="form-control"
                                    min="<?php
                                    echo date('Y-m-d', strtotime('+1 day'));
                                    ?>"
                                    required
                                >

                            </div>


                        </div>


                        <button
                            type="submit"
                            name="book"
                            class="btn btn-success w-100"
                        >
                            Confirm Booking
                        </button>


                        <br><br>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>