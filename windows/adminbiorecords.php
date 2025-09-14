<?php
session_start();
require_once __DIR__ . '/../config.php'; // adjust path to your config

// Redirect if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}

// Fetch biometric records with user info
$stmt = $conn->prepare("
    SELECT b.*, u.fullname, u.user_type, u.created_at as user_created
    FROM biometric_logs b
    LEFT JOIN users u ON b.user_id = u.id
    ORDER BY b.verification_time DESC
");
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Biometric Records - F.A.S.T</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-950 text-gray-100 font-sans min-h-screen">

<main class="max-w-7xl mx-auto px-6 py-12">
  
  <!-- Header -->
  <header class="mb-14 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
    <div class="flex items-center gap-3">
      <div class="w-14 h-14 rounded-2xl bg-yellow-400 flex items-center justify-center text-gray-900 text-2xl shadow-lg">
        <i class="fas fa-fingerprint"></i>
      </div>
      <div>
        <h1 class="text-4xl font-bold text-yellow-400">Biometric Records</h1>
        <p class="text-gray-400 text-sm">Monitor biometric activity and verification logs</p>
      </div>
    </div>
    <div>
      <span class="px-5 py-2 rounded-xl text-sm font-semibold bg-blue-500/90 text-white shadow-lg flex items-center gap-2">
        <i class="fas fa-database text-xs"></i> Records Active
      </span>
    </div>
  </header>

  <!-- Search -->
  <section class="mb-12 flex flex-col sm:flex-row gap-4 items-center justify-between">
    <div class="relative flex-1 w-full">
      <input type="text" placeholder="Search user or ID..." 
        class="w-full pl-10 pr-4 py-3 rounded-xl bg-white/5 border border-white/10 text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400">
      <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
    </div>
    <button class="px-5 py-3 rounded-xl bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-semibold shadow-lg transition">
      <i class="fas fa-file-export"></i> Export
    </button>
  </section>

  <!-- Biometric Table -->
  <section class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl shadow-xl overflow-hidden">
    <div class="px-6 py-5 border-b border-white/10 flex justify-between items-center">
      <div>
        <h2 class="text-xl font-semibold text-yellow-400 flex items-center gap-2">
          <i class="fas fa-list"></i> Records List
        </h2>
        <p class="text-gray-400 text-sm">Biometric verifications history</p>
      </div>
      <span class="text-sm text-gray-400">
        Last updated: <?= date('M d, Y') ?>
      </span>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-800/70 text-gray-300 uppercase text-xs tracking-wide">
          <tr>
            <th class="px-6 py-3 text-left">User</th>
            <th class="px-6 py-3 text-left">Type</th>
            <th class="px-6 py-3 text-left">Registration</th>
            <th class="px-6 py-3 text-left">Last Verification</th>
            <th class="px-6 py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/10 text-gray-200">
          <?php if ($records): ?>
            <?php foreach ($records as $r): ?>
              <tr class="hover:bg-white/5 transition">
                <td class="px-6 py-4 font-medium"><?= htmlspecialchars($r['fullname'] ?? 'N/A') ?></td>
                <td class="px-6 py-4">
                  <span class="px-3 py-1 rounded-full text-xs font-medium <?= $r['user_type']=='PWD'?'bg-blue-600/40 text-blue-200':'bg-green-600/40 text-green-200' ?>">
                    <?= htmlspecialchars($r['user_type'] ?? 'Other') ?>
                  </span>
                </td>
                <td class="px-6 py-4"><?= date('M d, Y', strtotime($r['user_created'] ?? '')) ?></td>
                <td class="px-6 py-4"><?= date('M d, Y · h:i A', strtotime($r['verification_time'])) ?></td>
                <td class="px-6 py-4 flex gap-2 justify-center">
                  <a href="viewbiorecord.php?id=<?= $r['id'] ?>" class="px-3 py-1.5 rounded-lg bg-indigo-500 hover:bg-indigo-600 text-white text-xs flex items-center gap-1 transition">
                    <i class="fas fa-eye"></i> View
                  </a>
                  <a href="deletebiorecord.php?id=<?= $r['id'] ?>" class="px-3 py-1.5 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs flex items-center gap-1 transition">
                    <i class="fas fa-trash"></i> Delete
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center py-6 text-gray-400">
                <i class="fas fa-info-circle mr-2"></i> No biometric records found
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>
</body>
</html>
