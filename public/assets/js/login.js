(() => {
  const form     = document.getElementById('loginForm');
  const btn      = document.getElementById('btnLogin');
  const alertBox = document.getElementById('alert');
  const passInput = document.getElementById('password');
  const toggleBtn = document.querySelector('.toggle-pass');
  const eyeOpen   = document.getElementById('eyeOpen');
  const eyeClosed = document.getElementById('eyeClosed');

  // ── Toggle visibilidad contraseña ─────────────────────
  toggleBtn.addEventListener('click', () => {
    const show = passInput.type === 'password';
    passInput.type = show ? 'text' : 'password';
    eyeOpen.style.display  = show ? 'none'  : 'block';
    eyeClosed.style.display = show ? 'block' : 'none';
  });

  // ── Mostrar alerta ────────────────────────────────────
  function showAlert(msg, type = 'error') {
    alertBox.textContent = msg;
    alertBox.className = `alert ${type}`;
    alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function clearAlert() {
    alertBox.className = 'alert';
    alertBox.textContent = '';
  }

  // ── Submit ────────────────────────────────────────────
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearAlert();

    const email    = document.getElementById('email').value.trim();
    const password = passInput.value;

    if (!email || !password) {
      showAlert('Por favor completa todos los campos.');
      return;
    }

    // Estado de carga
    btn.disabled = true;
    btn.classList.add('loading');

    try {
      const res  = await fetch('/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
      });

      const data = await res.json();

      if (data.success) {
        showAlert('¡Acceso correcto! Redirigiendo…', 'success');
        setTimeout(() => { window.location.href = data.redirect || '/dashboard'; }, 600);
      } else {
        showAlert(data.message || 'Credenciales incorrectas.');
        btn.disabled = false;
        btn.classList.remove('loading');
      }
    } catch {
      showAlert('Error de conexión. Intenta de nuevo.');
      btn.disabled = false;
      btn.classList.remove('loading');
    }
  });
})();
