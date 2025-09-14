<?php
session_start();
require_once __DIR__ . '/../config.php'; // adjust path

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $member_since = $_POST['member_since'];
    $status = $_POST['status'];
    $signed_date = $_POST['signed_date'];

    if ($name && $member_since && $status && $signed_date) {
        try {
            $stmt = $conn->prepare("INSERT INTO agma_events (name, member_since, status, signed_date) VALUES (:name, :member_since, :status, :signed_date)");
            $stmt->execute([
                ':name' => $name,
                ':member_since' => $member_since . '-01',
                ':status' => $status,
                ':signed_date' => $signed_date
            ]);

            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => "Event '$name' added successfully!"
            ];

        } catch (PDOException $e) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => "Error adding event: " . $e->getMessage()
            ];
        }
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => "Please fill in all fields."
        ];
    }

    // Always redirect back to the same page
    header("Location: ../mainadmin.php?page=./windows/adminevents.php");
    exit;
} else {
    header("Location: ../mainadmin.php?page=./windows/adminevents.php");
    exit;
}
