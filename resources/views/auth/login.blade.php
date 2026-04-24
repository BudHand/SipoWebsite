@extends('layouts.login')

@section('content')
<section class="login-page min-h-screen flex items-center justify-center px-4 py-10 relative overflow-hidden">

    {{-- Animated background --}}
    <div class="login-bg-orb absolute -top-24 -left-24 h-72 w-72 rounded-full bg-blue-200/60 blur-3xl"></div>
    <div class="login-bg-orb absolute bottom-0 -right-20 h-80 w-80 rounded-full bg-cyan-200/60 blur-3xl"></div>

    <div class="login-card relative z-10 w-full max-w-6xl grid lg:grid-cols-2 rounded-[2rem] overflow-hidden bg-white shadow-2xl border border-slate-200">

        {{-- LEFT --}}
        <div class="hidden lg:flex relative p-12 flex-col justify-between bg-white overflow-hidden">

            <div class="absolute inset-0">
                <img src="{{ asset('assets/img/backgroundLogin.png') }}"
                     class="w-full h-full object-cover opacity-10">
                <div class="absolute inset-0 bg-gradient-to-br from-white via-blue-50 to-slate-100"></div>
            </div>

            <div class="relative z-10 space-y-8 text-center">
                <img src="{{ asset('assets/img/Logo-SIPO-Text.png') }}"
                    class="login-left-animate h-20 w-auto mx-auto mb-10">

                <div class="space-y-4">
                    <span class="login-left-animate inline-block px-4 py-1.5 text-sm rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                        Sistem Informasi Persuratan Online
                    </span>

                    <h1 class="login-left-animate text-4xl font-extrabold text-slate-900 leading-tight">
                        Kelola Surat Lebih Cepat & Terstruktur
                    </h1>

                    <p class="login-left-animate text-slate-500 leading-relaxed max-w-md mx-auto">
                        SIPO membantu proses persuratan internal menjadi lebih efisien,
                        terdokumentasi, dan mudah dipantau.
                    </p>
                </div>
            </div>

            <div class="relative z-10 grid grid-cols-3 gap-4 mt-10">
                <div class="login-stat-card p-4 bg-white rounded-xl border shadow-sm hover:-translate-y-1 transition">
                    <p class="text-blue-600 font-bold text-lg">01</p>
                    <p class="text-sm text-slate-500 mt-1">Buat Surat</p>
                </div>
                <div class="login-stat-card p-4 bg-white rounded-xl border shadow-sm hover:-translate-y-1 transition">
                    <p class="text-blue-600 font-bold text-lg">02</p>
                    <p class="text-sm text-slate-500 mt-1">Approval</p>
                </div>
                <div class="login-stat-card p-4 bg-white rounded-xl border shadow-sm hover:-translate-y-1 transition">
                    <p class="text-blue-600 font-bold text-lg">03</p>
                    <p class="text-sm text-slate-500 mt-1">Monitoring</p>
                </div>
            </div>
        </div>

        {{-- RIGHT --}}
        <div class="flex items-center px-6 py-10 sm:px-12 lg:px-14 bg-white">
            <div class="w-full max-w-md mx-auto">

                <div class="login-form-item lg:hidden text-center mb-8">
                    <img src="{{ asset('assets/img/Logo-SIPO-Text.png') }}"
                         class="h-14 mx-auto mb-4">
                </div>

                <div class="login-form-item mb-8">
                    <p class="text-sm font-semibold text-blue-600 mb-2">
                        Selamat datang kembali
                    </p>

                    <h2 class="text-3xl font-extrabold text-slate-900">
                        Masuk ke SIPO
                    </h2>

                    <p class="text-sm text-slate-500 mt-2">
                        Gunakan akun Anda untuk melanjutkan.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="login-form-item mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div class="login-form-item mb-5 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div class="login-form-item">
                        <label class="text-sm font-semibold text-slate-700 mb-2 block">
                            NIP
                        </label>

                        <div class="relative group">
                            <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition"></i>

                            <input type="text"
                                   name="credential"
                                   value="{{ old('credential') }}"
                                   required autofocus
                                   placeholder="Masukkan NIP"
                                   class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition">
                        </div>
                    </div>

                    <div class="login-form-item">
                        <label class="text-sm font-semibold text-slate-700 mb-2 block">
                            Password
                        </label>

                        <div class="relative group">
                            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition"></i>

                            <input type="password"
                                   name="password"
                                   required
                                   placeholder="Masukkan Password"
                                   class="w-full pl-11 pr-12 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 text-sm focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition">

                            <button type="button"
                                    onclick="togglePassword(this)"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600 transition">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="login-form-item flex justify-between items-center text-sm">
                        <label class="flex items-center gap-2 text-slate-600">
                            <input type="checkbox" name="remember"
                                   class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            Ingat saya
                        </label>

                        <a href="{{ route('forgot-password') }}"
                           class="page-transition-link text-blue-600 font-semibold hover:text-blue-700">
                            Lupa Password?
                        </a>
                    </div>

                    <button type="submit"
                            class="login-form-item w-full py-3 rounded-xl bg-blue-600 text-white font-bold shadow-lg shadow-blue-600/25 hover:bg-blue-700 transition hover:-translate-y-0.5">
                        Masuk
                    </button>
                </form>

                <p class="login-form-item mt-8 text-center text-xs text-slate-400">
                    © {{ date('Y') }} SIPO - Sistem Informasi Persuratan Online
                </p>
            </div>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script>
function togglePassword(button) {
    const input = button.parentElement.querySelector('input');
    const icon = button.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
@endpush
