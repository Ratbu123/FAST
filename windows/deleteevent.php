<?php
require_once __DIR__ . '/../config.php';
if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM agma_events WHERE id=?");
    if($stmt->execute([$id])){
        session_start();
        $_SESSION['success'] = "Event deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete event.";
    }
}
header("Location: ../dashboard.php"); // redirect back to dashboard
exit;
?>
