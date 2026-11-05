const $ = id => document.getElementById(id);

async function apiFetch(url) {
  const res = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
  if (res.status === 401) { window.location.href = '/login?session=expired'; return null; }
  if (!res.ok) { const d = await res.json().catch(() => ({})); throw new Error(d.error || `HTTP ${res.status}`); }
  return res.json();
}

function todayStr() {
  const d = new Date();
  return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
}

async function loadUser() {
  try {
    const user = await apiFetch('/api/me');
    if (!user) return;
    $('navbarUser').textContent = user.name || user.email || '–';
    const initials = (user.name || 'U').split(' ').map(w => w[0]).slice(0,2).join('').toUpperCase();
    $('sidebarAvatar').textContent = initials;
    $('sidebarName').textContent   = user.name || user.email;
  } catch(e) { console.error('[loadUser]', e); }
}

async function fetchDashboard() {
  $('dashError').style.display  = 'none';
  $('dashLoader').style.display = 'flex';
  $('dashGrid').style.display   = 'none';
}

$('sidebarToggle').addEventListener('click', () => {
  if (window.innerWidth <= 768) $('sidebar').classList.toggle('open');
  else document.body.classList.toggle('sidebar-collapsed');
});
document.addEventListener('click', e => {
  if (window.innerWidth > 768) return;
  const sb = $('sidebar');
  if (sb.classList.contains('open') && !sb.contains(e.target) && !$('sidebarToggle').contains(e.target)) sb.classList.remove('open');
});

async function init() {
  loadUser();
  $('inputFecha').value = todayStr();
  await fetchDashboard();
}
init();
