<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Página não encontrada — KwanzaSafe</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:#f0fdf4;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem}
.card{background:white;border-radius:1.5rem;border:1px solid #e2e8f0;box-shadow:0 4px 32px rgba(6,78,59,0.08);padding:3rem 2.5rem;max-width:440px;width:100%;text-align:center}
.code{font-family:'Syne',sans-serif;font-size:5rem;font-weight:800;color:#009d44;line-height:1;margin-bottom:0.5rem}
.title{font-family:'Syne',sans-serif;font-size:1.375rem;font-weight:800;color:#0f172a;margin-bottom:0.75rem}
.sub{font-size:0.9rem;color:#64748b;line-height:1.6;margin-bottom:2rem}
.btn{display:inline-flex;align-items:center;gap:0.5rem;background:#064e3b;color:white;text-decoration:none;padding:0.875rem 1.75rem;border-radius:12px;font-family:'Syne',sans-serif;font-weight:800;font-size:0.875rem;letter-spacing:0.04em;transition:background 0.18s}
.btn:hover{background:#065f46}
</style>
</head>
<body>
<div class="card">
    <div class="code">404</div>
    <div class="title">Página não encontrada</div>
    <div class="sub">A página que procuras não existe ou foi movida. Verifica o endereço ou volta ao início.</div>
    <a href="{{ url('/') }}" class="btn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Voltar ao início
    </a>
</div>
</body>
</html>
