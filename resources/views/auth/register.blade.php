@extends('layouts.guest')

@section('content')
    {{-- Logo --}}
    <div style="width:110px; height:110px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; perspective:1000px; background:rgba(34,197,94,0.06); border:1px solid rgba(34,197,94,0.1);">
        <img src="{{ asset('pictures/ibslogo.jpg') }}" alt="IBS Logo" style="max-width:65%; height:auto; filter:drop-shadow(0 0 15px rgba(34,197,94,0.35)); border-radius:8px;">
    </div>

    <h3 style="margin-bottom:6px; color:white; text-align:center; font-weight:800; font-size:1.4rem;">Create Account</h3>
    <p style="text-align:center; color:rgba(255,255,255,0.4); font-size:0.85rem; margin-bottom:28px;">Join InsightBlitz today</p>

    @if ($errors->any())
        <script>Swal.fire({ icon:'error', title:'Registration Error', text:'{{ $errors->first() }}', confirmButtonColor:'#22c55e' });</script>
    @endif

    <form method="POST" action="{{ route('register.store') }}" novalidate>
        @csrf

        {{-- NAME --}}
        <div style="position:relative; margin-bottom:22px;">
            <i class="fa-solid fa-user" style="position:absolute; left:14px; top:14px; color:#22c55e; z-index:5; font-size:0.85rem;"></i>
            <input name="name" value="{{ old('name') }}" required placeholder=" "
                   style="width:100%; padding:13px 13px 13px 42px; border:1px solid rgba(255,255,255,0.08); background:rgba(255,255,255,0.04); border-radius:10px; outline:none; box-sizing:border-box; color:#fff; font-size:0.92rem; transition:all 0.3s;"
                   onfocus="this.style.borderColor='#22c55e'; this.style.boxShadow='0 0 0 3px rgba(34,197,94,0.12)'"
                   onblur="this.style.borderColor='rgba(255,255,255,0.08)'; this.style.boxShadow='none'">
            <label class="floating-label" style="position:absolute; left:42px; top:13px; color:rgba(255,255,255,0.4); pointer-events:none; transition:all 0.3s ease; font-size:0.9rem;">Name</label>
        </div>

        {{-- EMAIL --}}
        <div style="position:relative; margin-bottom:22px;">
            <i class="fa-solid fa-envelope" style="position:absolute; left:14px; top:14px; color:#22c55e; z-index:5; font-size:0.85rem;"></i>
            <input name="email" type="email" value="{{ old('email') }}" required placeholder=" "
                   style="width:100%; padding:13px 13px 13px 42px; border:1px solid rgba(255,255,255,0.08); background:rgba(255,255,255,0.04); border-radius:10px; outline:none; box-sizing:border-box; color:#fff; font-size:0.92rem; transition:all 0.3s;"
                   onfocus="this.style.borderColor='#22c55e'; this.style.boxShadow='0 0 0 3px rgba(34,197,94,0.12)'"
                   onblur="this.style.borderColor='rgba(255,255,255,0.08)'; this.style.boxShadow='none'">
            <label class="floating-label" style="position:absolute; left:42px; top:13px; color:rgba(255,255,255,0.4); pointer-events:none; transition:all 0.3s ease; font-size:0.9rem;">Email</label>
        </div>

        {{-- PASSWORD --}}
        <div style="position:relative; margin-bottom:22px;">
            <i class="fa-solid fa-lock" style="position:absolute; left:14px; top:14px; color:#22c55e; z-index:5; font-size:0.85rem;"></i>
            <input name="password" type="password" required placeholder=" "
                   style="width:100%; padding:13px 13px 13px 42px; border:1px solid rgba(255,255,255,0.08); background:rgba(255,255,255,0.04); border-radius:10px; outline:none; box-sizing:border-box; color:#fff; font-size:0.92rem; transition:all 0.3s;"
                   onfocus="this.style.borderColor='#22c55e'; this.style.boxShadow='0 0 0 3px rgba(34,197,94,0.12)'"
                   onblur="this.style.borderColor='rgba(255,255,255,0.08)'; this.style.boxShadow='none'">
            <label class="floating-label" style="position:absolute; left:42px; top:13px; color:rgba(255,255,255,0.4); pointer-events:none; transition:all 0.3s ease; font-size:0.9rem;">Password</label>
        </div>

        {{-- CONFIRM PASSWORD --}}
        <div style="position:relative; margin-bottom:28px;">
            <i class="fa-solid fa-shield-halved" style="position:absolute; left:14px; top:14px; color:#22c55e; z-index:5; font-size:0.85rem;"></i>
            <input name="password_confirmation" type="password" required placeholder=" "
                   style="width:100%; padding:13px 13px 13px 42px; border:1px solid rgba(255,255,255,0.08); background:rgba(255,255,255,0.04); border-radius:10px; outline:none; box-sizing:border-box; color:#fff; font-size:0.92rem; transition:all 0.3s;"
                   onfocus="this.style.borderColor='#22c55e'; this.style.boxShadow='0 0 0 3px rgba(34,197,94,0.12)'"
                   onblur="this.style.borderColor='rgba(255,255,255,0.08)'; this.style.boxShadow='none'">
            <label class="floating-label" style="position:absolute; left:42px; top:13px; color:rgba(255,255,255,0.4); pointer-events:none; transition:all 0.3s ease; font-size:0.9rem;">Confirm Password</label>
        </div>

        <div style="display:grid; gap:10px;">
            <button type="submit" style="position:relative; overflow:hidden; width:100%; padding:13px; background:linear-gradient(135deg,#22c55e,#16a34a); color:black; border:none; border-radius:10px; font-weight:700; cursor:pointer; transition:all 0.3s; font-size:0.92rem; box-shadow:0 4px 20px rgba(34,197,94,0.25);">
                Register
            </button>
            <a href="{{ route('login') }}" style="display:block; text-align:center; width:100%; padding:13px; background:rgba(255,255,255,0.04); color:rgba(255,255,255,0.7); border:1px solid rgba(255,255,255,0.08); border-radius:10px; font-weight:600; text-decoration:none; transition:all 0.3s; font-size:0.92rem;">
                Back to Sign In
            </a>
        </div>
    </form>
@endsection

@push('styles')
<style>
    input:focus ~ .floating-label,
    input:not(:placeholder-shown) ~ .floating-label {
        top: -20px !important;
        left: 10px !important;
        font-size: 0.75rem !important;
        color: #22c55e !important;
        font-weight: 600 !important;
    }
</style>
@endpush
