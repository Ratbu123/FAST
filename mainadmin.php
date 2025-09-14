<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}
$admin_username = $_SESSION['admin_username'] ?? 'Head Admin';
$admin_fullname = $_SESSION['admin_fullname'] ?? 'Head Admin';
$admin_email = $_SESSION['admin_email'] ?? 'admin@example.com';
?>
<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: false }">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>F.A.S.T Admin Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
body { transition: background-color 0.5s, color 0.5s; font-family: 'Montserrat', sans-serif; }
.carousel-container { perspective: 1200px; display: flex; justify-content: center; align-items: center; height: 28rem; position: relative; }
.carousel-item { transition: transform 1s ease-in-out, opacity 0.8s ease-in-out, box-shadow 0.5s; position: absolute; }
.hover-glow { box-shadow: 0 0 30px rgba(255,255,0,0.6); transform: scale(1.05); border:2px solid yellow; }
.animate-pulse-glow { animation: pulseGlow 2s infinite ease-in-out; }
@keyframes pulseGlow { 0%,100%{transform:scale(1); box-shadow:0 0 20px rgba(255,255,0,0.4);} 50%{transform:scale(1.05); box-shadow:0 0 35px rgba(255,255,0,0.6);} }

/* Gradient animation for welcome text */
@keyframes gradientBG { 0% {background-position: 0% 50%;} 50% {background-position: 100% 50%;} 100% {background-position: 0% 50%;} }
.animate-gradient { background-size: 200% 200%; animation: gradientBG 4s ease infinite; }

/* Pulse Glow for welcome paragraph */
@keyframes pulseTextGlow { 0%,100%{transform:scale(1); text-shadow:0 0 10px rgba(255,255,0,0.3);} 50%{transform:scale(1.02); text-shadow:0 0 20px rgba(255,255,0,0.5);} }
.animate-text-glow { animation: pulseTextGlow 2s infinite ease-in-out; }
</style>
</head>
<body :class="darkMode ? 'bg-gray-900 text-gray-100' : 'bg-gray-950 text-gray-100'" class="flex flex-col min-h-screen">

<!-- Header -->
<header class="bg-white/5 backdrop-blur-xl border-b border-white/10 shadow-md sticky top-0 z-50 flex justify-between items-center px-6 py-3">
  <div class="flex items-center gap-4">
    <img src="images/logo.png" alt="F.A.S.T Logo" class="w-16 h-16 rounded-full border-2 border-yellow-400 shadow-lg hover:scale-105 transition-transform">
    <div>
      <h1 class="text-2xl font-bold text-yellow-400 drop-shadow-lg">F.A.S.T</h1>
      <p class="text-sm text-gray-300">Facial Authentication & Signature Technology</p>
    </div>
  </div>
  <div class="flex items-center gap-4">
    <button @click="darkMode = !darkMode" class="text-yellow-400 text-lg hover:text-yellow-300 transition" title="Toggle Dark Mode">
      <i :class="darkMode ? 'fas fa-sun' : 'fas fa-moon'"></i>
    </button>
    <div x-data="{ open: false }" class="relative">
      <button @click="open=!open" class="flex items-center gap-2 bg-white/10 px-4 py-2 rounded-xl shadow-lg hover:bg-white/20 transition">
        <i class="fas fa-user-circle text-yellow-400 text-lg"></i>
        <span class="text-sm font-medium"><?php echo htmlspecialchars($admin_username); ?></span>
        <i class="fas fa-chevron-down text-xs ml-1"></i>
      </button>
      <div x-show="open" @click.away="open=false" x-transition class="absolute right-0 mt-2 w-48 bg-white/10 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 z-50 p-4">
        <button @click="profileOpen = true; open=false" class="block w-full text-left text-gray-200 hover:bg-white/5 rounded-lg px-2 py-2">Profile</button>
        <button @click="settingsOpen = true; open=false" class="block w-full text-left text-gray-200 hover:bg-white/5 rounded-lg px-2 py-2">Settings</button>
        <button @click="logout()" class="block w-full text-left text-red-500 hover:bg-white/5 rounded-lg px-2 py-2">Log out</button>
      </div>
    </div>
    <div x-data="{ open: false }" class="relative">
      <i @click="open = !open" class="fas fa-bell cursor-pointer text-xl hover:text-yellow-400 transition relative">
        <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full animate-ping"></span>
      </i>
      <div x-show="open" x-transition class="absolute right-0 mt-3 w-80 bg-white/10 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 z-50">
        <h4 class="px-4 py-3 font-semibold border-b border-white/20 text-gray-200 flex justify-between items-center">
          Notifications
          <button class="text-xs text-yellow-400 hover:underline" @click="markAllRead()">Mark all read</button>
        </h4>
        <ul class="max-h-60 overflow-y-auto text-sm">
          <li class="px-4 py-3 text-gray-200 font-medium hover:bg-white/5 transition rounded-lg">New user registered
            <span class="text-xs text-gray-400 float-right">Sep 14, 2025 10:15</span>
          </li>
          <li class="px-4 py-3 text-gray-400 hover:bg-white/5 transition rounded-lg">System backup completed
            <span class="text-xs text-gray-400 float-right">Sep 13, 2025 22:30</span>
          </li>
          <li class="px-4 py-3 text-gray-300">No new notifications</li>
        </ul>
      </div>
    </div>
  </div>
