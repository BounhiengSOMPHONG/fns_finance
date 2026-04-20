<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" style="display: flex; flex-direction: column; gap: 20px;">
        @csrf

        <!-- Username -->
        <div class="form-group">
            <label for="username" class="form-label required">ຊື່ຜູ້ໃຊ້ (Username)</label>
            <input id="username" class="form-input" type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" placeholder="ປ້ອນຊື່ຜູ້ໃຊ້" />
            @error('username')
                <p style="font-size:var(--font-size-sm); color:#DC2626; margin-top:4px;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password" class="form-label required">ລະຫັດຜ່ານ (Password)</label>
            <input id="password" class="form-input" type="password" name="password" required autocomplete="current-password" placeholder="ປ້ອນລະຫັດຜ່ານ" />
            @error('password')
                <p style="font-size:var(--font-size-sm); color:#DC2626; margin-top:4px;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me -->
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <label for="remember_me" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <input id="remember_me" type="checkbox" name="remember" style="width: 16px; height: 16px; accent-color: var(--color-primary);">
                <span class="form-label" style="margin: 0; cursor: pointer;">ຈື່ຂ້ອຍໄວ້ຕຳຫຼອດ (Remember me)</span>
            </label>
        </div>

        <div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; height: 42px; font-size: var(--font-size-md);">
                ເຂົ້າສູ່ລະບົບ (Log in)
            </button>
        </div>
    </form>
</x-guest-layout>