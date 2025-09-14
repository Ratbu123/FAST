<?php
session_start();
require_once __DIR__ . '/../config.php'; // Adjust path if needed

// Redirect if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}

// Fetch all documents with user info
$stmt = $conn->prepare("
    SELECT d.*, u.fullname, u.user_type, u.email, u.contact, u.created_at as user_created
    FROM user_documents d
    LEFT JOIN users u ON d.user_id = u.id
    ORDER BY d.uploaded_at DESC
");
$stmt->execute();
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Metrics
$totalDocs = count($documents);
$verifiedDocs = $conn->query("SELECT COUNT(*) FROM user_documents WHERE verified=1")->fetchColumn();
$pendingDocs = $totalDocs - $verifiedDocs;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Documents - F.A.S.T</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-950 text-gray-100 font-sans min-h-screen">

<main class="max-w-7xl mx-auto px-6 py-12">
  
  <!-- Header -->
  <header class="mb-14 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
    <div class="flex items-center gap-3">
      <div class="w-14 h-14 rounded-2xl bg-yellow-400 flex items-center justify-center text-gray-900 text-2xl shadow-lg">
        <i class="fas fa-file-signature"></i>
      </div>
      <div>
        <h1 class="text-4xl font-bold text-yellow-400">User Documents</h1>
        <p class="text-gray-400 text-sm">Manage and verify registered users’ documents</p>
      </div>
    </div>
    <div>
      <span class="px-5 py-2 rounded-xl text-sm font-semibold bg-green-500/90 text-white shadow-lg flex items-center gap-2">
        <i class="fas fa-check-circle text-xs"></i> Verified System
      </span>
    </div>
  </header>

  <!-- Search & Filter -->
  <section class="mb-12 flex flex-col sm:flex-row gap-4 items-center justify-between">
    <div class="relative flex-1 w-full">
      <input type="text" placeholder="Search users..." 
        class="w-full pl-10 pr-4 py-3 rounded-xl bg-white/5 border border-white/10 text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400">
      <i class="fas fa-search absolute left-3 top-3.5 text-gray-400"></i>
    </div>
    <select class="bg-white/5 border border-white/10 text-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-yellow-400">
      <option>All Types</option>
      <option>PWD</option>
      <option>Senior</option>
    </select>
  </section>

  <!-- User Documents Table -->
  <section class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl shadow-xl overflow-hidden">
    <div class="px-6 py-5 border-b border-white/10 flex justify-between items-center">
      <div>
        <h2 class="text-xl font-semibold text-yellow-400 flex items-center gap-2">
          <i class="fas fa-folder-open"></i> Registered Documents
        </h2>
        <p class="text-gray-400 text-sm">All submitted user files</p>
      </div>
      <a href="export_documents.php" class="text-sm text-yellow-300 hover:underline">Export CSV</a>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-800/70 text-gray-300 uppercase text-xs tracking-wide">
          <tr>
            <th class="px-6 py-3 text-left">Doc ID</th>
            <th class="px-6 py-3 text-left">User Name</th>
            <th class="px-6 py-3 text-left">Type</th>
            <th class="px-6 py-3 text-left">Email</th>
            <th class="px-6 py-3 text-left">Registration</th>
            <th class="px-6 py-3 text-left">Verification</th>
            <th class="px-6 py-3 text-center">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/10 text-gray-200">
          <?php if ($documents): ?>
            <?php foreach ($documents as $doc): ?>
              <tr class="hover:bg-white/5 transition">
                <td class="px-6 py-4"><?= htmlspecialchars($doc['id']) ?></td>
                <td class="px-6 py-4 font-medium"><?= htmlspecialchars($doc['fullname'] ?? 'N/A') ?></td>
                <td class="px-6 py-4">
                  <span class="px-3 py-1 rounded-full text-xs font-medium <?= $doc['user_type']=='PWD'?'bg-blue-600/40 text-blue-200':'bg-green-600/40 text-green-200' ?>">
                    <?= htmlspecialchars($doc['user_type'] ?? 'Other') ?>
                  </span>
                </td>
                <td class="px-6 py-4"><?= htmlspecialchars($doc['email'] ?? '-') ?></td>
                <td class="px-6 py-4"><?= date('M d, Y', strtotime($doc['user_created'] ?? $doc['uploaded_at'])) ?></td>
                <td class="px-6 py-4">
                  <?php if ($doc['verified']): ?>
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-600/40 text-green-200">Verified</span>
                  <?php else: ?>
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-600/40 text-red-200">Pending</span>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4 flex gap-2 justify-center">
                  <a href="viewdocument.php?id=<?= $doc['id'] ?>" class="px-3 py-1.5 rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-xs flex items-center gap-1 transition">
                    <i class="fas fa-eye"></i> View
                  </a>
                  <a href="verifiedocument.php?id=<?= $doc['id'] ?>" class="px-3 py-1.5 rounded-lg bg-green-500 hover:bg-green-600 text-white text-xs flex items-center gap-1 transition">
                    <i class="fas fa-check"></i> Verify
                  </a>
                  <a href="deletedocument.php?id=<?= $doc['id'] ?>" class="px-3 py-1.5 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs flex items-center gap-1 transition">
                    <i class="fas fa-trash"></i> Delete
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center py-6 text-gray-400">No documents found</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

</main>
</body>
</html>
