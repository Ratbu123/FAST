<?php
session_start();
require_once __DIR__ . '/../config.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit;
}

// Handle Delete Event via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM agma_events WHERE id = :id");
    $stmt->bindParam(':id', $deleteId, PDO::PARAM_INT);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Event deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete event.']);
    }
    exit;
}

// Fetch all events
$stmt = $conn->prepare("SELECT * FROM agma_events ORDER BY signed_date DESC");
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Metrics
$totalEvents = count($events);
$verifiedEvents = $conn->query("SELECT COUNT(*) FROM agma_events WHERE status='Verified'")->fetchColumn();
$pendingEvents = $conn->query("SELECT COUNT(*) FROM agma_events WHERE status='Pending'")->fetchColumn();
$declinedEvents = $conn->query("SELECT COUNT(*) FROM agma_events WHERE status='Declined'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en" x-data>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AGMA Events Dashboard - F.A.S.T</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
  body { font-family: 'Montserrat', sans-serif; transition: background 0.3s, color 0.3s; }
  .hover-glow:hover { box-shadow: 0 0 25px rgba(255,255,0,0.5); }
</style>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen flex flex-col">

<main class="max-w-7xl mx-auto p-6 flex-1">

  <!-- Toast Notifications -->
  <?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
  <div id="toast" class="fixed top-5 right-5 z-50 px-6 py-4 rounded-xl shadow-lg text-white font-semibold transition-transform transform">
      <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-500 px-4 py-2 rounded-lg"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
      <?php else: ?>
        <div class="bg-red-500 px-4 py-2 rounded-lg"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
      <?php endif; ?>
  </div>
  <script>
    setTimeout(() => {
      const toast = document.getElementById('toast');
      toast.style.transform = 'translateX(120%)';
      setTimeout(() => toast.remove(), 500);
    }, 3000);
  </script>
  <?php endif; ?>

  <!-- Header -->
  <header class="mb-14 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
    <div class="flex items-center gap-3">
      <div class="w-14 h-14 rounded-2xl bg-yellow-400 flex items-center justify-center text-gray-900 text-2xl shadow-lg">
        <i class="fas fa-calendar-alt"></i>
      </div>
      <div>
        <h1 class="text-4xl font-bold text-yellow-400">AGMA Events</h1>
        <p class="text-gray-400 text-sm">Annual General Meeting Assembly</p>
      </div>
    </div>
    <div>
      <button @click="document.getElementById('addModal').classList.remove('hidden')"
              class="px-5 py-2 rounded-xl text-sm font-semibold bg-yellow-500 text-white shadow-lg hover:bg-yellow-600 flex items-center gap-2">
        <i class="fas fa-plus"></i> Add Event
      </button>
    </div>
  </header>

  <!-- Metrics Grid -->
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
    <?php
      $cards = [
        ['title'=>'Total Events','icon'=>'fa-list','count'=>$totalEvents,'bg'=>'from-blue-600/80 to-blue-800/90','desc'=>'All registered events'],
        ['title'=>'Verified','icon'=>'fa-check-circle','count'=>$verifiedEvents,'bg'=>'from-green-600/80 to-green-800/90','desc'=>'Approved events'],
        ['title'=>'Pending','icon'=>'fa-clock','count'=>$pendingEvents,'bg'=>'from-indigo-600/80 to-indigo-800/90','desc'=>'Awaiting verification'],
        ['title'=>'Declined','icon'=>'fa-times-circle','count'=>$declinedEvents,'bg'=>'from-red-600/80 to-red-800/90','desc'=>'Rejected requests'],
      ];
      foreach($cards as $c): ?>
      <div class="bg-gradient-to-br <?= $c['bg'] ?> rounded-2xl shadow-xl p-6 hover-glow transition">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-medium text-gray-200"><?= $c['title'] ?></h3>
          <i class="fas <?= $c['icon'] ?> text-2xl text-white/70"></i>
        </div>
        <div class="text-5xl font-extrabold text-white"><?= $c['count'] ?></div>
        <p class="text-sm text-gray-300 mt-2"><?= $c['desc'] ?></p>
      </div>
    <?php endforeach; ?>
  </section>

  <!-- Event Table -->
  <section class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl shadow-xl mb-12 overflow-x-auto">
    <div class="px-6 py-5 border-b border-white/10 flex flex-col sm:flex-row sm:justify-between gap-3 items-center">
      <div>
        <h2 class="text-xl font-semibold text-yellow-400 flex items-center gap-2">
          <i class="fas fa-table"></i> Event Records
        </h2>
        <p class="text-gray-400 text-sm">List of all registered events</p>
      </div>
      <div class="flex gap-3">
        <input type="text" placeholder="Search events..." class="px-4 py-2 rounded-xl bg-gray-900 border border-gray-700 text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none">
        <select class="px-4 py-2 rounded-xl bg-gray-900 border border-gray-700 text-sm">
          <option>2025</option>
          <option>2024</option>
          <option>2023</option>
        </select>
      </div>
    </div>
    <table class="min-w-full divide-y divide-gray-800 text-sm">
      <thead class="bg-gray-900/50">
        <tr>
          <th class="px-6 py-3 text-left font-semibold text-gray-300">Name</th>
          <th class="px-6 py-3 text-left font-semibold text-gray-300">Member Since</th>
          <th class="px-6 py-3 text-left font-semibold text-gray-300">Status</th>
          <th class="px-6 py-3 text-left font-semibold text-gray-300">Signed Date</th>
          <th class="px-6 py-3 text-center font-semibold text-gray-300">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-800 text-gray-200" id="eventsTable">
        <?php if($events): foreach($events as $ev): ?>
        <tr id="event-<?= $ev['id'] ?>" class="hover:bg-white/5 transition">
          <td class="px-6 py-4"><?= htmlspecialchars($ev['name']) ?></td>
          <td class="px-6 py-4"><?= date('F Y', strtotime($ev['member_since'])) ?></td>
          <td class="px-6 py-4">
            <span class="px-3 py-1 rounded-full text-xs font-semibold 
              <?= $ev['status']=='Verified'?'bg-green-100/20 text-green-400':'' ?> 
              <?= $ev['status']=='Pending'?'bg-yellow-100/20 text-yellow-400':'' ?> 
              <?= $ev['status']=='Declined'?'bg-red-100/20 text-red-400':'' ?>">
              <?= htmlspecialchars($ev['status']) ?>
            </span>
          </td>
          <td class="px-6 py-4"><?= date('M d, Y', strtotime($ev['signed_date'])) ?></td>
          <td class="px-6 py-4 text-center flex justify-center gap-2">
            <button onclick="openViewModal(<?= $ev['id'] ?>)" 
                    class="px-3 py-1 text-xs bg-indigo-600 rounded-lg hover:bg-indigo-700 text-white">View</button>
            <button onclick="deleteEvent(<?= $ev['id'] ?>)" 
                    class="px-3 py-1 text-xs bg-red-600 rounded-lg hover:bg-red-700 text-white">Delete</button>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="5" class="text-center py-6 text-gray-400"><i class="fas fa-info-circle mr-2"></i> No events found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </section>

</main>

<!-- Add Event Modal -->
<div id="addModal" class="fixed inset-0 bg-black/70 hidden flex items-center justify-center z-50">
  <div class="bg-gray-900 rounded-2xl shadow-2xl w-full max-w-lg p-8 relative">
    <button onclick="document.getElementById('addModal').classList.add('hidden')" 
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-200">
      <i class="fas fa-times text-lg"></i>
    </button>
    <h2 class="text-2xl font-bold text-yellow-400 mb-6">Add AGMA Event</h2>
    <form method="POST" action="./windows/addevent.php" class="space-y-4">
      <input type="text" name="name" placeholder="Event Name" required
             class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:outline-none text-gray-100">
      <input type="month" name="member_since" placeholder="Member Since" required
             class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:outline-none text-gray-100">
      <select name="status" required
              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:outline-none text-gray-100">
        <option value="">Select Status</option>
        <option value="Verified">Verified</option>
        <option value="Pending">Pending</option>
        <option value="Declined">Declined</option>
      </select>
      <input type="date" name="signed_date" required
             class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:outline-none text-gray-100">
      <button type="submit" 
              class="w-full bg-yellow-500 text-white py-3 rounded-xl shadow hover:bg-yellow-600 transition font-semibold">
        Add Event
      </button>
    </form>
  </div>
</div>

<!-- View Event Modal -->
<div id="viewModal" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-50">
  <div class="bg-gray-900 rounded-2xl shadow-2xl w-full max-w-lg p-8 relative">
    <button onclick="closeViewModal()" 
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-200">
      <i class="fas fa-times text-lg"></i>
    </button>
    <h2 class="text-2xl font-bold text-yellow-400 mb-6" id="modalEventName">Event Name</h2>
    
    <div class="space-y-4 text-gray-200">
      <div class="flex justify-between">
        <span class="text-gray-400">Status:</span>
        <span id="modalEventStatus" class="px-3 py-1 rounded-full text-xs font-semibold"></span>
      </div>
      <div class="flex justify-between">
        <span class="text-gray-400">Member Since:</span>
        <span id="modalMemberSince"></span>
      </div>
      <div class="flex justify-between">
        <span class="text-gray-400">Signed Date:</span>
        <span id="modalSignedDate"></span>
      </div>
    </div>
  </div>
</div>

<script>
// AJAX Delete Event
function deleteEvent(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This event will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'delete_id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const row = document.getElementById('event-' + id);
                    if (row) row.remove();
                    Swal.fire('Deleted!', data.message, 'success');
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(err => Swal.fire('Error!', 'Something went wrong.', 'error'));
        }
    });
}

// View Event Modal
function openViewModal(id) {
    const row = document.getElementById('event-' + id);
    const name = row.children[0].textContent;
    const memberSince = row.children[1].textContent;
    const status = row.children[2].textContent.trim();
    const signedDate = row.children[3].textContent;

    document.getElementById('modalEventName').textContent = name;
    const statusEl = document.getElementById('modalEventStatus');
    statusEl.textContent = status;
    statusEl.className = "px-3 py-1 rounded-full text-xs font-semibold " +
        (status === 'Verified' ? "bg-green-100/20 text-green-400" :
         status === 'Pending' ? "bg-yellow-100/20 text-yellow-400" :
         status === 'Declined' ? "bg-red-100/20 text-red-400" : "");

    document.getElementById('modalMemberSince').textContent = memberSince;
    document.getElementById('modalSignedDate').textContent = signedDate;

    document.getElementById('viewModal').classList.remove('hidden');
}

function closeViewModal() {
    document.getElementById('viewModal').classList.add('hidden');
}
</script>

</body>
</html>
