<?php
session_start();
include("../Config/connection.php");

if (!isset($_SESSION['username'])) {
    header("Location: ../Customer/login.php");
    exit();
}

if ($_SESSION['role'] != "Admin") {
    header("Location: dashboard.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: rooms.php");
    exit();
}

$id = (int) $_GET['id'];

/* Fetch Room */
$stmt = mysqli_prepare($conn, "SELECT * FROM rooms WHERE room_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    echo "<script>alert('Room Not Found'); window.location='rooms.php';</script>";
    exit();
}

/* Update Room */
if (isset($_POST['update'])) {

    $room_number = trim($_POST['room_number']);
    $room_type = $_POST['room_type'];
    $price = (float) $_POST['price'];
    $capacity = (int) $_POST['capacity'];
    $status = $_POST['status'];

    /* Keep Old Image */
    $room_image = $row['room_image'];

    /* Photo Upload */
    if (isset($_FILES['room_image']) &&
        $_FILES['room_image']['error'] != UPLOAD_ERR_NO_FILE) {

        if ($_FILES['room_image']['error'] != UPLOAD_ERR_OK) {

            echo "<script>alert('Photo Upload Failed');</script>";

        } else {

            $file_name = $_FILES['room_image']['name'];
            $file_tmp = $_FILES['room_image']['tmp_name'];
            $file_size = $_FILES['room_image']['size'];

            $allowed_extensions = array(
                "jpg",
                "jpeg",
                "png",
                "webp"
            );

            $file_extension = strtolower(
                pathinfo($file_name, PATHINFO_EXTENSION)
            );

            /* Check Extension */
            if (!in_array($file_extension, $allowed_extensions)) {

                echo "<script>
                    alert('Only JPG, JPEG, PNG and WEBP files are allowed');
                </script>";

            }

            /* Check File Size */
            elseif ($file_size > 5 * 1024 * 1024) {

                echo "<script>
                    alert('Photo size must be less than 5MB');
                </script>";

            } else {

                $upload_directory = "../assets/images/rooms/";

                /* Create Folder If Not Exists */
                if (!is_dir($upload_directory)) {
                    mkdir($upload_directory, 0777, true);
                }

                $new_file_name = "room_" . $id . "_" .
                    time() . "." . $file_extension;

                $upload_path = $upload_directory . $new_file_name;

                /* Move Uploaded File */
                if (move_uploaded_file($file_tmp, $upload_path)) {

                    /*
                    Store path relative to project root
                    */
                    $room_image =
                        "assets/images/rooms/" . $new_file_name;

                } else {

                    echo "<script>
                        alert('Unable to save photo');
                    </script>";
                }
            }
        }
    }

    /* Update Query */
    $update_stmt = mysqli_prepare(
        $conn,
        "UPDATE rooms SET
        room_number = ?,
        room_type = ?,
        price = ?,
        capacity = ?,
        status = ?,
        room_image = ?
        WHERE room_id = ?"
    );

    mysqli_stmt_bind_param(
        $update_stmt,
        "ssdissi",
        $room_number,
        $room_type,
        $price,
        $capacity,
        $status,
        $room_image,
        $id
    );

    if (mysqli_stmt_execute($update_stmt)) {

        echo "<script>
            alert('Room Updated Successfully');
            window.location='rooms.php';
        </script>";

        exit();

    } else {

        echo "<script>
            alert('Room Update Failed');
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Edit Room</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>

<body style="background:#f4f6f9;">

<div class="container mt-5 mb-5">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">

            <h3 class="mb-0">Edit Room</h3>

        </div>

        <div class="card-body">

            <form method="post"
                  enctype="multipart/form-data">

                <!-- Room Number -->

                <div class="mb-3">

                    <label class="form-label">
                        Room Number
                    </label>

                    <input
                        type="text"
                        name="room_number"
                        class="form-control"
                        value="<?php
                        echo htmlspecialchars($row['room_number']);
                        ?>"
                        required>

                </div>


                <!-- Room Type -->

                <div class="mb-3">

                    <label class="form-label">
                        Room Type
                    </label>

                    <select
                        name="room_type"
                        class="form-control"
                        required>

                        <option value="Single"
                            <?php
                            if ($row['room_type'] == "Single")
                                echo "selected";
                            ?>>
                            Single
                        </option>

                        <option value="Double"
                            <?php
                            if ($row['room_type'] == "Double")
                                echo "selected";
                            ?>>
                            Double
                        </option>

                        <option value="Deluxe"
                            <?php
                            if ($row['room_type'] == "Deluxe")
                                echo "selected";
                            ?>>
                            Deluxe
                        </option>

                        <option value="Suite"
                            <?php
                            if ($row['room_type'] == "Suite")
                                echo "selected";
                            ?>>
                            Suite
                        </option>

                    </select>

                </div>


                <!-- Price -->

                <div class="mb-3">

                    <label class="form-label">
                        Price
                    </label>

                    <input
                        type="number"
                        name="price"
                        class="form-control"
                        min="0"
                        step="0.01"
                        value="<?php
                        echo htmlspecialchars($row['price']);
                        ?>"
                        required>

                </div>


                <!-- Capacity -->

                <div class="mb-3">

                    <label class="form-label">
                        Capacity
                    </label>

                    <input
                        type="number"
                        name="capacity"
                        class="form-control"
                        min="1"
                        value="<?php
                        echo htmlspecialchars($row['capacity']);
                        ?>"
                        required>

                </div>


                <!-- Status -->

                <div class="mb-3">

                    <label class="form-label">
                        Status
                    </label>

                    <select
                        name="status"
                        class="form-control"
                        required>

                        <option value="Available"
                            <?php
                            if ($row['status'] == "Available")
                                echo "selected";
                            ?>>
                            Available
                        </option>

                        <option value="Booked"
                            <?php
                            if ($row['status'] == "Booked")
                                echo "selected";
                            ?>>
                            Booked
                        </option>

                    </select>

                </div>


                <!-- Room Photo -->

                <div class="mb-3">

                    <label class="form-label">
                        Room Photo
                    </label>

                    <?php
                    if (!empty($row['room_image'])) {
                    ?>

                        <div class="mb-3">

                            <p class="mb-2">
                                Current Photo:
                            </p>

                            <img
                                src="../<?php
                                echo htmlspecialchars(
                                    $row['room_image']
                                );
                                ?>"
                                alt="Room Photo"
                                width="200"
                                height="130"
                                style="
                                object-fit:cover;
                                border-radius:8px;
                                border:1px solid #ddd;
                                ">

                        </div>

                    <?php
                    } else {
                    ?>

                        <p class="text-muted">
                            No Photo Uploaded
                        </p>

                    <?php
                    }
                    ?>


                    <input
                        type="file"
                        name="room_image"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp">

                    <small class="text-muted">

                        Allowed: JPG, JPEG, PNG, WEBP.
                        Maximum size: 5MB.
                        Leave empty to keep old photo.

                    </small>

                </div>


                <!-- Buttons -->

                <button
                    type="submit"
                    name="update"
                    class="btn btn-success">

                    Update Room

                </button>


                <a
                    href="rooms.php"
                    class="btn btn-secondary">

                    Cancel

                </a>

            </form>

        </div>

    </div>

</div>

</body>

</html>