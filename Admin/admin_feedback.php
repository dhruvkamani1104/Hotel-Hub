<?php
session_start();
include("../Config/connection.php");

/* Admin Login Check */
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== "Admin") {
    header("Location: login.php");
    exit();
}

/* Delete Feedback */
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $feedback_id = (int) $_GET['delete'];

    $deleteQuery = mysqli_prepare(
        $conn,
        "DELETE FROM feedback WHERE feedback_id = ?"
    );

    if ($deleteQuery) {
        mysqli_stmt_bind_param($deleteQuery, "i", $feedback_id);
        mysqli_stmt_execute($deleteQuery);
        mysqli_stmt_close($deleteQuery);
    }

    header("Location: admin_feedback.php?deleted=1");
    exit();
}

/* Feedback Statistics */
$totalQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM feedback");
$totalFeedback = (int) mysqli_fetch_assoc($totalQuery)['total'];

$ratingQuery = mysqli_query(
    $conn,
    "SELECT AVG(rating) AS average_rating FROM feedback"
);
$averageRatingData = mysqli_fetch_assoc($ratingQuery);
$averageRating = $averageRatingData['average_rating']
    ? number_format((float) $averageRatingData['average_rating'], 1)
    : "0.0";

/* Get Feedback */
$feedbackQuery = mysqli_query(
    $conn,
    "SELECT feedback_id, username, rating, message, feedback_date
     FROM feedback
     ORDER BY feedback_id DESC"
);

if (!$feedbackQuery) {
    die("Database Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Feedback Management | Admin</title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    >

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", sans-serif;
        }

        body {
            background: #f4f7fc;
            color: #333;
        }

        .container {
            width: 95%;
            max-width: 1400px;
            margin: 25px auto;
        }

        .top-bar {
            background: white;
            padding: 18px 25px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .08);
        }

        .top-bar h1 {
            color: #17324d;
            font-size: 25px;
        }

        .top-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            padding: 10px 15px;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }

        .dashboard-btn {
            background: #0d6efd;
        }

        .logout-btn {
            background: #dc3545;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 5px 18px rgba(0, 0, 0, .09);
        }

        .stat-card i {
            color: #0d6efd;
            font-size: 32px;
            margin-bottom: 12px;
        }

        .stat-card h2 {
            font-size: 30px;
            color: #17324d;
        }

        .stat-card p {
            color: #777;
            margin-top: 5px;
        }

        .table-card {
            background: white;
            margin-top: 25px;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 5px 18px rgba(0, 0, 0, .09);
            overflow-x: auto;
        }

        .table-card h2 {
            margin-bottom: 20px;
            color: #17324d;
        }

        table {
            width: 100%;
            min-width: 850px;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 14px 12px;
            border-bottom: 1px solid #e9ecef;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background: #0d6efd;
            color: white;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .stars {
            color: #ffc107;
            white-space: nowrap;
        }

        .message {
            max-width: 400px;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .delete-btn {
            background: #dc3545;
            padding: 8px 12px;
        }

        .alert {
            margin-top: 20px;
            padding: 13px 16px;
            border-radius: 8px;
            background: #d1e7dd;
            color: #0f5132;
            font-weight: 600;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #777;
        }

        @media (max-width: 650px) {
            .top-bar {
                flex-direction: column;
                align-items: flex-start;
            }

            .top-bar h1 {
                font-size: 21px;
            }

            .container {
                width: 92%;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="top-bar">
        <h1>
            <i class="fa-solid fa-comments"></i>
            Feedback Management
        </h1>

        <div class="top-actions">
            <a href="dashboard.php" class="btn dashboard-btn">
                <i class="fa-solid fa-arrow-left"></i>
                Back to Dashboard
            </a>

            <a href="../logout.php" class="btn logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </a>
        </div>
    </div>

    <?php if (isset($_GET['deleted'])) { ?>
        <div class="alert">
            <i class="fa-solid fa-circle-check"></i>
            Feedback deleted successfully.
        </div>
    <?php } ?>

    <div class="stats">

        <div class="stat-card">
            <i class="fa-solid fa-comments"></i>
            <h2><?php echo $totalFeedback; ?></h2>
            <p>Total Feedback</p>
        </div>

        <div class="stat-card">
            <i class="fa-solid fa-star"></i>
            <h2><?php echo $averageRating; ?>/5</h2>
            <p>Average Rating</p>
        </div>

    </div>

    <div class="table-card">

        <h2>
            <i class="fa-solid fa-table"></i>
            Customer Feedback List
        </h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Rating</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
            <?php if (mysqli_num_rows($feedbackQuery) > 0) { ?>

                <?php while ($row = mysqli_fetch_assoc($feedbackQuery)) { ?>

                    <tr>
                        <td>
                            <?php echo (int) $row['feedback_id']; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row['username']); ?>
                        </td>

                        <td>
                            <span class="stars">
                                <?php
                                $rating = (int) $row['rating'];

                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $rating
                                        ? '<i class="fa-solid fa-star"></i>'
                                        : '<i class="fa-regular fa-star"></i>';
                                }
                                ?>
                            </span>
                            <br>
                            <?php echo $rating; ?>/5
                        </td>

                        <td class="message">
                            <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                date("d-m-Y h:i A", strtotime($row['feedback_date']))
                            );
                            ?>
                        </td>

                        <td>
                            <a
                                href="admin_feedback.php?delete=<?php echo (int) $row['feedback_id']; ?>"
                                class="btn delete-btn"
                                onclick="return confirm('Are you sure you want to delete this feedback?');"
                            >
                                <i class="fa-solid fa-trash"></i>
                                Delete
                            </a>
                        </td>
                    </tr>

                <?php } ?>

            <?php } else { ?>

                <tr>
                    <td colspan="6" class="empty">
                        No feedback available.
                    </td>
                </tr>

            <?php } ?>
            </tbody>
        </table>

    </div>

</div>

</body>
</html>
