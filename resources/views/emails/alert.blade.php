<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body { font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial; margin:0; background:#f4f6f8; color:#1f2937; }
.wrap { max-width:720px; margin:28px auto; padding:24px; background:#ffffff; border-radius:12px; box-shadow: 0 6px 20px rgba(31,41,55,0.06); border:1px solid rgba(31,41,55,0.04); }
.header { display:flex; align-items:center; gap:12px; }
.logo { width:50px; height:50px; background:linear-gradient(135deg,#4f46e5,#06b6d4); border-radius:10px; display:flex; align-items:center; justify-content:center; color:white; font-weight:700; }
h1 { font-size:18px; margin:0; }
.meta { color:#6b7280; font-size:13px; margin-top:6px; }
.section { margin-top:18px; padding:14px; background:#fbfdff; border-radius:8px; border:1px solid rgba(59,130,246,0.06); }
.label { font-size:12px; color:#6b7280; margin-bottom:6px; }
.value { font-size:14px; color:#111827; word-break:break-all; }
.error { margin-top:14px; background:linear-gradient(90deg, rgba(254,226,226,0.3), rgba(255,250,235,0.2)); padding:12px; border-radius:8px; border:1px solid rgba(239,68,68,0.08); }
pre.stack { white-space:pre-wrap; font-size:12px; color:#374151; max-height:220px; overflow:auto; margin:8px 0 0 0; background:#fff; padding:12px; border-radius:6px; border:1px solid #eef2ff; }
.suggestion { margin-top:12px; padding:12px; border-left:4px solid #06b6d4; background:#f0f9ff; border-radius:6px; color:#064e3b; font-size:13px; }
.footer { margin-top:18px; font-size:12px; color:#6b7280; text-align:center; }
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
      Este é um alerta automático gerado pelo EVA — {{ date('Y-m-d H:i:s') }}
    </div>
  </div>
</body>
</html>