<?php
session_start();
require_once "db_connection.php";

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$adminId = $_SESSION['admin_id'];

// Fetch admin info
$stmt = $conn->prepare("SELECT fullname, email FROM users WHERE id = ? AND role = 'Admin'");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$stmt->bind_result($adminName, $adminEmail);
$stmt->fetch();
$stmt->close();

// Default location
$location = "BATELEC II Lipa";

// Fetch latest 10 notifications
$notifications = [];
$stmt = $conn->prepare("SELECT message, read_status, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param("i", $adminId);
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

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-900 via-blue-900 to-black font-[Montserrat] text-gray-100">

<!-- Header -->
<header class="bg-white/10 backdrop-blur-md border-b border-white/20 shadow-lg">
  <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-4">
    <!-- Logo + Title -->
    <div class="flex items-center gap-4">
      <img src="images/logo.png" alt="Logo" class="w-12 h-12 rounded-full border-2 border-yellow-400 shadow-md">
      <div>
        <h1 class="text-xl font-bold tracking-wide">F.A.S.T Admin Dashboard</h1>
        <p class="text-sm text-gray-300">Facial Authentication & Signature Technology</p>
      </div>
    </div>

    <!-- Admin Info + Notifications -->
    <div class="flex items-center gap-6">
      <span class="text-yellow-400 font-semibold"><?= htmlspecialchars($location) ?></span>

      <div class="flex items-center gap-2 bg-white/10 px-3 py-1 rounded-lg shadow-md">
        <i class="fas fa-user-circle text-yellow-300"></i>
        <span class="text-sm"><?= htmlspecialchars($adminName) ?></span>
      </div>

      <form action="logout.php" method="POST">
        <button type="submit" class="flex items-center gap-2 bg-red-600 hover:bg-red-700 px-3 py-1 rounded-lg shadow-md transition">
          <i class="fas fa-sign-out-alt"></i>
          <span>Log out</span>
        </button>
      </form>

      <!-- Notifications Dropdown -->
      <div class="relative" x-data="{ open: false }">
        <i @click="open = !open" class="fas fa-bell cursor-pointer text-xl hover:text-yellow-400 transition relative">
          <?php if (count($notifications) > 0): ?>
            <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
          <?php endif; ?>
        </i>
        <div x-show="open" x-transition class="absolute right-0 mt-3 w-72 bg-white/10 backdrop-blur-lg rounded-lg shadow-lg border border-white/20 z-50">
          <h4 class="px-4 py-2 font-semibold border-b border-white/20">Notifications</h4>
          <ul class="max-h-48 overflow-y-auto text-sm">
            <?php if (!empty($notifications)): ?>
              <?php foreach ($notifications as $note):
                  $readClass = $note['read_status'] ? 'text-gray-400' : 'text-gray-200 font-medium'; ?>
                  <li class="px-4 py-2 <?= $readClass ?>">
                    <?= htmlspecialchars($note['message']) ?>
                    <span class="text-xs text-gray-400 float-right"><?= date('M d, Y H:i', strtotime($note['created_at'])) ?></span>
                  </li>
              <?php endforeach; ?>
            <?php else: ?>
              <li class="px-4 py-2 text-gray-300">No new notifications</li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
</header>

<!-- Navigation -->
<nav class="bg-white/10 backdrop-blur-md border-b border-white/20 shadow-md">
  <div class="max-w-7xl mx-auto flex flex-wrap items-center gap-4 px-6 py-3">
    <?php
    $navItems = [
      "Dashboard" => "windows/Dashboard.php",
      "User Management" => "windows/UserManagement.php",
      "Documents" => "windows/Documents.php",
      "Biometric Data" => "windows/BioRecords.php",
      "Events" => "windows/Events.php"
    ];
    foreach ($navItems as $name => $page):
    ?>
      <div class="nav-item flex items-center gap-2 cursor-pointer hover:text-yellow-400 transition px-3 py-1 rounded" data-page="<?= $page ?>">
        <i class="fas fa-circle text-xs"></i>
        <span><?= $name ?></span>
      </div>
    <?php endforeach; ?>
  </div>
</nav>

<!-- Main Content -->
<main id="main-content" class="max-w-7xl mx-auto p-6">
  <div class="bg-white/5 border border-white/20 rounded-xl p-8 shadow-lg text-center text-gray-300 mb-8">
    <h2 class="text-2xl font-semibold mb-4">Welcome to F.A.S.T Admin Dashboard</h2>
    <p>Use the navigation menu above to manage users, documents, events, and biometric data.</p>
  </div>

  <!-- Image Carousel -->
  <div x-data="{ current: 0, images: [
                'images/success1.jpg',
                'images/success2.jpg',
                'images/success3.jpg'] }"
       x-init="setInterval(() => { current = (current + 1) % images.length }, 4000)"
       class="relative w-full max-w-4xl mx-auto rounded-xl overflow-hidden shadow-2xl mb-6">

    <template x-for="(img, index) in images" :key="index">
      <div x-show="current === index" x-transition class="absolute inset-0">
        <img :src="img" class="w-full h-64 object-cover rounded-xl">
      </div>
    </template>

    <button @click="current = (current === 0 ? images.length - 1 : current - 1)" 
            class="absolute top-1/2 left-2 transform -translate-y-1/2 bg-white/20 text-white p-2 rounded-full hover:bg-white/40 transition">
      <i class="fas fa-chevron-left"></i>
    </button>
    <button @click="current = (current === images.length - 1 ? 0 : current + 1)" 
            class="absolute top-1/2 right-2 transform -translate-y-1/2 bg-white/20 text-white p-2 rounded-full hover:bg-white/40 transition">
      <i class="fas fa-chevron-right"></i>
    </button>
  </div>
</main>

<script>
  // Dynamic Page Loader & Nav Highlight
  const navItems = document.querySelectorAll(".nav-item");
  const mainContent = document.getElementById("main-content");

  navItems.forEach(item => {
    item.addEventListener("click", () => {
      const page = item.getAttribute("data-page");
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
