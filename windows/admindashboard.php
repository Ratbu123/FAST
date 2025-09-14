<?php
session_start();
require_once __DIR__ . '/../config.php'; // Adjust path if needed

// Redirect if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}

// Fetch dashboard metrics
try {
    $totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalPWDs = $conn->query("SELECT COUNT(*) FROM users WHERE user_type='PWD'")->fetchColumn();
    $totalSeniors = $conn->query("SELECT COUNT(*) FROM users WHERE user_type='Senior'")->fetchColumn();
    $pending = $conn->query("SELECT COUNT(*) FROM users WHERE verified=0")->fetchColumn();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Fetch recent activity (latest 5)
try {
    $stmt = $conn->prepare("
        SELECT a.action, u.fullname AS user_name, a.created_at
        FROM activity_log a
        LEFT JOIN users u ON a.user_id = u.id
        ORDER BY a.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $activities = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - F.A.S.T</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-950 text-gray-100 font-sans min-h-screen">

<main class="max-w-7xl mx-auto px-6 py-12">

  <!-- Header -->
  <header class="mb-14 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
    <div class="flex items-center gap-3">
      <div class="w-14 h-14 rounded-2xl bg-yellow-400 flex items-center justify-center text-gray-900 text-2xl shadow-lg">
        <i class="fas fa-bolt"></i>
      </div>
      <div>
        <h1 class="text-4xl font-bold text-yellow-400">F.A.S.T Dashboard</h1>
        <p class="text-gray-400 text-sm">Facial Authentication & Signature Technology</p>
      </div>
    </div>
    <div>
      <span class="px-5 py-2 rounded-xl text-sm font-semibold bg-green-500/90 text-white shadow-lg flex items-center gap-2">
        <i class="fas fa-circle text-xs"></i> Active System
      </span>
    </div>
  </header>

  <!-- Metrics Grid -->
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">

    <!-- Total Users -->
    <div class="bg-gradient-to-br from-blue-600/80 to-blue-800/90 backdrop-blur-md rounded-2xl shadow-xl p-6 hover:-translate-y-1 hover:shadow-2xl transition-all duration-300">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-200">Total Users</h3>
        <i class="fas fa-users text-2xl text-blue-300"></i>
      </div>
      <div class="text-5xl font-extrabold text-white"><?= $totalUsers ?></div>
      <p class="text-sm text-gray-300 mt-2">All registered users</p>
    </div>

    <!-- Total PWDs -->
    <div class="bg-gradient-to-br from-indigo-600/80 to-indigo-800/90 backdrop-blur-md rounded-2xl shadow-xl p-6 hover:-translate-y-1 hover:shadow-2xl transition-all duration-300">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-200">Total PWDs</h3>
        <i class="fas fa-wheelchair text-2xl text-indigo-300"></i>
      </div>
      <div class="text-5xl font-extrabold text-white"><?= $totalPWDs ?></div>
      <p class="text-sm text-gray-300 mt-2">Registered PWDs</p>
    </div>

    <!-- Total Seniors -->
    <div class="bg-gradient-to-br from-green-600/80 to-green-800/90 backdrop-blur-md rounded-2xl shadow-xl p-6 hover:-translate-y-1 hover:shadow-2xl transition-all duration-300">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-200">Total Seniors</h3>
        <i class="fas fa-user-clock text-2xl text-green-300"></i>
      </div>
      <div class="text-5xl font-extrabold text-white"><?= $totalSeniors ?></div>
      <p class="text-sm text-gray-300 mt-2">Senior Citizens</p>
    </div>

    <!-- Pending -->
    <div class="bg-gradient-to-br from-red-600/80 to-red-800/90 backdrop-blur-md rounded-2xl shadow-xl p-6 hover:-translate-y-1 hover:shadow-2xl transition-all duration-300">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-200">Pending</h3>
        <i class="fas fa-clock text-2xl text-red-300"></i>
      </div>
      <div class="text-5xl font-extrabold text-white"><?= $pending ?></div>
      <p class="text-sm text-gray-300 mt-2">Awaiting verification</p>
    </div>

  </section>

  <!-- Recent Activity -->
  <section class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl shadow-xl">
    <div class="px-6 py-5 border-b border-white/10 flex justify-between items-center">
      <div>
        <h2 class="text-xl font-semibold text-yellow-400 flex items-center gap-2">
          <i class="fas fa-history"></i> Recent Activity
        </h2>
        <p class="text-gray-400 text-sm">Latest system logs</p>
      </div>
      <a href="activity-log.php" class="text-sm text-yellow-300 hover:underline">View All</a>
    </div>

    <ul class="divide-y divide-white/10 text-gray-200">
      <?php if (!empty($activities)): ?>
        <?php foreach ($activities as $act): ?>
          <li class="px-6 py-4 flex justify-between items-center hover:bg-white/5 transition">
            <span class="flex items-center gap-2">
              <i class="fas fa-user text-yellow-400"></i>
              <?= htmlspecialchars($act['user_name'] ?? 'System') ?>: <?= htmlspecialchars($act['action']) ?>
            </span>
            <span class="text-sm text-gray-400"><?= date('M d, Y · h:i A', strtotime($act['created_at'])) ?></span>
          </li>
        <?php endforeach; ?>
      <?php else: ?>
        <li class="px-6 py-4 text-gray-400 text-center">No recent activity found.</li>
      <?php endif; ?>
    </ul>
  </section>

</main>

</body>
</html>
