<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>404 · Página no encontrada</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'DM Sans', sans-serif;
      background: #ffffff;
      color: #111827;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }
    .container {
      text-align: center;
      max-width: 480px;
      width: 100%;
    }
    .img-404 {
      width: 260px;
      height: 260px;
      object-fit: contain;
      margin: 0 auto 2rem;
      display: block;
    }
    .code {
      font-family: 'Syne', sans-serif;
      font-size: 5rem;
      font-weight: 700;
      color: #e5e7eb;
      line-height: 1;
      margin-bottom: .5rem;
      letter-spacing: -.04em;
    }
    h1 {
      font-family: 'Syne', sans-serif;
      font-size: 1.4rem;
      font-weight: 700;
      color: #111827;
      margin-bottom: .6rem;
    }
    p {
      font-size: .95rem;
      color: #6b7280;
      line-height: 1.6;
      margin-bottom: 2rem;
    }
    a.btn {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      background: #111827;
      color: #fff;
      text-decoration: none;
      padding: .7rem 1.6rem;
      border-radius: 10px;
      font-size: .9rem;
      font-weight: 500;
      transition: background .2s, transform .1s;
    }
    a.btn:hover { background: #374151; }
    a.btn:active { transform: scale(.97); }
  </style>
</head>
<body>
  <div class="container">

    <img
      class="img-404"
      src="{{ asset('/assets/img/logo.jpg') }}"
      alt="Página no encontrada"
      onerror="this.style.display='none'"
    />

    <div class="code">404</div>
    <h1>Página no encontrada</h1>
    <p>Lo sentimos, la página que buscas no existe<br/>o fue movida a otra dirección.</p>

    <a href="{{ route('dashboard') }}" class="btn">
      <svg width="15" height="15" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M10 3H3v14h14v-7M10 10L17 3M12 3h5v5"/>
      </svg>
      Volver al inicio
    </a>
  </div>
</body>
</html>
