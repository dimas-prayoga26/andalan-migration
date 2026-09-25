@extends('layouts.main')

@section('title', 'Email Login')

@section('css')
    <style>
        .mail-access-card {
            max-width: 520px;
            margin-inline: auto;
            border-radius: 8px;
            border: 1px solid #d8deef !important;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.10) !important;
        }

        .mail-access-icon {
            width: 58px;
            height: 58px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #eef2ff;
            color: #2044d4;
        }

        .mail-access-card .form-label {
            color: #0f172a;
            font-size: 14px;
        }

        .mail-access-card .form-control {
            border-color: #cbd5e1;
            color: #0f172a;
            font-weight: 600;
        }

        .mail-access-card .form-control:focus {
            border-color: #2044d4;
            box-shadow: 0 0 0 3px rgba(32, 68, 212, 0.12);
        }

        .mail-pin-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(48px, 1fr));
            gap: 10px;
        }

        .mail-pin-input {
            height: 64px;
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            border-radius: 8px;
        }
    </style>
@endsection

@section('navbarTitle', 'Email')

@section('content')

@php
    $pendingEmail = $pendingEmail ?? null;
@endphp

@include('layouts.breadcrumb', [
    'title' => 'Email',
    'current' => 'Login',
    'homeRoute' => 'dashboard',
])

<div class="card border-0 mb-0 h-auto">
    <div class="card-body py-5">
        <div class="mail-access-card card p-4 p-md-5 mb-0">
            <div class="text-center mb-4">
                <span class="mail-access-icon">
                    <i class="fa-regular fa-envelope fs-3"></i>
                </span>
            </div>

            @if (! $pendingEmail)
                <h4 class="text-center mb-2">Email Access</h4>
                <p class="text-center text-muted mb-4">Masukkan email yang sudah terdaftar.</p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('applicant.email.check') }}">
                    @csrf
                    <div class="form-group mb-4">
                        <label class="form-label"><strong>Email</strong></label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control form-control-lg" placeholder="hr@rnb.co.id" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">Lanjut</button>
                </form>
            @else
                <h4 class="text-center mb-2">Masukkan PIN</h4>
                <p class="text-center text-muted mb-1">Email valid untuk</p>
                <p class="text-center fw-semibold text-break mb-4">{{ $pendingEmail }}</p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('applicant.email.login') }}">
                    @csrf
                    <input type="hidden" name="email" value="{{ $pendingEmail }}">
                    <div class="form-group mb-4">
                        <label class="form-label"><strong>PIN</strong></label>
                        <div class="mail-pin-grid">
                            @for ($index = 0; $index < 4; $index++)
                                <input type="password" name="pin_digits[]" inputmode="numeric" maxlength="1" pattern="[0-9]" class="form-control mail-pin-input" autocomplete="one-time-code" required {{ $index === 0 ? 'autofocus' : '' }}>
                            @endfor
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">Masuk ke Email</button>
                </form>
                <a href="{{ route('applicant.email.index') }}" class="btn btn-light w-100 mt-3">Ganti Email</a>
            @endif
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    document.querySelectorAll('.mail-pin-input').forEach(function (input, index, inputs) {
        input.addEventListener('input', function () {
            input.value = input.value.replace(/\D/g, '').slice(0, 1);

            if (input.value && inputs[index + 1]) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Backspace' && !input.value && inputs[index - 1]) {
                inputs[index - 1].focus();
            }
        });
    });
</script>
@endsection
