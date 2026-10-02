<?php
session_start();
include("../Config/connection.php");

// Admin Access
if (
    !isset($_SESSION['username']) ||
    $_SESSION['role'] !== "Admin"
) {
    header("Location: ../login.php");
    exit();
}


/* ================================
   UPDATE PAYMENT STATUS
================================ */

if (isset($_POST['update_status'])) {

    $payment_id = (int) $_POST['payment_id'];
    $payment_status = trim($_POST['payment_status']);

    $allowed_status = [
        'Pending',
        'Paid',
        'Failed',
        'Refunded',
        'Cancelled'
    ];

    if (in_array($payment_status, $allowed_status, true)) {

        $stmt = $conn->prepare(
            "UPDATE payments
             SET payment_status = ?
             WHERE payment_id = ?"
        );

        $stmt->bind_param(
            "si",
            $payment_status,
            $payment_id
        );

        $stmt->execute();
        $stmt->close();

        header("Location: payment.php?success=updated");
        exit();
    }

    header("Location: payment.php?error=invalid_status");
    exit();
}


/* ================================
   DELETE PAYMENT
================================ */

if (isset($_GET['delete'])) {

    $payment_id = (int) $_GET['delete'];

    $stmt = $conn->prepare(
        "DELETE FROM payments
         WHERE payment_id = ?"
    );

    $stmt->bind_param("i", $payment_id);

    $stmt->execute();
    $stmt->close();

    header("Location: payment.php?success=deleted");
    exit();
}


/* ================================
   DASHBOARD STATISTICS
================================ */

$total_payments = 0;
$paid_amount = 0;
$pending_amount = 0;
$failed_count = 0;


// Total Payments
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM payments"
);

if ($result) {

    $total_payments =
        (int) $result->fetch_assoc()['total'];
}


// Total Paid Amount
$result = $conn->query(
    "SELECT COALESCE(SUM(amount), 0) AS total
     FROM payments
     WHERE payment_status = 'Paid'"
);

if ($result) {

    $paid_amount =
        (float) $result->fetch_assoc()['total'];
}


// Pending Amount
$result = $conn->query(
    "SELECT COALESCE(SUM(amount), 0) AS total
     FROM payments
     WHERE payment_status = 'Pending'"
);

if ($result) {

    $pending_amount =
        (float) $result->fetch_assoc()['total'];
}


// Failed Payments
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM payments
     WHERE payment_status = 'Failed'"
);

if ($result) {

    $failed_count =
        (int) $result->fetch_assoc()['total'];
}


/* ================================
   PAYMENT LIST
================================ */

$sql = "SELECT
            p.payment_id,
            p.booking_id,
            p.amount,
            p.payment_method,
            p.payment_status,
            p.payment_date,

            b.check_in,
            b.check_out,

            c.first_name,
            c.last_name,

            r.room_number

        FROM payments p

        LEFT JOIN bookings b
            ON p.booking_id = b.booking_id

        LEFT JOIN customers c
            ON b.customer_id = c.customer_id

        LEFT JOIN rooms r
            ON b.room_id = r.room_id

        ORDER BY p.payment_id DESC";


