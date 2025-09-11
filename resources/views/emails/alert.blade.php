<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
/* Tipografia e espaçamento melhorados para leitura em clientes de e-mail */
body { font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif; margin:0; background:#f4f6f8; color:#111827; -webkit-font-smoothing:antialiased; }
.wrap { max-width:760px; margin:32px auto; padding:28px 28px; background:#ffffff; border-radius:14px; box-shadow: 0 8px 30px rgba(31,41,55,0.06); border:1px solid rgba(31,41,55,0.04); }
.header { display:flex; align-items:center; gap:16px; }
.logo { width:56px; height:56px; background:linear-gradient(135deg,#4f46e5,#06b6d4); border-radius:12px; display:flex; align-items:center; justify-content:center; color:white; font-weight:700; font-size:18px; }
h1 { font-size:20px; margin:0; line-height:1.2; }
.meta { color:#6b7280; font-size:13px; margin-top:6px; }
.section { margin-top:20px; padding:16px; background:#fbfdff; border-radius:10px; border:1px solid rgba(59,130,246,0.06); }
.label { font-size:13px; color:#6b7280; margin-bottom:8px; }
.value { font-size:15px; color:#0f172a; word-break:break-word; line-height:1.45; }
.error { margin-top:14px; background:linear-gradient(90deg, rgba(254,226,226,0.28), rgba(255,250,235,0.16)); padding:14px; border-radius:10px; border:1px solid rgba(239,68,68,0.08); }
pre.stack { white-space:pre-wrap; font-size:13px; color:#374151; max-height:320px; overflow:auto; margin:10px 0 0 0; background:#fff; padding:14px; border-radius:8px; border:1px solid #eef2ff; }
.suggestion { margin-top:14px; padding:14px; border-left:4px solid #06b6d4; background:#f0f9ff; border-radius:8px; color:#065f46; font-size:14px; }
.footer { margin-top:20px; font-size:13px; color:#6b7280; text-align:center; }

/* Ajustes responsivos simples para mobile */
@media only screen and (max-width:480px) {
  .wrap { margin:18px 12px; padding:18px; }
  .logo { width:48px; height:48px; font-size:16px; }
  h1 { font-size:18px; }
  .value { font-size:15px; }
  pre.stack { font-size:12px; }
}
</style>
</head>
<body>
  <div class="wrap">
    <div class="header">
      <div class="logo">EVA</div>
      <div>
        <h1>{{ $eva_payload['title'] ?? '[EVA] Alerta' }}</h1>
        <div class="meta">Módulo: {{ $eva_payload['module'] ?? 'N/A' }} • Arquivo: {{ basename($eva_payload['file'] ?? '') }}:{{ $eva_payload['line'] ?? '?' }}</div>
      </div>
    </div>

    <div class="section">
      <div class="label">Erro / Exceção</div>
      <div class="value error">
        <strong>{{ $eva_payload['class'] ?? '' }}</strong><br>
        {{ $eva_payload['message'] ?? '' }}
      </div>

      @if(!empty($eva_payload['suggestion']))
      <div class="suggestion">
        <strong>Sugestão automática:</strong> {{ $eva_payload['suggestion'] }}
      </div>
      @endif

      @if(!empty($eva_payload['stack']))
      <div class="label" style="margin-top:12px">Stack trace</div>
      <pre class="stack">{{ $eva_payload['stack'] }}</pre>
      @endif
    </div>

    <div class="footer">
      Este é um alerta automático gerado pelo EVA — {{ date('H:i:s d/m/Y') }}
    </div>
  </div>
</body>
</html>