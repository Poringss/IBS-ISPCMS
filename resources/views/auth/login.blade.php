@extends('layouts.guest')

@section('content')

<div style="display:flex; flex-direction:column; justify-content:center; align-items:center; min-height:100vh; width:100%;">

    {{-- LANDING SECTION --}}
    <div id="landing-section" style="text-align:center; max-width:800px; padding:20px; transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);">
        <h1 style="font-size:3.5rem; font-weight:800; margin-bottom:10px; letter-spacing:-1px; background:linear-gradient(135deg, #22c55e, #4ade80); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">
            InsightBlitz
        </h1>
        <p style="font-size:1.15rem; color:rgba(255,255,255,0.55); margin-bottom:40px; max-width:500px; margin-left:auto; margin-right:auto; line-height:1.7;">
            Manage your organizational assets with a modern and fluid experience.
        </p>
        <div style="display:flex; justify-content:center; gap:20px;">
            <button onclick="showLogin()" class="btn" style="padding:14px 38px; border-radius:12px; font-weight:700; background:linear-gradient(135deg,#22c55e,#16a34a); color:black; border:none; transition:all 0.3s; box-shadow:0 4px 20px rgba(34,197,94,0.3); font-size:0.95rem;">
                Get Started
            </button>
            <button onclick="toggleModal(true)" class="btn" style="padding:14px 38px; border-radius:12px; font-weight:600; background:rgba(255,255,255,0.05); color:rgba(255,255,255,0.8); border:1px solid rgba(255,255,255,0.12); backdrop-filter:blur(8px); transition:all 0.3s; font-size:0.95rem;">
                Learn More
            </button>
        </div>
    </div>

    {{-- LOGIN SECTION --}}
    <div id="login-section" style="display:none; opacity:0; transform:scale(0.95) translateY(20px); transition: opacity 0.8s ease, transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);">
        <div style="position:relative; padding:2px; border-radius:20px; overflow:hidden; display:inline-block;">
            {{-- Shine Border --}}
            <div style="position:absolute; top:-50%; left:-50%; width:200%; height:200%; background:conic-gradient(from 0deg, transparent 20%, #22c55e 25%, #4ade80 50%, #22c55e 75%, transparent 80%); animation:rotate-shine 4s linear infinite; z-index:0;"></div>

            <div style="position:relative; z-index:2; background:rgba(6,6,12,0.92); backdrop-filter:blur(20px); padding:35px 42px; border-radius:19px; width:400px; text-align:center;">

                {{-- LOGO --}}
                <div id="parallax-trigger" style="width:100%; display:flex; flex-direction:column; justify-content:center; align-items:center; margin-bottom:8px; perspective:1000px;">
                    <img src="{{ asset('pictures/ibslogo.jpg') }}" id="parallax-logo" style="width:200px; height:auto; filter:drop-shadow(0 0 25px rgba(34,197,94,0.35)); transition:transform 0.5s cubic-bezier(0.23,1,0.32,1), filter 0.3s ease; cursor:pointer; transform-style:preserve-3d; border-radius:12px;">
                    <div style="margin-top:8px; font-size:1.5rem; font-weight:800; background:linear-gradient(135deg,#22c55e,#4ade80); -webkit-background-clip:text; -webkit-text-fill-color:transparent; letter-spacing:2px; text-transform:uppercase;">
                        IBS SYSTEM
                    </div>
                </div>

                <span style="color:rgba(255,255,255,0.4); font-size:0.85rem; display:block; margin-bottom:28px;">
                    {{ (($context ?? 'client') === 'admin') ? 'Admin Login' : 'User Login' }}
                </span>

                @if (session('success'))
                    <script>Swal.fire({ icon:'success', title:'Success', text:'{{ session('success') }}', confirmButtonColor:'#22c55e' });</script>
                @endif
                @if ($errors->any())
                    <script>Swal.fire({ icon:'error', title:'Login Failed', text:'{{ $errors->first() }}', confirmButtonColor:'#22c55e' });</script>
                @endif

                {{-- FORM --}}
                <form method="POST" action="{{ route('login.attempt') }}" onsubmit="return handleLoginSubmit(this)">
                    @csrf
                    <input type="hidden" name="context" value="{{ $context ?? 'client' }}"/>

                    {{-- EMAIL --}}
                    <div style="position:relative; margin-bottom:22px; text-align:left;">
                        <i class="fa-solid fa-user" style="position:absolute; left:14px; top:14px; color:#22c55e; z-index:5; font-size:0.85rem;"></i>
                        <input name="email" type="email" value="{{ old('email') }}" required placeholder=" "
                               style="width:100%; padding:13px 13px 13px 42px; border:1px solid rgba(255,255,255,0.08); background:rgba(255,255,255,0.04); border-radius:10px; outline:none; box-sizing:border-box; color:#fff; font-size:0.92rem; transition:all 0.3s;"
                               onfocus="this.style.borderColor='#22c55e'; this.style.background='rgba(255,255,255,0.07)'; this.style.boxShadow='0 0 0 3px rgba(34,197,94,0.12)'"
                               onblur="this.style.borderColor='rgba(255,255,255,0.08)'; this.style.background='rgba(255,255,255,0.04)'; this.style.boxShadow='none'">
                        <label style="position:absolute; left:42px; top:13px; color:rgba(255,255,255,0.4); pointer-events:none; transition:all 0.3s ease; font-size:0.9rem;" class="floating-label">Email</label>
                    </div>

                    {{-- PASSWORD --}}
                    <div style="position:relative; margin-bottom:22px; text-align:left;">
                        <i class="fa-solid fa-lock" style="position:absolute; left:14px; top:14px; color:#22c55e; z-index:5; font-size:0.85rem;"></i>
                        <input name="password" type="password" id="password" required placeholder=" "
                               onkeyup="checkCaps(event)"
                               style="width:100%; padding:13px 13px 13px 42px; border:1px solid rgba(255,255,255,0.08); background:rgba(255,255,255,0.04); border-radius:10px; outline:none; box-sizing:border-box; color:#fff; font-size:0.92rem; transition:all 0.3s;"
                               onfocus="this.style.borderColor='#22c55e'; this.style.background='rgba(255,255,255,0.07)'; this.style.boxShadow='0 0 0 3px rgba(34,197,94,0.12)'"
                               onblur="this.style.borderColor='rgba(255,255,255,0.08)'; this.style.background='rgba(255,255,255,0.04)'; this.style.boxShadow='none'">
                        <label style="position:absolute; left:42px; top:13px; color:rgba(255,255,255,0.4); pointer-events:none; transition:all 0.3s ease; font-size:0.9rem;" class="floating-label">Password</label>
                        <span id="caps-warning" style="position:absolute; right:42px; top:14px; color:#4ade80; font-size:0.75rem; display:none;">Caps ON</span>
                        <i class="fa-solid fa-eye toggle-password" id="eye-icon" onclick="togglePass()" style="position:absolute; right:14px; top:14px; color:rgba(255,255,255,0.25); cursor:pointer; transition:0.3s; font-size:0.85rem;"></i>
                    </div>

                    {{-- REMEMBER --}}
                    <div style="text-align:left; margin-bottom:18px;">
                        <label style="font-size:0.82rem; color:rgba(255,255,255,0.45); cursor:pointer;">
                            <input type="checkbox" name="remember" style="accent-color:#22c55e; margin-right:6px;"> Remember me
                        </label>
                    </div>

                    <button type="submit" id="submit-btn" style="position:relative; overflow:hidden; width:100%; padding:13px; background:linear-gradient(135deg,#22c55e,#16a34a); color:black; border:none; border-radius:10px; font-weight:700; cursor:pointer; transition:all 0.3s; font-size:0.92rem; box-shadow:0 4px 20px rgba(34,197,94,0.25);">
                        <span id="btn-text">Sign In</span>
                    </button>
                </form>

                {{-- LINKS --}}
                <div style="margin-top:22px; font-size:0.8rem;">
                    @if(($context ?? 'client') === 'admin')
                        <a href="{{ route('login') }}" style="color:rgba(255,255,255,0.45); text-decoration:none; transition:color 0.2s;">Back to client login</a>
                    @else
                        <div>
                            <a href="{{ route('register.show') }}" style="color:rgba(255,255,255,0.45); text-decoration:none;">
                                Don't have an account? <span style="color:#22c55e; font-weight:600;">Create one</span>
                            </a>
                        </div>
                        <div style="margin-top:6px;">
                            <a href="{{ route('admin.login') }}" style="color:rgba(255,255,255,0.45); text-decoration:none;">
                                Admin? <span style="color:#22c55e; font-weight:600;">Sign in here</span>
                            </a>
                        </div>
                    @endif
                </div>

                <p style="font-size:0.68rem; margin-top:22px; color:rgba(255,255,255,0.25);">
                    <i class="fa-solid fa-shield-halved" style="color:rgba(34,197,94,0.5);"></i> Authorized Personnel Only
                </p>
            </div>
        </div>
    </div>
