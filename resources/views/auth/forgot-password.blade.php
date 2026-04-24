@extends('layouts.login')

@section('content')
    <section class="login-page min-h-screen flex items-center justify-center px-4 py-10">
        <div
            class="login-card w-full max-w-6xl grid lg:grid-cols-2 rounded-[2rem] overflow-hidden bg-white border border-slate-200 shadow-2xl">

            {{-- Left Branding --}}
            <div class="login-left-panel hidden lg:flex relative min-h-[620px] p-10 flex-col justify-between overflow-hidden bg-white">
                <div class="absolute inset-0">
                    <img src="{{ asset('assets/img/backgroundLogin.png') }}"
                         alt="Background SIPO"
                         class="h-full w-full object-cover opacity-10">
                    <div class="absolute inset-0 bg-gradient-to-br from-white via-blue-50 to-slate-100"></div>
                </div>

                <div class="relative z-10 text-center space-y-8">
                    <img src="{{ asset('assets/img/Logo-SIPO-Text.png') }}"
                        alt="SIPO"
                        class="login-logo h-20 w-auto mx-auto mb-6">

                    <div class="space-y-5">
                        <span class="login-badge inline-flex rounded-full bg-blue-50 px-4 py-2 text-sm text-blue-700 border border-blue-100">
                            Pemulihan Akun
                        </span>

                        <h1 class="login-title text-4xl font-extrabold tracking-tight text-slate-900 leading-tight">
                            Reset Password dengan Aman dan Cepat
                        </h1>

                        <p class="login-subtitle max-w-md mx-auto text-slate-500 text-base leading-relaxed">
                            Masukkan email dan NIP yang terdaftar. Link reset password akan dikirim ke email tujuan.
                        </p>
                    </div>
                </div>

                <div class="relative z-10 grid grid-cols-3 gap-3 text-sm">
                    <div class="login-stat-card rounded-2xl bg-white border border-slate-200 p-4 shadow-sm">
                        <p class="text-2xl font-bold text-blue-600">01</p>
                        <p class="text-slate-500 mt-1">Validasi</p>
                    </div>
                    <div class="login-stat-card rounded-2xl bg-white border border-slate-200 p-4 shadow-sm">
                        <p class="text-2xl font-bold text-blue-600">02</p>
                        <p class="text-slate-500 mt-1">Kirim Link</p>
                    </div>
                    <div class="login-stat-card rounded-2xl bg-white border border-slate-200 p-4 shadow-sm">
                        <p class="text-2xl font-bold text-blue-600">03</p>
                        <p class="text-slate-500 mt-1">Reset</p>
                    </div>
                </div>
            </div>

            {{-- Right Form --}}
            <div class="bg-white text-slate-900 px-6 py-10 sm:px-12 lg:px-14 flex items-center">
                <div class="w-full max-w-md mx-auto">

                    <div class="lg:hidden login-form-item mb-8 text-center">
                        <img src="{{ asset('assets/img/Logo-SIPO-Text.png') }}"
                             alt="SIPO"
                             class="h-16 mx-auto mb-4">
                        <h1 class="text-2xl font-bold text-slate-900">Lupa Password</h1>
                        <p class="text-sm text-slate-500">Sistem Informasi Persuratan Online</p>
                    </div>

                    <div class="login-form-item mb-8">
                        <p class="text-sm font-semibold text-blue-600 mb-2">Butuh bantuan masuk?</p>
                        <h2 class="text-3xl font-extrabold text-slate-900">Lupa Password</h2>
                        <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                            Isi data berikut untuk menerima link reset password melalui email.
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="login-form-item mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('status'))
                        @php
                            $statusMessages = [
                                'email_sent' => 'Link untuk melakukan reset password telah dikirim ke email Anda.',
                                'invalid_nip' => 'NIP yang Anda masukkan tidak valid.',
                                'email_not_found' => 'Email tidak ditemukan di sistem.',
                            ];

                            $statusMessage = $statusMessages[session('status')] ?? session('status');
                            $isSuccess = session('status') === 'email_sent';
                        @endphp

                        <div class="login-form-item mb-5 rounded-2xl border px-4 py-3 text-sm
                            {{ $isSuccess ? 'border-green-200 bg-green-50 text-green-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                            {{ $statusMessage }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('send-email') }}" novalidate class="space-y-5">
                        @csrf

                        <div class="login-form-item">
                            <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">
                                Email Tujuan
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input id="email"
                                       type="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       required
                                       autofocus
                                       placeholder="Masukkan Email Tujuan"
                                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-4 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            </div>
                        </div>

                        <div class="login-form-item">
                            <label for="nip" class="block text-sm font-semibold text-slate-700 mb-2">
                                NIP
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input id="nip"
                                       type="text"
                                       name="nip"
                                       value="{{ old('nip') }}"
                                       required
                                       placeholder="Masukkan NIP"
                                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3.5 pl-11 pr-4 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            </div>
                        </div>

                        <button type="submit"
                                class="login-form-item w-full rounded-2xl bg-blue-600 px-5 py-3.5 text-sm font-bold uppercase tracking-wide text-white shadow-lg shadow-blue-600/25 transition hover:bg-blue-700 hover:-translate-y-0.5 active:translate-y-0">
                            Kirim Link Reset
                        </button>

                        <a href="{{ route('login') }}"
                           class="page-transition-link login-form-item flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50 hover:text-blue-600">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali ke Login
                        </a>
                    </form>

                    <p class="login-form-item mt-8 text-center text-xs text-slate-400">
                        © {{ date('Y') }} SIPO - Sistem Informasi Persuratan Online
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
