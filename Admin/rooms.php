<?php
session_start();
include("../Config/connection.php");

if (!isset($_SESSION['username'])) {
    header("Location: ../Customer/login.php");
    exit();
}

if ($_SESSION['role'] != "Admin") {
    header("Location: ../Customer/login.php");
    exit();
}

/* AUTO COMPLETE FINISHED BOOKINGS */

mysqli_query($conn, "
    UPDATE bookings
    SET booking_status='Completed'
    WHERE booking_status='Confirmed'
    AND check_out <= CURDATE()
");


/* ADD ROOM WITH PHOTO */

if (isset($_POST['add_room'])) {

    $room_number = mysqli_real_escape_string(
        $conn,
        $_POST['room_number']
    );

    $room_type = mysqli_real_escape_string(
        $conn,
        $_POST['room_type']
    );

    $price = (float) $_POST['price'];

    $capacity = (int) $_POST['capacity'];

    $status = "Available";

    $room_image = "";


    /* PHOTO UPLOAD */

    if (
        isset($_FILES['room_image']) &&
        $_FILES['room_image']['error'] != UPLOAD_ERR_NO_FILE
    ) {

        $file_name = $_FILES['room_image']['name'];
        $file_tmp = $_FILES['room_image']['tmp_name'];
        $file_size = $_FILES['room_image']['size'];

        $extension = strtolower(
            pathinfo($file_name, PATHINFO_EXTENSION)
        );

        $allowed_extensions = array(
            "jpg",
            "jpeg",
            "png",
            "webp"
        );


        if (!in_array($extension, $allowed_extensions)) {

            echo "<script>
                alert('Only JPG, JPEG, PNG and WEBP files are allowed');
            </script>";

        } elseif ($file_size > 5 * 1024 * 1024) {

            echo "<script>
                alert('Photo size must be less than 5MB');
            </script>";

        } else {

            $upload_folder = "../assets/images/rooms/";


            if (!is_dir($upload_folder)) {
                mkdir($upload_folder, 0777, true);
            }


            $new_file_name = "room_" .
                time() . "_" .
                rand(1000, 9999) .
                "." . $extension;


            $upload_path = $upload_folder . $new_file_name;


            if (move_uploaded_file($file_tmp, $upload_path)) {

                $room_image =
                    "assets/images/rooms/" . $new_file_name;

            }
        }
    }


    /* INSERT ROOM */

    $sql = "INSERT INTO rooms
    (room_number, room_type, price, capacity, status, room_image)
    VALUES
    (?, ?, ?, ?, ?, ?)";


    $stmt = mysqli_prepare($conn, $sql);


    mysqli_stmt_bind_param(
        $stmt,
        "ssdiss",
        $room_number,
        $room_type,
        $price,
        $capacity,
        $status,
        $room_image
    );


    if (mysqli_stmt_execute($stmt)) {

        echo "<script>
            alert('Room Added Successfully');
            window.location='rooms.php';
        </script>";

        exit();

    } else {

        echo "<script>
            alert('Error Adding Room');
        </script>";
    }
}


/* DELETE ROOM */

if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];


    $check = mysqli_query($conn, "
        SELECT booking_id
        FROM bookings
        WHERE room_id='$id'
        LIMIT 1
    ");


    if (mysqli_num_rows($check) > 0) {

        echo "<script>
            alert('This room cannot be deleted because it has booking history.');
            window.location='rooms.php';
        </script>";

        exit();
    }


    mysqli_query($conn, "
        DELETE FROM rooms
        WHERE room_id='$id'
    ");


    echo "<script>
        alert('Room Deleted');
        window.location='rooms.php';
    </script>";

    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Rooms</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">


    <style>

        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        .container {
            margin-top: 30px;
            margin-bottom: 40px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 18px rgba(0,0,0,.15);
            overflow: hidden;
        }

        .card-header {
            padding: 18px;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
        }

        .btn {
            border-radius: 8px;
        }

        .table {
            vertical-align: middle;
        }

        .room-photo {
            width: 100px;
            height: 75px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #ddd;
        }

        .no-photo {
            width: 100px;
            height: 75px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e9ecef;
            border-radius: 10px;
            color: #777;
            font-size: 12px;
            margin: auto;
        }

    </style>

</head>


<body>

<div class="container">


    <!-- PAGE HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold">

                <i class="fa fa-hotel text-primary"></i>

                Manage Rooms

            </h2>

            <p class="text-muted">
                Add and manage hotel rooms
            </p>

        </div>


        <a href="dashboard.php"
           class="btn btn-primary">

            <i class="fa fa-arrow-left"></i>
           Back To Dashboard

        </a>

    </div>



    <!-- ADD ROOM -->

    <div class="card mb-4">


        <div class="card-header bg-primary text-white">

            <h4 class="mb-0">

                <i class="fa fa-plus-circle"></i>

                Add New Room

            </h4>

        </div>


        <div class="card-body">


            <!-- IMPORTANT: enctype -->

            <form method="POST"
                  enctype="multipart/form-data">


                <div class="row g-3">


                    <!-- ROOM NUMBER -->

                    <div class="col-md-2">

                        <label class="form-label fw-bold">
                            Room No
                        </label>

                        <input
                            type="text"
                            name="room_number"
                            class="form-control"
                            placeholder="Room No"
                            required>

                    </div>



                    <!-- ROOM TYPE -->

                    <div class="col-md-2">

                        <label class="form-label fw-bold">
                            Room Type
                        </label>

                        <select
                            name="room_type"
                            class="form-select"
                            required>

                            <option value="Single">
                                Single
                            </option>

                            <option value="Double">
                                Double
                            </option>

                            <option value="Deluxe">
                                Deluxe
                            </option>

                            <option value="Suite">
                                Suite
                            </option>

                        </select>

                    </div>



                    <!-- PRICE -->

                    <div class="col-md-2">

                        <label class="form-label fw-bold">
                            Price
                        </label>

                        <input
                            type="number"
                            name="price"
                            class="form-control"
                            placeholder="Price"
                            min="0"
                            step="0.01"
                            required>

                    </div>



                    <!-- CAPACITY -->

                    <div class="col-md-2">

                        <label class="form-label fw-bold">
                            Capacity
                        </label>

                        <input
                            type="number"
                            name="capacity"
                            class="form-control"
                            placeholder="Capacity"
                            min="1"
                            required>

                    </div>



                    <!-- PHOTO -->

                    <div class="col-md-3">

                        <label class="form-label fw-bold">
                            Room Photo
                        </label>

                        <input
                            type="file"
                            name="room_image"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp">

                        <small class="text-muted">
                            JPG, PNG, WEBP | Max 5MB
                        </small>

                    </div>



                    <!-- ADD BUTTON -->

                    <div class="col-md-1 d-flex align-items-end">

                        <button
                            type="submit"
                            name="add_room"
                            class="btn btn-success w-100">

                            <i class="fa fa-plus"></i>

                        </button>

                    </div>


                </div>


            </form>

        </div>

    </div>



    <!-- ALL ROOMS -->

    <div class="card">


        <div class="card-header bg-dark text-white">

            <h4 class="mb-0">

                <i class="fa fa-bed"></i>

                All Rooms

            </h4>

        </div>


        <div class="card-body">


            <div class="table-responsive">


                <table
                    class="table table-bordered table-hover text-center">


                    <thead class="table-primary">

                        <tr>

                            <th>ID</th>

                            <th>Photo</th>

                            <th>Room No</th>

                            <th>Room Type</th>

                            <th>Price</th>

                            <th>Capacity</th>

                            <th>Status</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    $result = mysqli_query($conn, "
                        SELECT *
                        FROM rooms
                        ORDER BY room_id DESC
                    ");


                    if (mysqli_num_rows($result) > 0) {


                        while ($row = mysqli_fetch_assoc($result)) {


                            $room_id = $row['room_id'];

                            $today = date('Y-m-d');


                            $bookingCheck = mysqli_query($conn, "
                                SELECT booking_id
                                FROM bookings
                                WHERE room_id='$room_id'
                                AND booking_status='Confirmed'
                                AND check_in <= '$today'
                                AND check_out > '$today'
                                LIMIT 1
                            ");


                            if (mysqli_num_rows($bookingCheck) > 0) {

                                $roomStatus = "Booked";

                            } else {

                                $roomStatus = "Available";

                            }

                    ?>


                        <tr>


                            <!-- ID -->

                            <td>

                                <?php
                                echo $row['room_id'];
                                ?>

                            </td>



                            <!-- PHOTO -->

                            <td>


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

                                        No Photo

                                    </div>


                                <?php

                                }

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

                                <span class="badge bg-info text-dark">

                                    <?php
                                    echo htmlspecialchars(
                                        $row['room_type']
                                    );
                                    ?>

                                </span>

                            </td>



                            <!-- PRICE -->

                            <td>

                                ₹ <?php
                                echo number_format(
                                    $row['price'],
                                    2
                                );
                                ?>

                            </td>



                            <!-- CAPACITY -->

                            <td>

                                <i class="fa fa-users"></i>

                                <?php
                                echo $row['capacity'];
                                ?>

                            </td>



                            <!-- STATUS -->

                            <td>


                                <?php

                                if ($roomStatus == "Available") {

                                ?>

                                    <span class="badge bg-success">

                                        <i class="fa fa-check"></i>

                                        Available

                                    </span>


                                <?php

                                } else {

                                ?>

                                    <span class="badge bg-danger">

                                        <i class="fa fa-lock"></i>

                                        Booked

                                    </span>


                                <?php

                                }

                                ?>


                            </td>



                            <!-- ACTION -->

                            <td>


                                <a
                                    href="edit_room.php?id=<?php
                                    echo $row['room_id'];
                                    ?>"
                                    class="btn btn-warning btn-sm"
                                    title="Edit Room">

                                    <i class="fa fa-edit"></i>

                                </a>



                                <a
                                    href="rooms.php?delete=<?php
                                    echo $row['room_id'];
                                    ?>"
                                    class="btn btn-danger btn-sm"
                                    title="Delete Room"
                                    onclick="return confirm('Delete this room?')">

                                    <i class="fa fa-trash"></i>

                                </a>


                            </td>


                        </tr>


                    <?php

                        }

                    } else {

                    ?>


                        <tr>

                            <td colspan="8"
                                class="text-muted py-4">

                                <i class="fa fa-bed fa-2x"></i>

                                <br>

                                No Rooms Found

                            </td>

                        </tr>


                    <?php

                    }

                    ?>


                    </tbody>


                </table>


            </div>

        </div>

    </div>



    <br>

</div>


</body>

</html>