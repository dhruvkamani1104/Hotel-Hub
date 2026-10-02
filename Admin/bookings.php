<?php
session_start();
include("../Config/connection.php");

// Admin Authentication
if (!isset($_SESSION['username']) || $_SESSION['role'] != "Admin") {
    header("Location: ../login.php");
    exit();
}

/* =====================================================
   AUTO COMPLETE EXPIRED BOOKINGS
===================================================== */

$expiredQuery = mysqli_query(
    $conn,
    "SELECT booking_id, room_id
     FROM bookings
     WHERE booking_status = 'Confirmed'
     AND check_out <= NOW()"
);

while ($expired = mysqli_fetch_assoc($expiredQuery)) {

    $bookingId = $expired['booking_id'];
    $roomId = $expired['room_id'];

    // Booking status Completed
    $updateBooking = mysqli_prepare(
        $conn,
        "UPDATE bookings
         SET booking_status = 'Completed'
         WHERE booking_id = ?"
    );

    mysqli_stmt_bind_param($updateBooking, "i", $bookingId);
    mysqli_stmt_execute($updateBooking);
    mysqli_stmt_close($updateBooking);

    // Check if room has another active confirmed booking
    $checkRoom = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM bookings
         WHERE room_id = ?
         AND booking_status = 'Confirmed'
         AND check_out > NOW()"
    );

    mysqli_stmt_bind_param($checkRoom, "i", $roomId);
    mysqli_stmt_execute($checkRoom);

    $roomResult = mysqli_stmt_get_result($checkRoom);
    $roomData = mysqli_fetch_assoc($roomResult);

    mysqli_stmt_close($checkRoom);

    // Make room available if no active booking exists
    if ($roomData['total'] == 0) {

        $updateRoom = mysqli_prepare(
            $conn,
            "UPDATE rooms
             SET status = 'Available'
             WHERE room_id = ?"
        );

        mysqli_stmt_bind_param($updateRoom, "i", $roomId);
        mysqli_stmt_execute($updateRoom);
        mysqli_stmt_close($updateRoom);
    }
}


/* =====================================================
   ACCEPT BOOKING
===================================================== */