</div>

{{-- LEARN MORE MODAL --}}
<div id="learn-more-modal" onclick="closeModal(event)" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); backdrop-filter:blur(12px); display:flex; justify-content:center; align-items:center; z-index:1000; opacity:0; visibility:hidden; pointer-events:none; transition:opacity 0.5s ease, visibility 0.5s ease;">
    <div style="background:rgba(6,6,12,0.95); border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:40px; width:90%; max-width:620px; text-align:center; box-shadow:0 25px 60px rgba(0,0,0,0.6); backdrop-filter:blur(20px); max-height:90vh; overflow-y:auto; transform:scale(0.8) translateY(30px); transition:transform 0.5s cubic-bezier(0.34,1.56,0.64,1);" class="modal-card-inner">

        <div style="text-align:left; margin-bottom:28px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:24px;">
            <h3 style="background:linear-gradient(135deg,#22c55e,#4ade80); -webkit-background-clip:text; -webkit-text-fill-color:transparent; margin-bottom:14px; font-size:1.3rem; font-weight:800;">About InsightBlitz</h3>
            <p style="color:rgba(255,255,255,0.65); line-height:1.7; font-size:0.92rem;">
              Insightblitz is a full-stack Web3 go-to-market partner focused on one thing: execution that drives real adoption.
            </p>
        </div>

        <h4 style="color:rgba(255,255,255,0.5); font-size:0.85rem; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:16px;">The Team</h4>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; text-align:left;">
            <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.05); padding:11px 14px; border-radius:10px; color:rgba(255,255,255,0.8); font-size:0.88rem; display:flex; align-items:center; gap:10px; transition:0.3s;">
                <i class="fa-solid fa-code" style="color:#22c55e; font-size:0.75rem;"></i> Gavter Dausen
            </div>
            <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.05); padding:11px 14px; border-radius:10px; color:rgba(255,255,255,0.8); font-size:0.88rem; display:flex; align-items:center; gap:10px;">
                <i class="fa-solid fa-code" style="color:#22c55e; font-size:0.75rem;"></i> Mike Lazarito
            </div>
            <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.05); padding:11px 14px; border-radius:10px; color:rgba(255,255,255,0.8); font-size:0.88rem; display:flex; align-items:center; gap:10px;">
                <i class="fa-solid fa-code" style="color:#22c55e; font-size:0.75rem;"></i> Jp Lamasan
            </div>
        </div>

        <button onclick="toggleModal(false)" style="margin-top:30px; width:100%; background:rgba(255,255,255,0.06); color:rgba(255,255,255,0.7); padding:13px; border-radius:10px; font-weight:600; border:1px solid rgba(255,255,255,0.1); cursor:pointer; transition:0.3s; font-size:0.9rem;">
            Dismiss
        </button>
    </div>
