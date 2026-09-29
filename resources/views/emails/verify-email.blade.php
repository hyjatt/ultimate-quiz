<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm your F1Quiz race licence</title>
</head>
<body style="margin:0;background:#070a10;color:#e5e7eb;font-family:Arial,Helvetica,sans-serif;">
    <div style="padding:32px 16px;background:#070a10;background-image:linear-gradient(rgba(255,255,255,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.035) 1px,transparent 1px);background-size:28px 28px;">
        <div style="max-width:600px;margin:0 auto;background:#11151c;border:1px solid rgba(255,255,255,.12);">
            <div style="padding:24px 28px;background:linear-gradient(110deg,#ff2b2b,#a91224);">
                <div style="font-size:12px;font-weight:800;letter-spacing:3px;color:#fecaca;">F1QUIZ // RACE CONTROL</div>
                <div style="margin-top:8px;font-size:30px;font-weight:900;font-style:italic;letter-spacing:-1px;color:#fff;">CONFIRM YOUR LICENCE</div>
            </div>
            <div style="padding:32px 28px;">
                <p style="margin:0 0 8px;color:#94a3b8;font-size:12px;font-weight:700;letter-spacing:2px;text-transform:uppercase;">Driver verification</p>
                <h1 style="margin:0 0 20px;color:#fff;font-size:24px;">Welcome to the grid, {{ $username }}.</h1>
                <p style="margin:0 0 24px;color:#cbd5e1;font-size:16px;line-height:1.6;">Confirm your email address to activate your race licence and enter the F1Quiz championship.</p>
                <a href="{{ $url }}" style="display:inline-block;padding:14px 22px;background:#ff2b2b;color:#fff;font-size:13px;font-weight:800;letter-spacing:1px;text-decoration:none;text-transform:uppercase;">Verify email address</a>
                <p style="margin:28px 0 0;padding-top:20px;border-top:1px solid rgba(255,255,255,.1);color:#64748b;font-size:13px;line-height:1.6;">This verification link expires in 60 minutes. If you did not create an F1Quiz account, you can safely ignore this message.</p>
            </div>
            <div style="padding:18px 28px;border-top:1px solid rgba(255,255,255,.08);color:#64748b;font-size:11px;letter-spacing:1px;text-transform:uppercase;">Earn your place. Own the grid.</div>
        </div>
    </div>
</body>
</html>
