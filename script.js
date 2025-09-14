// === Nav underline animation + dynamic content loading ===

// Select elements
const navItems = document.querySelectorAll('.nav-item');
const underline = document.querySelector('.nav-underline');
const mainContent = document.getElementById('main-content');

// Move underline to the active nav item
function moveUnderline(element) {
  const rect = element.getBoundingClientRect();
  const parentRect = element.parentElement.getBoundingClientRect();
  underline.style.width = rect.width + "px";
  underline.style.left = (rect.left - parentRect.left) + "px";
}

// Load a partial HTML file into <main>
async function loadPage(pageUrl) {
  try {
    const response = await fetch(pageUrl);
    if (!response.ok) throw new Error(`Failed to load ${pageUrl}`);

    let content = await response.text();

    // Remove full-page tags if present
    content = content
      .replace(/<!DOCTYPE[^>]*>/gi, '')
      .replace(/<html[^>]*>/gi, '')
      .replace(/<\/html>/gi, '')
      .replace(/<head[^>]*>[\s\S]*?<\/head>/gi, '')
      .replace(/<body[^>]*>/gi, '')
      .replace(/<\/body>/gi, '')
      .replace(/<style[^>]*>[\s\S]*?<\/style>/gi, ''); // strips inline styles

    mainContent.innerHTML = content;
  } catch (error) {
    mainContent.innerHTML = `<p style="color:red;">Error loading page.</p>`;
    console.error(error);
  }
}

// Add click events to nav items
navItems.forEach(item => {
  item.addEventListener('click', () => {
    document.querySelector('.nav-item.active')?.classList.remove('active');
    item.classList.add('active');
    moveUnderline(item);

    const pageUrl = item.getAttribute('data-page');
    if (pageUrl) loadPage(pageUrl);
  });
});

// Load default content on page load
window.addEventListener('load', () => {
  const active = document.querySelector('.nav-item.active');
  if (active) {
    moveUnderline(active);
    const pageUrl = active.getAttribute('data-page');
    if (pageUrl) loadPage(pageUrl); // load default dashboard content
  }
});

// Recalculate underline position on resize
window.addEventListener('resize', () => {
  const active = document.querySelector('.nav-item.active');
  if (active) moveUnderline(active);
});


// Add login button redirect to Dashboard.html
const loginBtn = document.querySelector('.login-btn');
if (loginBtn) {
    loginBtn.addEventListener('click', function() {
        window.location.href = 'Main.html';
    });
}

const logoutBtn = document.querySelector('.btn-logout');
if (logoutBtn) {
    logoutBtn.addEventListener('click', function() {
        window.location.href = 'Login.html';
    });
}

document.addEventListener("DOMContentLoaded", () => {
    const bell = document.querySelector(".notification-bell");
    const dropdown = document.getElementById("notification-dropdown");

    bell.addEventListener("click", () => {
        dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
    });

    // Optional: close dropdown if you click outside
    document.addEventListener("click", (e) => {
        if (!bell.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = "none";
        }
    });
});