if (isset($_GET['accept'])) {

    $bookingId = intval($_GET['accept']);

    mysqli_begin_transaction($conn);

    try {

        // Get booking details
        $getBooking = mysqli_prepare(
            $conn,
            "SELECT room_id, total_amount
             FROM bookings
             WHERE booking_id = ?
             AND booking_status = 'Pending'"
        );

        mysqli_stmt_bind_param($getBooking, "i", $bookingId);
        mysqli_stmt_execute($getBooking);

        $bookingResult = mysqli_stmt_get_result($getBooking);
        $bookingData = mysqli_fetch_assoc($bookingResult);

        mysqli_stmt_close($getBooking);

        if (!$bookingData) {
            throw new Exception("Booking is not pending or does not exist.");
        }

        $roomId = $bookingData['room_id'];
        $totalAmount = $bookingData['total_amount'];

        // Update booking status
        $updateBooking = mysqli_prepare(
            $conn,
            "UPDATE bookings
             SET booking_status = 'Confirmed'
             WHERE booking_id = ?
             AND booking_status = 'Pending'"
        );

        mysqli_stmt_bind_param($updateBooking, "i", $bookingId);
        mysqli_stmt_execute($updateBooking);
        mysqli_stmt_close($updateBooking);

        // Update room status
        $updateRoom = mysqli_prepare(
            $conn,
            "UPDATE rooms
             SET status = 'Booked'
             WHERE room_id = ?"
        );

        mysqli_stmt_bind_param($updateRoom, "i", $roomId);
        mysqli_stmt_execute($updateRoom);
        mysqli_stmt_close($updateRoom);

        // Check if payment already exists
        $checkPayment = mysqli_prepare(
            $conn,
            "SELECT payment_id
             FROM payments
             WHERE booking_id = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($checkPayment, "i", $bookingId);
        mysqli_stmt_execute($checkPayment);

        $paymentResult = mysqli_stmt_get_result($checkPayment);
        $paymentExists = mysqli_num_rows($paymentResult);

        mysqli_stmt_close($checkPayment);

        // Automatically create payment
        if ($paymentExists == 0) {

            $paymentMethod = "Online";
            $paymentStatus = "Paid";

            $insertPayment = mysqli_prepare(
                $conn,
                "INSERT INTO payments
                (
                    booking_id,
                    amount,
                    payment_method,
                    payment_status
                )
                VALUES (?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $insertPayment,
                "idss",
                $bookingId,
                $totalAmount,
                $paymentMethod,
                $paymentStatus
            );

            mysqli_stmt_execute($insertPayment);
            mysqli_stmt_close($insertPayment);
        }

        mysqli_commit($conn);

        header("Location: bookings.php?success=accepted");
        exit();

    } catch (Exception $e) {

        mysqli_rollback($conn);

        header("Location: bookings.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}


/* =====================================================
   COMPLETE BOOKING & RELEASE ROOM
===================================================== */

if (isset($_GET['complete'])) {

    $bookingId = intval($_GET['complete']);

    mysqli_begin_transaction($conn);

    try {

        $getBooking = mysqli_prepare(
            $conn,
            "SELECT room_id
             FROM bookings
             WHERE booking_id = ?
             AND booking_status = 'Confirmed'
             FOR UPDATE"
        );

        mysqli_stmt_bind_param($getBooking, "i", $bookingId);
        mysqli_stmt_execute($getBooking);

        $bookingResult = mysqli_stmt_get_result($getBooking);
        $bookingData = mysqli_fetch_assoc($bookingResult);
        mysqli_stmt_close($getBooking);

        if (!$bookingData) {
            throw new Exception("Only confirmed bookings can be completed.");
        }

        $roomId = (int)$bookingData['room_id'];

        $updateBooking = mysqli_prepare(
            $conn,
            "UPDATE bookings
             SET booking_status = 'Completed'
             WHERE booking_id = ?
             AND booking_status = 'Confirmed'"
        );

        mysqli_stmt_bind_param($updateBooking, "i", $bookingId);
        mysqli_stmt_execute($updateBooking);
        mysqli_stmt_close($updateBooking);

        $checkRoom = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) AS total
             FROM bookings
             WHERE room_id = ?
             AND booking_status = 'Confirmed'
             AND check_out > NOW()"
        );

        mysqli_stmt_bind_param($checkRoom, "i", $roomId);
        mysqli_stmt_execute($checkRoom);
        $roomResult = mysqli_stmt_get_result($checkRoom);
        $roomData = mysqli_fetch_assoc($roomResult);
        mysqli_stmt_close($checkRoom);

        if ((int)$roomData['total'] == 0) {
            $updateRoom = mysqli_prepare(
                $conn,
                "UPDATE rooms SET status = 'Available' WHERE room_id = ?"
            );

            mysqli_stmt_bind_param($updateRoom, "i", $roomId);
            mysqli_stmt_execute($updateRoom);
            mysqli_stmt_close($updateRoom);
        }

        mysqli_commit($conn);
        header("Location: bookings.php?success=completed");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        header("Location: bookings.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}


/* =====================================================
   REJECT BOOKING
===================================================== */

if (isset($_GET['reject'])) {

    $bookingId = intval($_GET['reject']);

    mysqli_begin_transaction($conn);

    try {

        // Get room ID
        $getBooking = mysqli_prepare(
            $conn,
            "SELECT room_id
             FROM bookings
             WHERE booking_id = ?
             AND booking_status = 'Pending'"
        );

        mysqli_stmt_bind_param($getBooking, "i", $bookingId);
        mysqli_stmt_execute($getBooking);

        $bookingResult = mysqli_stmt_get_result($getBooking);
        $bookingData = mysqli_fetch_assoc($bookingResult);

        mysqli_stmt_close($getBooking);

        if (!$bookingData) {
            throw new Exception("Booking is not pending or does not exist.");
        }

        $roomId = $bookingData['room_id'];

        // Update booking status
        $updateBooking = mysqli_prepare(
            $conn,
            "UPDATE bookings
             SET booking_status = 'Cancelled'
             WHERE booking_id = ?
             AND booking_status = 'Pending'"
        );

        mysqli_stmt_bind_param($updateBooking, "i", $bookingId);
        mysqli_stmt_execute($updateBooking);
        mysqli_stmt_close($updateBooking);

        // Check for other active bookings
        $checkRoom = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) AS total
             FROM bookings
             WHERE room_id = ?
             AND booking_status = 'Confirmed'
             AND check_out > NOW()"
        );

        mysqli_stmt_bind_param($checkRoom, "i", $roomId);
        mysqli_stmt_execute($checkRoom);

        $roomResult = mysqli_stmt_get_result($checkRoom);
        $roomData = mysqli_fetch_assoc($roomResult);

        mysqli_stmt_close($checkRoom);

        // Make room available if no confirmed booking exists
        if ($roomData['total'] == 0) {

            $updateRoom = mysqli_prepare(
                $conn,
                "UPDATE rooms
                 SET status = 'Available'
                 WHERE room_id = ?"
            );

            mysqli_stmt_bind_param($updateRoom, "i", $roomId);
            mysqli_stmt_execute($updateRoom);
            mysqli_stmt_close($updateRoom);
        }

        mysqli_commit($conn);

        header("Location: bookings.php?success=rejected");
        exit();

    } catch (Exception $e) {

        mysqli_rollback($conn);

        header("Location: bookings.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}


/* =====================================================
   DASHBOARD STATISTICS
===================================================== */

// Total bookings
$totalBookingQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM bookings"
);

$totalBookingData = mysqli_fetch_assoc($totalBookingQuery);
$totalBookings = $totalBookingData['total'];

// Available rooms
$availableRoomQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM rooms
     WHERE status = 'Available'"
);

$availableRoomData = mysqli_fetch_assoc($availableRoomQuery);
$availableRooms = $availableRoomData['total'];


/* =====================================================
   GET ALL BOOKINGS
===================================================== */

$bookingsQuery = mysqli_query(
    $conn,
    "SELECT
        b.booking_id,
        b.check_in,
        b.check_out,
        b.booking_date,
        b.total_amount,
        b.booking_status,
        c.first_name,
        c.last_name,
        c.email,
        r.room_number,
        r.room_type
     FROM bookings b
     INNER JOIN customers c
        ON b.customer_id = c.customer_id
     INNER JOIN rooms r
        ON b.room_id = r.room_id
     ORDER BY b.booking_id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Bookings</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .container {
            width: 95%;
            margin: 30px auto;
        }

        h1 {
            color: #1e3c72;
            margin-bottom: 25px;
        }

        /* Statistics */

        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            min-width: 220px;
            flex: 1;
        }

        .stat-card h3 {
            margin: 0 0 10px;
            color: #555;
            font-size: 18px;
        }

        .stat-card p {
            margin: 0;
            color: #1e3c72;
            font-size: 32px;
            font-weight: bold;
        }

        /* Messages */

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        /* Table */

        .table-container {
            overflow-x: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1100px;
        }

        th {
            background: #1e3c72;
            color: white;
            padding: 14px;
            text-align: left;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #f5f5f5;
        }

        /* Status */

        .status {
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 13px;
            font-weight: bold;
            display: inline-block;
        }

        .pending {
            background: #fff3cd;
            color: #856404;
        }

        .confirmed {
            background: #d4edda;
            color: #155724;
        }

        .cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .completed {
            background: #cce5ff;
            color: #004085;
        }

        /* Buttons */

        .btn {
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
            color: white;
            font-size: 13px;
            margin-right: 5px;
            display: inline-block;
        }

        .accept {
            background: #28a745;
        }

        .reject {
            background: #dc3545;
        }

        .complete {
            background: #007bff;
        }

        .btn:hover {
            opacity: 0.8;
        }

        .no-action {
            color: #777;
            font-size: 13px;
        }


        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 15px;
            flex-wrap: wrap;
        }

        .dashboard-btn {
            background: #1e3c72;
            color: white;
            padding: 10px 16px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            display: inline-block;
        }

        .dashboard-btn:hover {
            background: #142850;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="header-row">
        <h1>Manage Bookings</h1>

        <a href="dashboard.php" class="dashboard-btn">
            ← Back to Dashboard
        </a>
    </div>


    <!-- Success and Error Messages -->

    <?php if (isset($_GET['success'])): ?>

        <div class="message success">

            <?php
            if ($_GET['success'] == "accepted") {
                echo "Booking accepted and payment added successfully.";
            } elseif ($_GET['success'] == "rejected") {
                echo "Booking rejected successfully.";
            } elseif ($_GET['success'] == "completed") {
                echo "Booking completed and room is now available.";
            }
            ?>

        </div>

    <?php endif; ?>


    <?php if (isset($_GET['error'])): ?>

        <div class="message error">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>

    <?php endif; ?>


    <!-- Statistics Cards -->

    <div class="stats">

        <div class="stat-card">

            <h3>Total Bookings</h3>

            <p>
                <?php echo $totalBookings; ?>
            </p>

        </div>


        <div class="stat-card">

            <h3>Available Rooms</h3>

            <p>
                <?php echo $availableRooms; ?>
            </p>

        </div>

    </div>


    <!-- Bookings Table -->

    <div class="table-container">

        <table>

            <thead>

                <tr>

                    <th>Booking ID</th>

                    <th>Customer</th>

                    <th>Email</th>

                    <th>Room Number</th>

                    <th>Room Type</th>

                    <th>Check In</th>

                    <th>Check Out</th>

                    <th>Booking Date</th>

                    <th>Total Amount</th>

                    <th>Status</th>

                    <th>Action</th>

                </tr>

            </thead>

            <tbody>

                <?php if (mysqli_num_rows($bookingsQuery) > 0): ?>

                    <?php while ($booking = mysqli_fetch_assoc($bookingsQuery)): ?>

                        <tr>

                            <td>
                                <?php echo $booking['booking_id']; ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $booking['first_name'] . " " . $booking['last_name']
                                );
                                ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($booking['email']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($booking['room_number']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($booking['room_type']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($booking['check_in']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($booking['check_out']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($booking['booking_date']); ?>
                            </td>

                            <td>
                                ₹<?php echo number_format($booking['total_amount'], 2); ?>
                            </td>

                            <td>

                                <?php
                                $status = $booking['booking_status'];
                                $statusClass = strtolower($status);
                                ?>

                                <span class="status <?php echo $statusClass; ?>">

                                    <?php echo htmlspecialchars($status); ?>

                                </span>

                            </td>

                            <td>

                                <?php if ($status == "Pending"): ?>

                                    <a
                                        class="btn accept"
                                        href="bookings.php?accept=<?php echo $booking['booking_id']; ?>"
                                        onclick="return confirm('Are you sure you want to accept this booking?');"
                                    >
                                        Accept
                                    </a>

                                    <a
                                        class="btn reject"
                                        href="bookings.php?reject=<?php echo $booking['booking_id']; ?>"
                                        onclick="return confirm('Are you sure you want to reject this booking?');"
                                    >
                                        Reject
                                    </a>

                                <?php elseif ($status == "Confirmed"): ?>

                                    <a
                                        class="btn complete"
                                        href="bookings.php?complete=<?php echo $booking['booking_id']; ?>"
                                        onclick="return confirm('Complete booking and release room?');"
                                    >
                                        Complete & Release
                                    </a>

                                <?php else: ?>

                                    <span class="no-action">
                                        No Action
                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="11" style="text-align:center;">
                            No bookings found.
                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>

</html>