<?php
session_start();
include("../Config/connection.php");

/* Login Check */
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$success = "";
$error = "";

/* Submit Feedback */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
    $message = trim($_POST['message'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $error = "Please select a rating between 1 and 5.";
    } elseif (empty($message)) {
        $error = "Please enter your feedback message.";
    } else {

        $query = mysqli_prepare(
            $conn,
            "INSERT INTO feedback (username, rating, message)
             VALUES (?, ?, ?)"
        );

        if ($query) {
            mysqli_stmt_bind_param(
                $query,
                "sis",
                $username,
                $rating,
                $message
            );

            if (mysqli_stmt_execute($query)) {
                $success = "Thank you! Your feedback has been submitted successfully.";
            } else {
                $error = "Something went wrong. Please try again.";
            }

            mysqli_stmt_close($query);
        } else {
            $error = "Database query error.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Feedback | Hotel Hub</title>

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
            min-height: 100vh;
            background: #f4f7fc;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 25px;
        }

        .feedback-container {
            width: 100%;
            max-width: 650px;
        }

        .top-bar {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 18px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #0d6efd;
            color: white;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
        }

        .back-btn:hover {
            background: #084298;
            transform: translateY(-2px);
        }

        .feedback-card {
            background: white;
            padding: 35px;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .feedback-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .feedback-header i {
            font-size: 48px;
            color: #0d6efd;
            margin-bottom: 12px;
        }

        .feedback-header h1 {
            color: #17324d;
            margin-bottom: 8px;
        }

        .feedback-header p {
            color: #777;
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .username-box {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f1f3f5;
            color: #555;
        }

        .rating-box {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 8px;
        }

        .rating-box input {
            display: none;
        }

        .rating-box label {
            font-size: 35px;
            color: #cbd1d8;
            cursor: pointer;
            transition: 0.2s;
        }

        .rating-box label:hover,
        .rating-box label:hover ~ label,
        .rating-box input:checked ~ label {
            color: #ffc107;
        }

        textarea {
            width: 100%;
            min-height: 140px;
            resize: vertical;
            padding: 13px;
            border: 1px solid #ddd;
            border-radius: 8px;
            outline: none;
            font-size: 15px;
        }

        textarea:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.12);
        }

        .submit-btn {
            width: 100%;
            border: none;
            background: #198754;
            color: white;
            padding: 13px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .submit-btn:hover {
            background: #146c43;
            transform: translateY(-2px);
        }

        .message {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .success {
            background: #d1e7dd;
            color: #0f5132;
        }

        .error {
            background: #f8d7da;
            color: #842029;
        }

        @media (max-width: 600px) {
            body {
                padding: 15px;
            }

            .feedback-card {
                padding: 25px 20px;
            }

            .feedback-header h1 {
                font-size: 27px;
            }
        }
    </style>
</head>

<body>

<div class="feedback-container">

    <div class="top-bar">
        <a href="dashboard.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Dashboard
        </a>
    </div>

    <div class="feedback-card">

        <div class="feedback-header">
            <i class="fa-solid fa-comment-dots"></i>
            <h1>Give Your Feedback</h1>
            <p>We value your experience at Hotel Hub.</p>
        </div>

        <?php if (!empty($success)) { ?>
            <div class="message success">
                <i class="fa-solid fa-circle-check"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="message error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php } ?>

        <form method="POST" action="">

            <div class="form-group">
                <label>Username</label>

                <div class="username-box">
                    <i class="fa-solid fa-user"></i>
                    <?php echo htmlspecialchars($username); ?>
                </div>
            </div>

            <div class="form-group">
                <label>Rating</label>

                <div class="rating-box">
                    <input type="radio" name="rating" id="star5" value="5">
                    <label for="star5" title="5 Stars">
                        <i class="fa-solid fa-star"></i>
                    </label>

                    <input type="radio" name="rating" id="star4" value="4">
                    <label for="star4" title="4 Stars">
                        <i class="fa-solid fa-star"></i>
                    </label>

                    <input type="radio" name="rating" id="star3" value="3">
                    <label for="star3" title="3 Stars">
                        <i class="fa-solid fa-star"></i>
                    </label>

                    <input type="radio" name="rating" id="star2" value="2">
                    <label for="star2" title="2 Stars">
                        <i class="fa-solid fa-star"></i>
                    </label>

                    <input type="radio" name="rating" id="star1" value="1">
                    <label for="star1" title="1 Star">
                        <i class="fa-solid fa-star"></i>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="message">Your Feedback</label>

                <textarea
                    name="message"
                    id="message"
                    placeholder="Write your feedback here..."
                    required
                ></textarea>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fa-solid fa-paper-plane"></i>
                Submit Feedback
            </button>

        </form>

    </div>

</div>

</body>
</html>
