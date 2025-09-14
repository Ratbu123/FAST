<?php
session_start();
require_once "config.php"; // Your PDO connection

// Redirect if already logged in to mainadmin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && !isset($_GET['success'])) {
    header("Location: mainadmin.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, username, password, fullname FROM admins WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_username'] = $row['username'];
            $_SESSION['admin_fullname'] = $row['fullname'];

            // Redirect to same page with success flag to trigger SweetAlert
            header("Location: admin-login.php?success=1");
            exit;
        }
    }

    // Login failed
    header("Location: admin-login.php?error=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - F.A.S.T</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-cover bg-center relative" 
      style="background-image: url('images/LogInBG.jpg');">

<!-- Overlay -->
<div class="absolute inset-0 bg-black/50"></div>

<!-- Card -->
<div class="relative w-full max-w-md bg-white/10 backdrop-blur-lg shadow-2xl 
            rounded-2xl p-8 text-white border border-white/20">

  <!-- ADMIN LOGIN -->
  <h2 class="text-center text-3xl font-extrabold text-yellow-400 mb-6 tracking-wide">
    ADMIN LOGIN
  </h2>

  <!-- Logo + App Name Side by Side -->
  <div class="flex items-center justify-center gap-3 mb-4">
    <img src="./images/logo.png" alt="Logo" class="w-16 h-16 rounded-full shadow-lg border-2 border-yellow-400">
    <h1 class="text-4xl font-bold text-yellow-300 drop-shadow-md">F.A.S.T</h1>
  </div>

  <!-- Subtitle -->
  <p class="text-center text-gray-200 text-sm mb-6">
    Facial Authentication & Signature Technology
  </p>

  <!-- Login Form -->
  <form action="" method="POST" class="space-y-5">
    <!-- Username -->
    <div>
      <label for="username" class="block text-sm font-medium text-gray-200">Username</label>
      <div class="mt-1 relative">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-300">
          <i class="fas fa-user"></i>
        </span>
        <input type="text" id="username" name="username" required
          class="w-full pl-10 pr-3 py-2 rounded-lg bg-white/10 text-white placeholder-gray-300
                 border border-white/20 focus:ring-2 focus:ring-yellow-400 focus:outline-none"
          placeholder="Enter your username">
      </div>
    </div>

    <!-- Password -->
    <div>
      <label for="password" class="block text-sm font-medium text-gray-200">Password</label>
      <div class="mt-1 relative">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-300">
          <i class="fas fa-lock"></i>
        </span>
        <input type="password" id="password" name="password" required autocomplete="off"
          class="w-full pl-10 pr-3 py-2 rounded-lg bg-white/10 text-white placeholder-gray-300
                 border border-white/20 focus:ring-2 focus:ring-yellow-400 focus:outline-none"
          placeholder="Enter your password">
      </div>
    </div>

    <!-- Remember Me + Forgot Password -->
    <div class="flex items-center justify-between text-sm">
      <label class="flex items-center text-gray-200">
        <input type="checkbox" id="rememberMe" class="h-4 w-4 text-yellow-400 border-gray-300 rounded">
        <span class="ml-2">Remember Me</span>
      </label>
      <a href="forgot-password.php" class="text-yellow-300 hover:underline">Forgot Password?</a>
    </div>

    <!-- Submit -->
    <button type="submit"
      class="w-full bg-yellow-400 text-gray-900 font-semibold py-2 px-4 rounded-lg shadow-lg 
             hover:bg-yellow-500 hover:shadow-xl transition duration-300">
      Login
    </button>
  </form>

  <!-- Footer -->
  <div class="mt-6 text-center">
    <p class="text-gray-300 text-xs">Powered by <span class="text-yellow-400 font-bold">FAST Technology</span></p>
  </div>
</div>

<!-- SweetAlert Messages -->
<?php if (isset($_GET['error'])): ?>
<script>
Swal.fire({
  icon: 'error',
  title: 'Login Failed',
  text: 'Invalid username or password!',
  confirmButtonColor: '#facc15'
});
</script>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<script>
Swal.fire({
  icon: 'success',
  title: 'Login Successful',
  html: 'Welcome, <b><?php echo addslashes($_SESSION['admin_fullname']); ?></b>!',
  confirmButtonText: 'Proceed',
  confirmButtonColor: '#facc15',
  allowOutsideClick: false,
  allowEscapeKey: false
}).then(() => {
  window.location.href = "mainadmin.php";
});
</script>
<?php endif; ?>

</body>
</html>
