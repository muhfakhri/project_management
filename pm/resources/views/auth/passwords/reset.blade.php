<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Set a new password - Project Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        :root{--blue:#5aa0ff;--blue-dark:#2b6cb0;--muted:#6b7280}
        html,body{height:100%}
        body{margin:0;font-family:Inter, 'Segoe UI', system-ui, -apple-system, 'Helvetica Neue', Arial;display:flex;align-items:center;justify-content:center;background:#eaf5ff}
        .floating-panel{display:flex;box-shadow:0 24px 60px rgba(16,24,40,0.08);border-radius:18px;overflow:hidden;max-width:900px}
        .panel-left{flex:1;padding:56px;background:#f9fbff;min-width:320px}
        .panel-right{width:40%;background:var(--blue);color:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:36px;gap:20px}
        .muted{color:var(--muted)}
        @media (max-width:900px){.floating-panel{flex-direction:column}.panel-right{width:100%}.panel-left{padding:24px}}
    </style>
</head>
<body>
    <div style="min-height:80vh;display:flex;align-items:center;justify-content:center;padding:24px;background:#ffffff;">
        <div style="position:relative;width:100%;max-width:1100px">
            <div style="position:absolute;left:-60px;top:-40px;width:220px;height:220px;background:#d8eefc;border-radius:22px;transform:rotate(12deg);z-index:0"></div>
            <div style="position:absolute;right:-80px;bottom:-60px;width:260px;height:260px;background:#e6f4ff;border-radius:28px;z-index:0"></div>

            <div class="floating-panel" style="position:relative;z-index:1;margin:0 auto;">
                <div class="panel-left">
                    <h2 style="margin-top:0;color:var(--blue-dark)">Set a new password</h2>
                    <p class="muted">Choose a secure new password for your account.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}" style="margin-top:18px;max-width:520px">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $email ?? old('email') }}" required style="border-radius:8px;min-height:48px;padding:12px 14px">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New password</label>
                            <input type="password" name="password" class="form-control" required style="border-radius:8px;min-height:48px;padding:12px 14px">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm new password</label>
                            <input type="password" name="password_confirmation" class="form-control" required style="border-radius:8px;min-height:48px;padding:12px 14px">
                        </div>

                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <a href="{{ route('login') }}" class="muted">Back to login</a>
                            <button class="btn btn-primary custom" type="submit">Reset password</button>
                        </div>
                    </form>
                </div>

                <div class="panel-right">
                    <h3 style="margin:0">Project Management</h3>
                    <p style="max-width:220px;text-align:center;opacity:0.95">Manage team work, tasks and time in one secure place.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