</header>

<!-- Navigation -->
<nav class="bg-white/5 backdrop-blur-xl border-b border-white/10 shadow-md sticky top-[72px] z-40">
  <div class="max-w-7xl mx-auto flex items-center space-x-6 px-6 py-3 text-sm font-medium overflow-x-auto">
    <div class="nav-item flex items-center gap-2 px-4 py-2 rounded-xl cursor-pointer hover:bg-yellow-400/20 transition" data-page="./windows/admindashboard.php"><i class="fas fa-home"></i><span>Dashboard</span></div>
    <div class="nav-item flex items-center gap-2 px-4 py-2 rounded-xl cursor-pointer hover:bg-yellow-400/20 transition" data-page="./windows/adminusermanagement.php"><i class="fas fa-users"></i><span>User Management</span></div>
    <div class="nav-item flex items-center gap-2 px-4 py-2 rounded-xl cursor-pointer hover:bg-yellow-400/20 transition" data-page="./windows/admindocuments.php"><i class="fas fa-file-alt"></i><span>Documents</span></div>
    <div class="nav-item flex items-center gap-2 px-4 py-2 rounded-xl cursor-pointer hover:bg-yellow-400/20 transition" data-page="./windows/adminbiorecords.php"><i class="fas fa-fingerprint"></i><span>Biometric Data</span></div>
    <div class="nav-item flex items-center gap-2 px-4 py-2 rounded-xl cursor-pointer hover:bg-yellow-400/20 transition" data-page="./windows/adminevents.php"><i class="fas fa-calendar-alt"></i><span>Events</span></div>
  </div>
</nav>

<main id="main-content" class="max-w-7xl mx-auto p-6 flex-1">

<!-- Welcome Section -->
<div class="text-center mb-10">
  <h2 class="text-5xl md:text-6xl font-extrabold mb-4 bg-clip-text text-transparent animate-gradient bg-gradient-to-r from-yellow-400 via-yellow-300 to-yellow-500 drop-shadow-lg">
    Welcome to <span class="text-white hover:text-yellow-300 transition-colors">F.A.S.T Admin Dashboard</span>
  </h2>
  <p class="text-gray-300 text-lg md:text-xl animate-text-glow mb-6">
    Manage <span class="text-yellow-400 font-semibold hover:underline transition-all cursor-pointer">users</span>, 
    <span class="text-yellow-400 font-semibold hover:underline transition-all cursor-pointer">documents</span>, 
    <span class="text-yellow-400 font-semibold hover:underline transition-all cursor-pointer">events</span>, 
    and <span class="text-yellow-400 font-semibold hover:underline transition-all cursor-pointer">biometric data</span> efficiently using the menu above.
  </p>
  </div>
</div>

<!-- 3D Spinning Cube Carousel -->
<div x-data="enhancedCarousel()" x-init="init()" class="carousel-container">
  <template x-for="(slide, index) in slides" :key="index">
    <div class="carousel-item w-72 md:w-96 h-64 md:h-72 rounded-3xl shadow-2xl overflow-hidden flex flex-col items-center justify-center"
         :style="getStyle(index)"
         @mouseenter="hover(index)" @mouseleave="leave()"
         :class="currentHover===index ? 'hover-glow scale-105' : ''">
      <img :src="slide.src" alt="" class="w-full h-full object-cover rounded-3xl">
      <div class="mt-2 text-center text-white bg-black/40 backdrop-blur-md px-4 py-1 rounded-xl font-semibold text-base md:text-lg" x-text="slide.caption"></div>
    </div>
  </template>

  <!-- Dot Pagination -->
  <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex space-x-2">
    <template x-for="(slide,index) in slides" :key="index">
      <span @click="goTo(index)" :class="current===index ? 'bg-yellow-400' : 'bg-black'" class="w-3 h-3 rounded-full cursor-pointer transition"></span>
    </template>
  </div>
