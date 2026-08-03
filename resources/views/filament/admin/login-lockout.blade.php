@if (method_exists($this, 'isLockedOut') && $this->isLockedOut())
    <div
        class="admin-login-lockout"
        role="alert"
        x-data="{ remaining: {{ $this->getLockoutSecondsRemaining() }}, timer: null }"
        x-init="timer = setInterval(() => { if (remaining <= 1) { clearInterval(timer); window.location.reload(); return; } remaining--; }, 1000)"
    >
        <x-filament::icon
            icon="heroicon-o-x-circle"
            class="admin-login-lockout-icon"
        />
        <div>
            <p class="admin-login-lockout-title">Login diblokir sementara</p>
            <p class="admin-login-lockout-message">
                Coba lagi dalam
                <span x-text="`${String(Math.floor(remaining / 60)).padStart(2, '0')}:${String(remaining % 60).padStart(2, '0')}`"></span>.
            </p>
        </div>
    </div>
@endif
