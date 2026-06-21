<x-guest-layout>
    {{-- Session Status --}}
    @if (session('status'))
        <div class="fns-login-status">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="fns-login-form">
        @csrf

        {{-- Username --}}
        <div class="fns-login-field">
            <label for="username" class="fns-form-label">ຊື່ຜູ້ໃຊ້</label>
            <input
                id="username"
                type="text"
                name="username"
                value="{{ old('username') }}"
                class="fns-form-input"
                placeholder="ກະລຸນາໃສ່ຊື່ຜູ້ໃຊ້"
                required autofocus autocomplete="username"
            >
            @error('username')
                <p class="fns-login-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div class="fns-login-field">
            <label for="password" class="fns-form-label">ລະຫັດຜ່ານ</label>
            <input
                id="password"
                type="password"
                name="password"
                class="fns-form-input"
                placeholder="••••••••"
                required autocomplete="current-password"
            >
            @error('password')
                <p class="fns-login-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember Me --}}
        <div class="fns-login-options">
            <input
                id="remember_me"
                type="checkbox"
                name="remember"
                class="fns-login-checkbox"
            >
            <label for="remember_me">
                ຈົດຈຳການເຂົ້າສູ່ລະບົບ
            </label>
        </div>

        {{-- Submit --}}
        <div class="fns-login-action">
            <button type="submit" class="fns-btn-primary">
                ເຂົ້າສູ່ລະບົບ
            </button>
        </div>
    </form>
</x-guest-layout>
