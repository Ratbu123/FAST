<?php
session_start();
require_once "db_connection.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch admin info
$stmt = $conn->prepare("SELECT fullname, email FROM users WHERE id = ? AND role = 'Admin'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($adminName, $adminEmail);
$stmt->fetch();
$stmt->close();

// Default location
$location = "BATELEC II Lipa";

// Fetch latest 10 notifications
$notifications = [];
$stmt = $conn->prepare("SELECT message, read_status, created_at 
                        FROM notifications 
                        WHERE user_id = ? 
                        ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>F.A.S.T Admin Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Montserrat', sans-serif; }
  ::-webkit-scrollbar { width: 6px; }
  ::-webkit-scrollbar-thumb { background: #facc15; border-radius: 6px; }
</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-900 via-blue-900 to-black text-gray-100 flex">

<!-- Sidebar -->
<aside class="w-64 bg-white/10 backdrop-blur-md border-r border-white/20 shadow-xl flex flex-col">
  <div class="flex items-center gap-3 p-6 border-b border-white/20">
    <img src="images/logo.png" class="w-12 h-12 rounded-full border-2 border-yellow-400 shadow-md" alt="F.A.S.T Logo">
    <div>
      <h1 class="text-lg font-bold text-yellow-400">F.A.S.T</h1>
      <p class="text-xs text-gray-300">Admin Dashboard</p>
    </div>
  </div>

  <!-- Sidebar Navigation -->
  <nav class="flex-1 p-4 space-y-2">
    <?php
    $navItems = [
      "Dashboard" => "windows/Dashboard.php",
      "User Management" => "windows/UserManagement.php",
      "Documents" => "windows/Documents.php",
      "Biometric Data" => "windows/BioRecords.php",
      "Events" => "windows/Events.php"
    ];
    foreach ($navItems as $name => $page): ?>
      <div class="nav-item flex items-center gap-3 px-3 py-2 rounded-lg cursor-pointer hover:bg-yellow-400/20 hover:text-yellow-400 transition"
           data-page="<?= $page ?>">
        <i class="fas fa-angle-right"></i>
        <span><?= $name ?></span>
      </div>
    <?php endforeach; ?>
  </nav>

  <!-- Logout -->
  <form action="logout.php" method="POST" class="p-4 border-t border-white/20">
    <button type="submit" class="w-full flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg shadow transition">
      <i class="fas fa-sign-out-alt"></i> Log out
    </button>
  </form>
</aside>

<!-- Main -->
<div class="flex-1 flex flex-col">
  <!-- Header -->
  <header class="bg-white/10 backdrop-blur-md border-b border-white/20 shadow-lg">
    <div class="flex items-center justify-between px-6 py-4">
      <div>
        <h1 class="text-xl font-bold">Welcome, <?= htmlspecialchars($adminName) ?></h1>
        <p class="text-sm text-gray-300"><?= htmlspecialchars($location) ?> | <?= htmlspecialchars($adminEmail) ?></p>
      </div>

      <!-- Notifications -->
      <div class="relative" x-data="{ open: false }">
        <button @click="open = !open" class="relative text-xl hover:text-yellow-400 transition">
          <i class="fas fa-bell"></i>
          <?php if (count($notifications) > 0): ?>
            <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-ping"></span>
          <?php endif; ?>
        </button>
        <div x-show="open" @click.away="open = false" 
             x-transition class="absolute right-0 mt-3 w-80 bg-white/10 backdrop-blur-lg rounded-lg shadow-lg border border-white/20 z-50">
          <h4 class="px-4 py-2 font-semibold border-b border-white/20">Notifications</h4>
          <ul class="max-h-64 overflow-y-auto text-sm divide-y divide-white/10">
            <?php if (!empty($notifications)): ?>
              <?php foreach ($notifications as $note):
                $readClass = $note['read_status'] ? 'text-gray-400' : 'text-yellow-300 font-semibold'; ?>
                <li class="px-4 py-2 <?= $readClass ?>">
                  <?= htmlspecialchars($note['message']) ?>
                  <span class="block text-xs text-gray-400"><?= date('M d, Y H:i', strtotime($note['created_at'])) ?></span>
                </li>
              <?php endforeach; ?>
            <?php else: ?>
              <li class="px-4 py-2 text-gray-300">No new notifications</li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main id="main-content" class="flex-1 p-6 overflow-y-auto">
    <div class="bg-gradient-to-r from-yellow-500/20 to-yellow-400/10 border border-yellow-400/30 rounded-xl p-8 shadow-lg text-center text-gray-300 mb-8 animate-fadeIn">
      <h2 class="text-2xl font-semibold mb-3">F.A.S.T Admin Control Panel</h2>
      <p class="text-sm">Manage users, documents, biometric data, and events from the sidebar menu.</p>
    </div>

    <!-- Image Carousel -->
    <div x-data="{ current: 0, images: [
                  'images/success1.jpg',
                  'images/success2.jpg',
                  'images/success3.jpg'] }"
         x-init="setInterval(() => { current = (current + 1) % images.length }, 4000)"
         class="relative w-full max-w-5xl mx-auto rounded-xl overflow-hidden shadow-2xl h-80">

      <template x-for="(img, index) in images" :key="index">
        <div x-show="current === index" x-transition.opacity.duration.500ms class="absolute inset-0">
          <img :src="img" class="w-full h-80 object-cover rounded-xl">
        </div>
      </template>

      <!-- Arrows -->
      <button @click="current = (current === 0 ? images.length - 1 : current - 1)"
              class="absolute top-1/2 left-3 -translate-y-1/2 bg-black/40 text-white p-3 rounded-full hover:bg-black/60 transition">
        <i class="fas fa-chevron-left"></i>
      </button>
      <button @click="current = (current === images.length - 1 ? 0 : current + 1)"
              class="absolute top-1/2 right-3 -translate-y-1/2 bg-black/40 text-white p-3 rounded-full hover:bg-black/60 transition">
        <i class="fas fa-chevron-right"></i>
      </button>

      <!-- Dots -->
      <div class="absolute bottom-3 w-full flex justify-center gap-2">
        <template x-for="(img, index) in images" :key="index">
          <div @click="current = index"
               :class="current === index ? 'bg-yellow-400' : 'bg-white/50'"
               class="w-3 h-3 rounded-full cursor-pointer transition"></div>
        </template>
      </div>
    </div>
  </main>
</div>

<script>
// Page Loader
const navItems = document.querySelectorAll(".nav-item");
const mainContent = document.getElementById("main-content");

navItems.forEach(item => {
  item.addEventListener("click", () => {
    const page = item.getAttribute("data-page");

    mainContent.innerHTML = `
      <div class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-yellow-400 border-t-transparent"></div>
      </div>
    `;

    fetch(page)
      .then(res => res.text())
      .then(data => mainContent.innerHTML = data)
      .catch(() => mainContent.innerHTML = "<div class='p-6 text-center text-red-400'>Error loading page.</div>");

    navItems.forEach(i => i.classList.remove("bg-yellow-400/20", "text-yellow-400"));
    item.classList.add("bg-yellow-400/20", "text-yellow-400");
  });
});
</script>
</body>
</html>
