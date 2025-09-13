<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        line-height: 1.6;
        color: #1f2937;
        background-color: #f9fafb;
        padding: 20px;
    }
    
    .container {
        max-width: 680px;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .header {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: #ffffff;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    
    .header-content h1 {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 4px;
    }
    
    .meta {
        font-size: 14px;
        opacity: 0.9;
        color: #e2e8f0;
    }
    
    .content {
        padding: 24px;
    }
    
    .section {
        margin-bottom: 24px;
    }
    
    .label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        margin-bottom: 8px;
        color: #6b7280;
    }
    
    .label.danger {
        color: #dc2626;
    }
    
    .error-box {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 16px;
        border-left: 4px solid #dc2626;
    }
    
    .error-class {
        font-weight: 600;
        color: #dc2626;
        font-size: 16px;
        margin-bottom: 8px;
    }
    
    .error-message {
        color: #374151;
        font-size: 14px;
    }
    
    .suggestion-box {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 16px;
        border-left: 4px solid #2563eb;
        margin-top: 16px;
    }
    
    .suggestion-title {
        font-weight: 600;
        color: #2563eb;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .suggestion-text {
        color: #374151;
        font-size: 14px;
    }
    
    .file-info {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 16px;
        font-family: 'Monaco', 'Menlo', monospace;
        font-size: 13px;
        color: #475569;
    }
    
    .stack-container {
        background: #1e293b;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .stack-header {
        background: #334155;
        padding: 12px 16px;
        border-bottom: 1px solid #475569;
    }
    
    .stack-title {
        color: #e2e8f0;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    
    .stack-trace {
        background: #1e293b;
        color: #e2e8f0;
        padding: 16px;
        font-family: 'Monaco', 'Menlo', monospace;
        font-size: 12px;
        line-height: 1.5;
        max-height: 200px;
        overflow-y: auto;
        white-space: pre-wrap;
        word-break: break-word;
    }

  /* Syntax highlight helpers */
  .kw { color: #f59e0b; font-weight:600; }
  .str { color: #f97316; }
  .num { color: #34d399; }
  .code-line { display:flex; }
  .line-number { background: #f8fafc; color: #94a3b8; padding: 4px 12px; text-align: right; min-width: 50px; border-right: 1px solid #e2e8f0; user-select: none; }
  .line-content { flex:1; padding:4px 12px; font-family: 'Monaco', 'Menlo', monospace; font-size:13px; white-space:pre-wrap; }
  .line-error { background: #fef2f2; border-left: 4px solid #dc2626; }
  .line-error .line-number { background: #fecaca; color: #991b1b; font-weight: 600; }
  .line-error .line-content { background: #fef2f2; color: #991b1b; }
    
    .footer {
        background: #f8fafc;
        padding: 16px 24px;
        border-top: 1px solid #e2e8f0;
        text-align: center;
        font-size: 12px;
        color: #6b7280;
    }
    
    .timestamp {
        color: #9ca3af;
    }
    
    @media (max-width: 640px) {
        body { padding: 10px; }
        .header { padding: 16px; flex-direction: column; text-align: center; }
        .content { padding: 16px; }
        .section { margin-bottom: 20px; }
    }
</style>
</head>
<body>
  <div class="container">
    <div class="header">
      <!--<div class="logo">EVA</div>-->
      <div class="header-content">
        <h1>{{ $eva_payload['title'] ?? '[EVA] Alerta' }}</h1>
        <div class="meta">Módulo: {{ $eva_payload['module'] ?? 'N/A' }} • Arquivo: {{ basename($eva_payload['file'] ?? '') }}:{{ $eva_payload['line'] ?? '?' }}</div>
      </div>
    </div>

    <div class="content">
      <div class="section">
        <div class="label danger">Erro / Exceção</div>
        <div class="error-box">
          <div class="error-class">{{ $eva_payload['class'] ?? '' }}</div>
          <div class="error-message">{{ $eva_payload['message'] ?? '' }}</div>
        </div>

        @if(!empty($eva_payload['suggestion']))
        <div class="suggestion-box">
          <div class="suggestion-title">💡 Sugestão Automática do Sistema</div>
          <div class="suggestion-text">{{ $eva_payload['suggestion'] }}</div>
        </div>
        @endif
      </div>

      <div class="section">
        <div class="label">Arquivo</div>
        <div class="file-info">{{ basename($eva_payload['file'] ?? '') }}:{{ $eva_payload['line'] ?? '?' }}</div>
      </div>

      @if(!empty($eva_payload['stack']))
      <div class="section">
        <div class="label">Trecho do Código</div>
        <div class="code-container">
          <div class="code-header">Contexto ao redor da linha {{ $eva_payload['line'] ?? '?' }}</div>
          <div class="code-content">
            @php
              $errorLine = isset($eva_payload['line']) ? (int) $eva_payload['line'] : null;
              $rawCode = null;
              if (!empty($eva_payload['code']) && is_string($eva_payload['code'])) {
                  $rawCode = $eva_payload['code'];
              } elseif (!empty($eva_payload['file']) && file_exists($eva_payload['file'])) {
                  // tentar ler o arquivo do filesystem (executado no servidor)
                  try {
                      $rawCode = implode("\n", file($eva_payload['file']));
                  } catch (\Throwable $ex) {
                      $rawCode = null;
                  }
              }

              $codeLines = [];
              if ($rawCode !== null) {
                  $lines = preg_split('/\r\n|\n|\r/', $rawCode);
                  $total = count($lines);
                  if ($errorLine === null) {
                      $start = max(1, 1);
                  } else {
                      $start = max(1, $errorLine - 5);
                  }
                  $end = min($total, ($errorLine ?: 1) + 4);
                  for ($i = $start; $i <= $end; $i++) {
                      $codeLines[$i] = $lines[$i-1] ?? '';
                  }
              }
            @endphp

            @if(!empty($codeLines))
              @foreach($codeLines as $ln => $content)
                @php $isErrorLine = ($errorLine !== null && $ln == $errorLine); @endphp
                <div class="code-line {{ $isErrorLine ? 'line-error' : '' }}">
                  <div class="line-number">{{ $ln }}</div>
                  <div class="line-content">
                    @php
                      $text = rtrim($content, "\n\r");
                      // escape
                      $escaped = e($text);
                      // simple highlighting: strings, numbers and keywords
                      $patterns = [
                        // strings '...' or "..."
                        '/(&#039;.*?&#039;|&quot;.*?&quot;)/i',
                        // numbers
                        '/\b(\d+)\b/',
                        // keywords (php/js common)
                        '/\b(function|return|if|else|foreach|for|while|switch|case|break|class|new|throw|try|catch|public|protected|private|static|const|var|let|echo)\b/i'
                      ];
                      $replacements = [
                        '<span class="str">$1</span>',
                        '<span class="num">$1</span>',
                        '<span class="kw">$1</span>'
                      ];
                      $highlighted = preg_replace($patterns, $replacements, $escaped);
                    @endphp
                    {!! $highlighted !!}
                  </div>
                </div>
              @endforeach
            @else
              <pre class="stack-trace">{{ $eva_payload['stack'] ?? 'Código não disponível' }}</pre>
            @endif
          </div>
        </div>
      </div>
      @endif

      <div class="footer">
        Este é um alerta automático gerado pelo EVA — <span class="timestamp">{{ date('H:i:s d/m/Y') }}</span>
      </div>
    </div>
  </div>
</body>
</html>