</div>

@endsection

@push('styles')
<style>
    @keyframes rotate-shine { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    input:focus ~ .floating-label,
    input:not(:placeholder-shown) ~ .floating-label {
        top: -20px !important;
        left: 10px !important;
        font-size: 0.75rem !important;
        color: #22c55e !important;
        font-weight: 600 !important;
    }

    .fade-out { opacity: 0; transform: translateY(-30px); pointer-events: none; transition: all 0.5s ease; }
</style>
@endpush

@push('scripts')
<script>
    // Parallax
    const parallaxTrigger = document.getElementById('parallax-trigger');
    const logo = document.getElementById('parallax-logo');
    if (parallaxTrigger && logo) {
        parallaxTrigger.addEventListener('mousemove', (e) => {
            const rect = logo.getBoundingClientRect();
            const tiltX = (rect.top + rect.height/2 - e.clientY) / 10;
            const tiltY = (e.clientX - rect.left - rect.width/2) / 10;
            logo.style.transform = `rotateX(${tiltX}deg) rotateY(${tiltY}deg) scale(1.08)`;
        });
        parallaxTrigger.addEventListener('mouseleave', () => {
            logo.style.transform = 'rotateX(0deg) rotateY(0deg) scale(1)';
        });
    }

    function showLogin() {
        const landing = document.getElementById('landing-section');
        const login = document.getElementById('login-section');
        landing.classList.add('fade-out');
        setTimeout(() => {
            landing.style.display = 'none';
            login.style.display = 'block';
            login.offsetHeight;
            login.style.opacity = '1';
            login.style.transform = 'scale(1) translateY(0)';
        }, 400);
    }

    function togglePass() {
        const p = document.getElementById('password');
        const icon = document.getElementById('eye-icon');
        if (p.type === 'password') { p.type = 'text'; icon.classList.replace('fa-eye','fa-eye-slash'); }
        else { p.type = 'password'; icon.classList.replace('fa-eye-slash','fa-eye'); }
    }

    function checkCaps(e) {
        const w = document.getElementById('caps-warning');
        w.style.display = e.getModifierState('CapsLock') ? 'block' : 'none';
    }

    function handleLoginSubmit(form) {
        const btn = document.getElementById('submit-btn');
        const txt = document.getElementById('btn-text');
        btn.disabled = true;
        txt.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Authenticating...';
        return true;
    }

    function toggleModal(show) {
        const m = document.getElementById('learn-more-modal');
        if (show) { m.style.opacity='1'; m.style.visibility='visible'; m.style.pointerEvents='auto'; m.querySelector('.modal-card-inner').style.transform='scale(1) translateY(0)'; }
        else { m.style.opacity='0'; m.style.visibility='hidden'; m.style.pointerEvents='none'; m.querySelector('.modal-card-inner').style.transform='scale(0.8) translateY(30px)'; }
    }

    function closeModal(e) { if(e.target.id==='learn-more-modal') toggleModal(false); }

    // Auto-show login if errors exist
    @if($errors->any() || session('success'))
    window.addEventListener('DOMContentLoaded', () => showLogin());
    @endif
</script>
@endpush
