/* OrbitPesa — Dashboard JavaScript */
(function() {
  'use strict';

  // ---- Sidebar toggle (mobile) ----
  const sidebar = document.getElementById('sidebar');
  const menuToggle = document.getElementById('menuToggle');
  const sidebarOverlay = document.getElementById('sidebarOverlay');

  if (menuToggle) {
    menuToggle.addEventListener('click', () => {
      sidebar?.classList.toggle('open');
      if (sidebarOverlay) {
        sidebarOverlay.style.display = sidebar?.classList.contains('open') ? 'block' : 'none';
      }
    });
  }
  if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', () => {
      sidebar?.classList.remove('open');
      sidebarOverlay.style.display = 'none';
    });
  }

  // ---- Notification dropdown ----
  const notifToggle   = document.getElementById('notifToggle');
  const notifDropdown = document.getElementById('notifDropdown');
  const notifBadge    = document.getElementById('notifBadge');

  if (notifToggle && notifDropdown) {
    notifToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      document.querySelectorAll('.dropdown-menu.open').forEach(m => m.classList.remove('open'));
      notifDropdown.classList.toggle('open');
    });
    document.addEventListener('click', (e) => {
      if (!notifDropdown.contains(e.target) && e.target !== notifToggle) {
        notifDropdown.classList.remove('open');
      }
    });
  }

  // Poll unread count every 30s and update badge
  if (notifBadge) {
    const pollNotifCount = () => {
      fetch(window.APP_URL + '/dashboard/notifications/count', { credentials: 'same-origin' })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
          if (!data) return;
          if (data.count > 0) {
            notifBadge.textContent = data.count > 99 ? '99+' : data.count;
            notifBadge.style.display = '';
          } else {
            notifBadge.style.display = 'none';
          }
        })
        .catch(() => {});
    };
    setInterval(pollNotifCount, 30000);
  }

  // ---- Dropdown menus ----
  document.querySelectorAll('[data-toggle="dropdown"]').forEach(trigger => {
    trigger.addEventListener('click', (e) => {
      e.stopPropagation();
      const menu = trigger.nextElementSibling;
      document.querySelectorAll('.dropdown-menu.open').forEach(m => {
        if (m !== menu) m.classList.remove('open');
      });
      menu?.classList.toggle('open');
    });
  });
  document.addEventListener('click', () => {
    document.querySelectorAll('.dropdown-menu.open').forEach(m => m.classList.remove('open'));
  });

  // ---- Modals ----
  window.openModal = (id) => {
    const m = document.getElementById(id);
    if (m) { m.classList.add('open'); document.body.style.overflow = 'hidden'; }
  };
  window.closeModal = (id) => {
    const m = document.getElementById(id);
    if (m) { m.classList.remove('open'); document.body.style.overflow = ''; }
  };
  document.querySelectorAll('[data-modal]').forEach(btn => {
    btn.addEventListener('click', () => openModal(btn.dataset.modal));
  });
  document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', (e) => {
      if (e.target === backdrop) closeModal(backdrop.id);
    });
  });
  document.querySelectorAll('.modal-close').forEach(btn => {
    btn.addEventListener('click', () => {
      const modal = btn.closest('.modal-backdrop');
      if (modal) closeModal(modal.id);
    });
  });

  // ---- Copy to clipboard ----
  window.copyToClipboard = (text, btn) => {
    navigator.clipboard.writeText(text).then(() => {
      if (btn) {
        const orig = btn.textContent;
        btn.textContent = 'Copied!';
        btn.classList.add('copied');
        setTimeout(() => { btn.textContent = orig; btn.classList.remove('copied'); }, 2000);
      }
    });
  };
  document.querySelectorAll('[data-copy]').forEach(btn => {
    btn.addEventListener('click', () => copyToClipboard(btn.dataset.copy, btn));
  });
  document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const pre = btn.closest('.code-block')?.querySelector('pre');
      if (pre) copyToClipboard(pre.textContent.trim(), btn);
    });
  });
  document.querySelectorAll('.copy-field button').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.closest('.copy-field')?.querySelector('input');
      if (input) copyToClipboard(input.value, btn);
    });
  });

  // ---- Toggle switches ----
  document.querySelectorAll('.toggle-wrap').forEach(wrap => {
    const toggle = wrap.querySelector('.toggle');
    if (!toggle) return;
    wrap.addEventListener('click', () => {
      toggle.classList.toggle('on');
      const hidden = wrap.querySelector('input[type=hidden]');
      if (hidden) hidden.value = toggle.classList.contains('on') ? '1' : '0';
    });
  });

  // ---- Flash message auto-dismiss ----
  document.querySelectorAll('.alert[data-dismiss]').forEach(alert => {
    setTimeout(() => { alert.style.opacity = '0'; setTimeout(() => alert.remove(), 400); },
      parseInt(alert.dataset.dismiss) || 4000);
    alert.style.transition = 'opacity .4s';
  });

  // ---- Tab switching ----
  document.querySelectorAll('[data-tab-target]').forEach(tab => {
    tab.addEventListener('click', () => {
      const group = tab.dataset.tabGroup;
      const target = tab.dataset.tabTarget;
      document.querySelectorAll(`[data-tab-group="${group}"]`).forEach(t => t.classList.remove('active'));
      document.querySelectorAll(`[data-tab-content="${group}"]`).forEach(c => c.style.display = 'none');
      tab.classList.add('active');
      const content = document.querySelector(`[data-tab-id="${target}"]`);
      if (content) content.style.display = '';
    });
  });

  // ---- Confirm dialog ----
  window.confirmAction = (message, onConfirm) => {
    if (confirm(message)) onConfirm();
  };
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', (e) => {
      if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
  });

  // ---- API Key reveal ----
  document.querySelectorAll('[data-reveal]').forEach(btn => {
    btn.addEventListener('click', () => {
      const field = document.getElementById(btn.dataset.reveal);
      if (!field) return;
      if (field.type === 'password') {
        field.type = 'text';
        btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
      } else {
        field.type = 'password';
        btn.innerHTML = '<i class="fas fa-eye"></i>';
      }
    });
  });

  // ---- Number format (KES) ----
  window.formatKES = (n) => 'KES ' + parseFloat(n).toLocaleString('en-KE', { minimumFractionDigits: 2 });

  // ---- Chart placeholder (requires Chart.js) ----
  window.initChart = (canvasId, type, labels, data, color) => {
    const canvas = document.getElementById(canvasId);
    if (!canvas || typeof Chart === 'undefined') return;
    new Chart(canvas, {
      type,
      data: {
        labels,
        datasets: [{
          data,
          backgroundColor: color || '#158347',
          borderColor: color || '#158347',
          borderWidth: 2,
          fill: type === 'line',
          tension: 0.4,
          pointRadius: 3,
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => formatKES(ctx.raw) } } },
        scales: type !== 'pie' ? {
          y: { grid: { color: '#f0f0f0' }, ticks: { callback: v => 'KES ' + v.toLocaleString() } },
          x: { grid: { display: false } }
        } : {}
      }
    });
  };

  // ---- Form validation helpers ----
  window.validatePhone = (phone) => /^(07|01)\d{8}$/.test(phone.replace(/\s/g,''));
  window.validateEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  window.validateAmount = (amount) => !isNaN(amount) && parseFloat(amount) > 0;

  // ---- STK Push polling ----
  window.pollStkStatus = (reference, interval, onDone) => {
    const timer = setInterval(() => {
      fetch('/api/v1/payments/status?ref=' + reference, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(r => r.json())
      .then(data => {
        if (data.status === 'completed' || data.status === 'failed') {
          clearInterval(timer);
          onDone(data);
        }
      })
      .catch(() => clearInterval(timer));
    }, interval || 3000);
    return timer;
  };

  // ---- Active nav link ----
  const path = window.location.pathname;
  document.querySelectorAll('.nav-item').forEach(link => {
    if (link.getAttribute('href') && path.startsWith(link.getAttribute('href'))) {
      link.classList.add('active');
    }
  });

})();