$payments = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Payment Management</title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family: Arial, sans-serif;

            background: #f4f6f9;

            color: #222;

        }


        .container {

            width: 95%;

            max-width: 1400px;

            margin: 30px auto;

        }


        /* HEADER */

        .header-row {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 15px;

            margin-bottom: 20px;

        }


        .header-row h1 {

            margin: 0;

        }


        .dashboard-btn {

            background: #1e3c72;

            color: white;

            padding: 10px 16px;

            border-radius: 6px;

            text-decoration: none;

            font-size: 14px;

            white-space: nowrap;

        }


        .dashboard-btn:hover {

            background: #142850;

        }


        /* CARDS */

        .cards {

            display: grid;

            grid-template-columns: repeat(4, 1fr);

            gap: 18px;

            margin-bottom: 25px;

        }


        .card {

            background: white;

            padding: 22px;

            border-radius: 10px;

            box-shadow: 0 2px 8px rgba(0,0,0,.08);

        }


        .card h3 {

            margin: 0 0 10px;

            font-size: 15px;

            color: #666;

        }


        .card p {

            margin: 0;

            font-size: 25px;

            font-weight: bold;

        }


        /* MESSAGES */

        .message {

            background: #d4edda;

            color: #155724;

            padding: 12px;

            border-radius: 6px;

            margin-bottom: 18px;

        }


        .error {

            background: #f8d7da;

            color: #721c24;

            padding: 12px;

            border-radius: 6px;

            margin-bottom: 18px;

        }


        /* TABLE */

        .table-box {

            background: white;

            padding: 18px;

            border-radius: 10px;

            overflow-x: auto;

            box-shadow: 0 2px 8px rgba(0,0,0,.08);

        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 1100px;

        }


        th, td {

            padding: 13px 10px;

            border-bottom: 1px solid #e5e5e5;

            text-align: left;

            white-space: nowrap;

        }


        th {

            background: #f1f3f5;

        }


        /* STATUS */

        .status {

            padding: 5px 9px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;

        }


        .paid {

            background: #d4edda;

            color: #155724;

        }


        .pending {

            background: #fff3cd;

            color: #856404;

        }


        .failed,

        .cancelled {

            background: #f8d7da;

            color: #721c24;

        }


        .refunded {

            background: #d1ecf1;

            color: #0c5460;

        }


        /* FORM */

        select,

        button,

        .delete-btn {

            padding: 7px 9px;

            border: 1px solid #ccc;

            border-radius: 5px;

        }


        select {

            background: white;

            cursor: pointer;

        }


        button {

            background: #007bff;

            color: white;

            border: none;

            cursor: pointer;

        }


        button:hover {

            background: #0056b3;

        }


        /* DELETE BUTTON */

        .delete-btn {

            background: #dc3545;

            color: white;

            text-decoration: none;

            margin-left: 5px;

            border: none;

            cursor: pointer;

        }


        .delete-btn:hover {

            background: #b02a37;

        }


        /* RESPONSIVE */

        @media (max-width: 900px) {

            .cards {

                grid-template-columns: repeat(2, 1fr);

            }

        }


        @media (max-width: 500px) {

            .cards {

                grid-template-columns: 1fr;

            }


            .header-row {

                flex-direction: column;

                align-items: flex-start;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <!-- HEADER -->

    <div class="header-row">

        <h1>Payment Management</h1>


        <a href="dashboard.php"
           class="dashboard-btn">

            ← Back to Dashboard

        </a>

    </div>



    <!-- SUCCESS MESSAGE -->

    <?php if (isset($_GET['success'])): ?>

        <div class="message">

            <?php

            if ($_GET['success'] === 'updated') {

                echo "Payment status updated successfully.";

            }

            elseif ($_GET['success'] === 'deleted') {

                echo "Payment deleted successfully.";

            }

            ?>

        </div>

    <?php endif; ?>



    <!-- ERROR MESSAGE -->

    <?php if (isset($_GET['error'])): ?>

        <div class="error">

            Invalid payment status.

        </div>

    <?php endif; ?>



    <!-- STATISTICS CARDS -->

    <div class="cards">


        <div class="card">

            <h3>Total Payments</h3>

            <p>

                <?php echo $total_payments; ?>

            </p>

        </div>



        <div class="card">

            <h3>Total Paid Amount</h3>

            <p>

                ₹<?php echo number_format($paid_amount, 2); ?>

            </p>

        </div>



        <div class="card">

            <h3>Pending Amount</h3>

            <p>

                ₹<?php echo number_format($pending_amount, 2); ?>

            </p>

        </div>



        <div class="card">

            <h3>Failed Payments</h3>

            <p>

                <?php echo $failed_count; ?>

            </p>

        </div>


    </div>



    <!-- PAYMENT TABLE -->

    <div class="table-box">


        <table>


            <thead>

                <tr>

                    <th>Payment ID</th>

                    <th>Booking ID</th>

                    <th>Customer</th>

                    <th>Room</th>

                    <th>Amount</th>

                    <th>Method</th>

                    <th>Payment Date</th>

                    <th>Status</th>

                    <th>Manage</th>

                </tr>

            </thead>



            <tbody>


            <?php if ($payments && $payments->num_rows > 0): ?>


                <?php while ($payment = $payments->fetch_assoc()): ?>


                    <?php

                    $status =
                        $payment['payment_status'] ?? 'Pending';


                    $status_class =
                        strtolower($status);


                    $customer =
                        trim(
                            ($payment['first_name'] ?? '') .
                            ' ' .
                            ($payment['last_name'] ?? '')
                        );

                    ?>


                    <tr>


                        <!-- PAYMENT ID -->

                        <td>

                            <?php

                            echo (int) $payment['payment_id'];

                            ?>

                        </td>



                        <!-- BOOKING ID -->

                        <td>

                            <?php

                            echo (int) $payment['booking_id'];

                            ?>

                        </td>



                        <!-- CUSTOMER -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $customer ?: 'N/A'
                            );

                            ?>

                        </td>



                        <!-- ROOM -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $payment['room_number'] ?? 'N/A'
                            );

                            ?>

                        </td>



                        <!-- AMOUNT -->

                        <td>

                            ₹<?php

                            echo number_format(
                                (float) $payment['amount'],
                                2
                            );

                            ?>

                        </td>



                        <!-- METHOD -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $payment['payment_method'] ?? 'N/A'
                            );

                            ?>

                        </td>



                        <!-- PAYMENT DATE -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $payment['payment_date'] ?? 'N/A'
                            );

                            ?>

                        </td>



                        <!-- STATUS -->

                        <td>

                            <span class="status
                                <?php echo htmlspecialchars($status_class); ?>">

                                <?php

                                echo htmlspecialchars($status);

                                ?>

                            </span>

                        </td>



                        <!-- MANAGE -->

                        <td>


                            <!-- UPDATE FORM -->

                            <form method="POST"
                                  style="display:inline-block;">


                                <input
                                    type="hidden"
                                    name="payment_id"
                                    value="<?php

                                    echo (int) $payment['payment_id'];

                                    ?>"
                                >


                                <select
                                    name="payment_status"
                                    required
                                >


                                    <?php

                                    $options = [

                                        'Pending',

                                        'Paid',

                                        'Failed',

                                    ];

                                    ?>


                                    <?php foreach ($options as $option): ?>


                                        <option

                                            value="<?php

                                            echo $option;

                                            ?>"

                                            <?php

                                            echo $status === $option
                                                ? 'selected'
                                                : '';

                                            ?>

                                        >

                                            <?php

                                            echo $option;

                                            ?>

                                        </option>


                                    <?php endforeach; ?>


                                </select>



                                <button
                                    type="submit"
                                    name="update_status"
                                >

                                    Update

                                </button>


                            </form>



                            <!-- DELETE BUTTON -->

                            <a

                                class="delete-btn"

                                href="payments.php?delete=<?php

                                echo (int) $payment['payment_id'];

                                ?>"

                                onclick="return confirm(
                                    'Delete this payment?'
                                );"

                            >

                                Delete

                            </a>


                        </td>


                    </tr>


                <?php endwhile; ?>


            <?php else: ?>


                <tr>

                    <td colspan="9">

                        No payments found.

                    </td>

                </tr>


            <?php endif; ?>


            </tbody>


        </table>


    </div>


</div>


</body>

</html>