<?php
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

        header("Location: payments.php?success=updated");
        exit();
    }
}?>