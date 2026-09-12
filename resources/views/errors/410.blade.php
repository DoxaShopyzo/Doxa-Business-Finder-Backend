<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Preview Expired — Doxa Business Finder</title>
  <style>
    body { font-family: system-ui, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; text-align: center; }
    .card { background: #1e293b; border: 1px solid #334155; border-radius: 16px; padding: 40px; max-width: 480px; }
    h1 { color: #f43f5e; margin-bottom: 12px; }
    p { color: #94a3b8; font-size: 14px; line-height: 1.6; }
    a { display: inline-block; background: #38bdf8; color: #0f172a; font-weight: 700; padding: 10px 20px; border-radius: 8px; text-decoration: none; margin-top: 20px; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Preview Link Expired</h1>
    <p>{{ $message ?? 'This personalized preview link has expired per our 90-day data retention policy. Please contact your Doxa representative for an updated live concept demo.' }}</p>
    <a href="tel:+919843787854">📞 Request Fresh Link</a>
  </div>
</body>
</html>
