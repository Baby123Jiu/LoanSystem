// Shared helpers used across every page.

const baseUrl = sessionStorage.getItem('apiBaseUrl') || 'http://localhost/calculator/api';

function currentUser() {
  return {
    username: sessionStorage.getItem('username'),
    role: sessionStorage.getItem('role')
  };
}

function requirePageLogin() {
  const user = currentUser();
  if (!user.username) {
    window.location.href = 'login.html';
    return null;
  }
  return user;
}

function requirePageRole(allowedRoles) {
  const user = requirePageLogin();
  if (user && !allowedRoles.includes(user.role)) {
    window.location.href = 'calculator.html';
    return null;
  }
  return user;
}

async function logout() {
  try {
    await fetch(baseUrl + '/logout.php', { method: 'POST', credentials: 'include' });
  } catch (e) {}
  sessionStorage.clear();
  window.location.href = 'login.html';
}

// Fills in the "signed in as ..." label and wires up logout buttons.
// Call this once on every page after the DOM is ready.
function initTopbar() {
  const user = currentUser();
  const whoLabel = document.getElementById('whoLabel');
  if (whoLabel && user.username) {
    whoLabel.textContent = 'signed in as ' + user.username + ' (' + user.role + ')';
  }

  document.querySelectorAll('.logout-link').forEach(function (btn) {
    btn.addEventListener('click', logout);
  });

  // Hide any nav link marked admin-only if the current user isn't an admin.
    // Hide any nav link marked admin-only if the current user isn't an admin.
  if (user.role !== 'admin') {
    document.querySelectorAll('[data-admin-only]').forEach(function (el) {
      el.style.display = 'none';
    });
  }

  // Hide any nav link marked staff-only if the current user is an admin.
  if (user.role === 'admin') {
    document.querySelectorAll('[data-staff-only]').forEach(function (el) {
      el.style.display = 'none';
    });
  }
}

document.addEventListener('DOMContentLoaded', initTopbar);