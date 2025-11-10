<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Login - Project Management</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
	<style>
		:root{--blue:#5aa0ff;--blue-dark:#2b6cb0;--muted:#6b7280}
		html,body{height:100%}
	body{margin:0;font-family:Inter, 'Segoe UI', system-ui, -apple-system, 'Helvetica Neue', Arial;display:flex;align-items:center;justify-content:center;background:#eaf5ff;}

		/* New model: frosted glass centered card with underline inputs */
		.glass-frame{width:100%;max-width:980px;padding:24px}
		.glass-card{display:flex;flex-direction:column;align-items:center;background:rgba(255,255,255,0.6);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);border-radius:16px;padding:36px 36px 28px;box-shadow:0 20px 50px rgba(16,24,40,0.08);border:1px solid rgba(255,255,255,0.6)}
		.glass-badge{width:120px;height:120px;border-radius:999px;background:#fff;display:flex;align-items:center;justify-content:center;margin-top:-80px;box-shadow:0 8px 30px rgba(16,24,40,0.08)}
		.glass-badge img{width:72px;height:72px}
		h1.page-title{font-size:1.25rem;color:var(--blue-dark);margin:10px 0 6px}
		p.page-sub{color:var(--muted);margin-bottom:18px}
		.underline-input{border:0;border-bottom:2px solid rgba(16,24,40,0.06);padding:10px 8px;margin-bottom:14px;outline:none;border-radius:0;background:transparent}
		.underline-input:focus{border-bottom-color:var(--blue);box-shadow:none}
		.input-with-icon{position:relative}
		.input-with-icon i{position:absolute;left:8px;top:50%;transform:translateY(-50%);color:rgba(16,24,40,0.32)}
		.underline-input.icon{padding-left:36px}
		.btn-primary.custom{background:var(--blue);border:none;border-radius:10px;padding:10px 14px;font-weight:700;color:#fff}
		.muted{color:var(--muted);font-size:0.95rem}
		@media (max-width:700px){.glass-card{padding:24px}.glass-badge{width:88px;height:88px;margin-top:-60px}.glass-badge img{width:56px;height:56px}}
		/* Responsive floating panel */
		.floating-panel{display:flex;box-shadow:0 24px 60px rgba(16,24,40,0.08);border-radius:18px;overflow:hidden}
		.panel-left{flex:1;padding:56px;background:#f9fbff;min-width:320px}
		.panel-right{width:40%;background:var(--blue);color:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:36px;gap:20px}
		@media (max-width:900px){
			.floating-panel{flex-direction:column}
			.panel-right{width:100%}
			.panel-left{padding:24px}
		}
	</style>
</head>
<body>

	<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;padding:24px;background:#ffffff;">
		<div style="position:relative;width:100%;max-width:1100px">
			<!-- Decorative flat shapes -->
			<div style="position:absolute;left:-60px;top:-40px;width:220px;height:220px;background:#d8eefc;border-radius:22px;transform:rotate(12deg);z-index:0"></div>
			<div style="position:absolute;right:-80px;bottom:-60px;width:260px;height:260px;background:#e6f4ff;border-radius:28px;z-index:0"></div>

			<!-- Floating panel -->
			<div class="floating-panel" style="position:relative;z-index:1;margin:0 auto;max-width:900px;">
				<div class="panel-left" style="min-width:420px">
					<h2 style="margin-top:0;color:var(--blue-dark)">Welcome back</h2>
					<p class="muted">Sign in to continue to your project dashboard</p>

					@if ($errors->any())
						<div class="alert alert-danger">
							<ul class="mb-0 ps-3">
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif

					<form method="POST" action="{{ url('/login') }}" style="margin-top:18px;max-width:520px">
						@csrf
						<div class="mb-3">
							<label class="form-label">Email</label>
							<input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus style="border-radius:8px;min-height:48px;padding:12px 14px">
						</div>
						<div class="mb-3">
							<label class="form-label">Password</label>
							<input type="password" name="password" class="form-control" required style="border-radius:8px;min-height:48px;padding:12px 14px">
						</div>
						<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
							<div class="form-check">
								<input class="form-check-input" type="checkbox" name="remember" id="remember3">
								<label class="form-check-label muted" for="remember3">Remember me</label>
							</div>
							<a href="{{ route('password.request') }}" class="muted">Forgot password?</a>
						</div>
						<div style="display:flex;gap:12px;align-items:center">
							<button class="btn btn-primary custom" type="submit" style="padding:12px 20px">Sign in</button>
							<div style="color:#9aa3b2">or</div>
							<a class="btn btn-outline-secondary" href="{{ route('auth.google.redirect') }}" style="border-radius:8px;padding:8px 12px"><i class="fab fa-google"></i> Sign in with Google</a>
						</div>
                    
					<div style="margin-top:18px;max-width:520px;display:flex;justify-content:space-between;align-items:center">
						<div class="muted">Don't have an account?</div>
						<a href="{{ route('register') }}" class="btn btn-link">Sign up</a>
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

