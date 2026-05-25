<x-guest-layout>
    {{-- Session Status --}}
    @if (session('status'))
        <div style="color: rgba(255,255,255,0.7); background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; padding: 0.6rem 1rem; font-size: 0.82rem; margin-bottom: 1rem;">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" style="display: flex; flex-direction: column; gap: 1.1rem;">
        @csrf

        {{-- Username --}}
        <div>
            <label for="username" class="fns-form-label">ຊື່ຜູ້ໃຊ້ (Username)</label>
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
                <p style="color: #fca5a5; font-size: 0.75rem; margin-top: 0.35rem;">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="fns-form-label">ລະຫັດຜ່ານ (Password)</label>
            <input
                id="password"
                type="password"
                name="password"
                class="fns-form-input"
                placeholder="••••••••"
                required autocomplete="current-password"
            >
            @error('password')
                <p style="color: #fca5a5; font-size: 0.75rem; margin-top: 0.35rem;">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember Me --}}
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <input
                id="remember_me"
                type="checkbox"
                name="remember"
                style="width: 15px; height: 15px; accent-color: var(--fns-gold); border-radius: 4px;"
            >
            <label for="remember_me" style="font-size: 0.78rem; color: rgba(255,255,255,0.45); cursor: pointer;">
                ຈົດຈຳການເຂົ້າສູ່ລະບົບ
            </label>
        </div>

        {{-- Submit --}}
        <div style="margin-top: 0.5rem;">
            <button type="submit" class="fns-btn-primary">
                ເຂົ້າສູ່ລະບົບ
            </button>
        </div>
    </form>
</x-guest-layout>