</div>

</main>

<!-- Footer -->
<footer class="bg-yellow-400 mt-auto">
  <div class="max-w-7xl mx-auto px-6 py-4 flex flex-col md:flex-row items-center justify-between text-black text-sm">
    <span>&copy; <span x-text="new Date().getFullYear()"></span> F.A.S.T Admin Dashboard. All rights reserved.</span>
    <div class="flex gap-4 mt-2 md:mt-0">
      <a href="#" class="text-black hover:text-blue-500 transition"><i class="fab fa-facebook-f"></i></a>
      <a href="#" class="text-black hover:text-blue-500 transition"><i class="fab fa-twitter"></i></a>
      <a href="#" class="text-black hover:text-blue-500 transition"><i class="fab fa-instagram"></i></a>
    </div>
  </div>
</footer>

<script>
function enhancedCarousel() {
  return {
    slides: [
      { src: 'images/LogInBG.jpg', caption: 'Welcome to F.A.S.T Dashboard' },
      { src: 'images/OIP.webp', caption: 'Manage Users & Documents Easily' },
      { src: 'images/vn3.jpg', caption: 'Track Biometric Data Efficiently' },
      { src: 'images/image4.jpg', caption: 'Organize Events Smoothly' }
    ],
    current: 0,
    currentHover: null,
    interval: null,
    speed: 4000,
    init() { this.play(); document.addEventListener('keydown', e => { if(e.key==='ArrowLeft') this.prev(); if(e.key==='ArrowRight') this.next(); }); },
    play() { this.interval = setInterval(()=>this.next(), this.speed); },
    pause() { clearInterval(this.interval); },
    prev() { this.current = (this.current===0?this.slides.length-1:this.current-1); },
    next() { this.current = (this.current===this.slides.length-1?0:this.current+1); },
    goTo(index) { this.current=index; },
    hover(index){ this.currentHover=index; this.pause(); },
    leave(){ this.currentHover=null; this.play(); },
    getStyle(index){
      const total = this.slides.length;
      const angle = 360/total * index - 360/total * this.current;
      const radius = 300;
      const rad = angle * Math.PI / 180;
      const x = radius * Math.sin(rad);
      const z = radius * (1 - Math.cos(rad));
      const scale = index === this.current ? 1 : 0.75;
      const zIndex = index === this.current ? 20 : 10;
      const opacity = Math.abs(index - this.current) > 1 ? 0.3 : 1;
      return `transform: translateX(${x}px) translateZ(${-z}px) rotateY(${angle}deg) scale(${scale}); z-index:${zIndex}; opacity:${opacity};`;
    }
  }
}

// Navigation Loader
document.querySelectorAll(".nav-item").forEach(item => {
  item.addEventListener("click", () => {
    const page = item.dataset.page;
    fetch(page).then(res => res.text()).then(data => document.getElementById("main-content").innerHTML=data).catch(()=>document.getElementById("main-content").innerHTML="<div class='p-6 text-center text-red-400'>Error loading page.</div>");
    document.querySelectorAll(".nav-item").forEach(i=>i.classList.remove("bg-yellow-400/20","text-yellow-400"));
    item.classList.add("bg-yellow-400/20","text-yellow-400");
  });
});

// Logout
function logout() {
  Swal.fire({
    title: "Are you sure?",
    text: "You will be logged out.",
    icon: "warning",
    showCancelButton:true,
    confirmButtonColor:"#d33",
    cancelButtonColor:"#3085d6",
    confirmButtonText:"Yes, log out"
  }).then(result=>{if(result.isConfirmed) window.location.href="logout.php?logged_out=1";});
}

// Notifications
function markAllRead() { document.querySelectorAll('.fa-bell + span').forEach(el=>el.remove()); Swal.fire('All notifications marked as read','', 'success'); }

<?php if(isset($_GET['login']) && $_GET['login']==='success'): ?>
Swal.fire({icon:'success', title:'Login Successful', text:'Welcome back, <?php echo addslashes($admin_username); ?>!', confirmButtonColor:'#facc15'});
<?php endif; ?>
</script>
</body>
</html>
