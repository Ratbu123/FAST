<?php
session_start();
require_once "db_connection.php"; // Ensure this file defines $conn

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit();
}

// Handle "Remember Me" cookie
if (isset($_COOKIE['rememberme'])) {
    list($username, $token) = explode(':', $_COOKIE['rememberme']);
    $stmt = $conn->prepare("SELECT user_id, remember_token, fullname FROM users WHERE username = ? AND role = 'Admin' LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $dbToken, $fullname);
        $stmt->fetch();
        if ($dbToken && hash_equals($dbToken, $token)) {
            $_SESSION['user_id']  = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['fullname'] = $fullname;
            header("Location: main.php");
            exit();
        }
    }
    $stmt->close();
}

// Handle login form submission
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['rememberMe']);

    $stmt = $conn->prepare("SELECT user_id, password, fullname FROM users WHERE username = ? AND role = 'Admin' LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $hashedPassword, $fullname);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            // ✅ Login success
            $_SESSION['user_id']  = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['fullname'] = $fullname;

            // Remember Me functionality
            if ($remember) {
                $token = bin2hex(random_bytes(16));
                setcookie('rememberme', "$username:$token", time() + (86400 * 30), "/", "", false, true);

                $update = $conn->prepare("UPDATE users SET remember_token=? WHERE user_id=?");
                $update->bind_param("si", $token, $user_id);
                $update->execute();
                $update->close();
            }

            header("Location: main.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - F.A.S.T</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen flex items-center justify-center bg-cover bg-center relative" style="background-image: url('images/LogInBG.jpg');">

<!-- Overlay -->
<div class="absolute inset-0 bg-black/50"></div>

<!-- Login Card -->
<div class="relative w-full max-w-md bg-gradient-to-br from-blue-800/95 to-blue-600/90 shadow-2xl rounded-2xl p-8 text-white border border-blue-400/30">

    <!-- ADMIN LOGIN Heading -->
    <h2 class="text-3xl font-bold tracking-wide text-center mb-6">ADMIN LOGIN</h2>

    <!-- Logo + App Name -->
    <div class="flex items-center justify-center mb-3 gap-3">
        <img src="images/logo.png" alt="F.A.S.T Logo" class="w-16 h-16 rounded-full shadow-lg border-2 border-yellow-400">
        <span class="text-4xl font-extrabold text-yellow-300 drop-shadow-md">F.A.S.T</span>
    </div>

    <!-- Facial Authentication Signature Technology -->
    <p class="text-center text-gray-200 text-sm mb-6 italic">
        Facial Authentication Signature Technology
    </p>

    <!-- Login Form -->
    <form action="" method="POST" class="space-y-5">
        <div>
            <label for="username" class="block text-sm font-medium text-gray-200">Username</label>
            <div class="mt-1 relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-300"><i class="fas fa-user"></i></span>
                <input type="text" id="username" name="username" required
                    class="w-full pl-10 pr-3 py-2 rounded-lg bg-white/10 text-white placeholder-gray-300 border border-white/20 focus:ring-2 focus:ring-yellow-400 focus:outline-none"
                    placeholder="Enter your username">
            </div>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-200">Password</label>
            <div class="mt-1 relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-300"><i class="fas fa-lock"></i></span>
                <input type="password" id="password" name="password" required autocomplete="off"
                    class="w-full pl-10 pr-3 py-2 rounded-lg bg-white/10 text-white placeholder-gray-300 border border-white/20 focus:ring-2 focus:ring-yellow-400 focus:outline-none"
                    placeholder="Enter your password">
            </div>
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center text-gray-200">
                <input type="checkbox" name="rememberMe" class="h-4 w-4 text-yellow-400 border-gray-300 rounded">
                <span class="ml-2">Remember Me</span>
            </label>
            <a href="forgot-password.php" class="text-yellow-300 hover:underline">Forgot Password?</a>
        </div>

        <button type="submit" class="w-full bg-yellow-400 text-gray-900 font-semibold py-2 px-4 rounded-lg shadow-lg hover:bg-yellow-500 hover:shadow-xl transition duration-300">
            Login
        </button>
    </form>

    <!-- Powered by FAST Technology -->
    <p class="text-center text-gray-300 text-xs mt-6">Powered by FAST Technology</p>
</div>

<!-- Error Modal -->
<?php if(!empty($error)): ?>
<div id="error-modal" class="fixed inset-0 flex items-center justify-center z-50">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="bg-red-600 text-white p-6 rounded-xl shadow-xl transform transition-all scale-0" id="modal-box">
        <div class="flex items-center gap-3">
            <i class="fas fa-exclamation-triangle text-2xl"></i>
            <span class="font-semibold"><?php echo htmlspecialchars($error); ?></span>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
const modalBox = document.getElementById('modal-box');
if(modalBox){
    setTimeout(() => { 
        modalBox.classList.remove('scale-0'); 
        modalBox.classList.add('scale-100'); 
    }, 100);
    setTimeout(() => {
        modalBox.classList.remove('scale-100'); 
        modalBox.classList.add('scale-0');
        setTimeout(() => { 
            const modal = document.getElementById('error-modal'); 
            if(modal) modal.remove(); 
        }, 500);
    }, 3000);
}
</script>

</body>
</html>
