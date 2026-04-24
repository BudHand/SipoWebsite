@extends('layouts.login')

@section('content')
    <section class="login-page min-h-screen flex items-center justify-center px-4 py-10 relative overflow-hidden">

        {{-- Animated background --}}
        <div class="login-bg-orb absolute -top-24 -left-24 h-72 w-72 rounded-full bg-blue-200/60 blur-3xl"></div>
        <div class="login-bg-orb absolute bottom-0 -right-20 h-80 w-80 rounded-full bg-cyan-200/60 blur-3xl"></div>

        <div class="login-card relative z-10 w-full max-w-6xl grid lg:grid-cols-2 rounded-[2rem] overflow-hidden bg-white shadow-2xl border border-slate-200">

            {{-- Left Branding --}}
            <div class="hidden lg:flex relative p-12 flex-col justify-between bg-white overflow-hidden">

                <div class="absolute inset-0">
                    <img src="{{ asset('assets/img/backgroundLogin.png') }}"
                         alt="Background SIPO"
                         class="w-full h-full object-cover opacity-10">
                    <div class="absolute inset-0 bg-gradient-to-br from-white via-blue-50 to-slate-100"></div>
                </div>

                <div class="relative z-10 text-center space-y-8">
                    <img src="{{ asset('assets/img/Logo-SIPO-Text.png') }}"
                         alt="SIPO"
                         class="login-left-animate h-20 w-auto mx-auto mb-6">

                    <div class="space-y-5">
                        <span class="login-left-animate inline-flex rounded-full bg-blue-50 px-4 py-2 text-sm text-blue-700 border border-blue-100">
                            Keamanan Akun
                        </span>

                        <h1 class="login-left-animate text-4xl font-extrabold tracking-tight text-slate-900 leading-tight">
                            Buat Password Baru untuk Akun Anda
                        </h1>

                        <p class="login-left-animate max-w-md mx-auto text-slate-500 text-base leading-relaxed">
                            Gunakan password baru yang kuat agar akun SIPO Anda tetap aman dan terlindungi.
                        </p>
                    </div>
                </div>

                <div class="relative z-10 grid grid-cols-3 gap-4 mt-10">
                    <div class="login-stat-card p-4 bg-white rounded-xl border shadow-sm hover:-translate-y-1 transition">
                        <p class="text-blue-600 font-bold text-lg">01</p>
                        <p class="text-sm text-slate-500 mt-1">Token</p>
                    </div>
                    <div class="login-stat-card p-4 bg-white rounded-xl border shadow-sm hover:-translate-y-1 transition">
                        <p class="text-blue-600 font-bold text-lg">02</p>
                        <p class="text-sm text-slate-500 mt-1">Password</p>
                    </div>
                    <div class="login-stat-card p-4 bg-white rounded-xl border shadow-sm hover:-translate-y-1 transition">
                        <p class="text-blue-600 font-bold text-lg">03</p>
                        <p class="text-sm text-slate-500 mt-1">Selesai</p>
                    </div>
                </div>
            </div>

            {{-- Right Form --}}
            <div class="flex items-center px-6 py-10 sm:px-12 lg:px-14 bg-white">
                <div class="w-full max-w-md mx-auto">

                    <div class="login-form-item lg:hidden text-center mb-8">
                        <img src="{{ asset('assets/img/Logo-SIPO-Text.png') }}"
                             alt="SIPO"
                             class="h-16 mx-auto mb-4">
                    </div>

                    <div class="login-form-item mb-8">
                        <p class="text-sm font-semibold text-blue-600 mb-2">
                            Reset Password
                        </p>

                        <h2 class="text-3xl font-extrabold text-slate-900">
                            Password Baru
                        </h2>

                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                            Masukkan password baru dan konfirmasi password untuk melanjutkan.
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

                    <form method="POST" action="{{ route('password.update') }}" novalidate class="space-y-5">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="email" value="{{ request('email') }}">

                        <div class="login-form-item">
                            <label for="password" class="text-sm font-semibold text-slate-700 mb-2 block">
                                Password Baru
                            </label>

                            <div class="relative group">
                                <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition"></i>

                                <input id="password"
                                       type="password"
                                       name="password"
                                       required
                                       placeholder="Masukkan Password Baru"
                                       class="w-full pl-11 pr-12 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 placeholder:text-slate-400 text-sm focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition">

                                <button type="button"
                                        onclick="togglePassword(this)"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600 transition">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="login-form-item">
                            <label for="password_confirmation" class="text-sm font-semibold text-slate-700 mb-2 block">
                                Konfirmasi Password
                            </label>

                            <div class="relative group">
                                <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition"></i>

                                <input id="password_confirmation"
                                       type="password"
                                       name="password_confirmation"
                                       required
                                       placeholder="Ulangi Password Baru"
                                       class="w-full pl-11 pr-12 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-900 placeholder:text-slate-400 text-sm focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition">

                                <button type="button"
                                        onclick="togglePassword(this)"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600 transition">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit"
                                class="login-form-item w-full py-3 rounded-xl bg-blue-600 text-white font-bold shadow-lg shadow-blue-600/25 hover:bg-blue-700 transition hover:-translate-y-0.5">
                            Simpan Password
                        </button>

                        <a href="{{ route('login') }}"
                           class="page-transition-link login-form-item flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50 hover:text-blue-600">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali ke Login
                        </a>
                    </form>

                    <p class="login-form-item mt-8 text-center text-xs text-slate-400">
                        © {{ date('Y') }} SIPO
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

            if (!input || !icon) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
@endpush
