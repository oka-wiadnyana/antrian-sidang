<!-- PWA Install Button -->
<button id="pwa-install-btn" class="pwa-install-floating" style="display:none;">
    <i class="bi bi-download me-2"></i>
    <span>Install App</span>
    <div class="install-shine"></div>
</button>

<style>
    .pwa-install-floating {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 16px 24px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 14px;
        box-shadow: 0 8px 32px rgba(102, 126, 234, 0.4);
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        position: relative;
        z-index: 1000;
    }

    .pwa-install-floating:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 48px rgba(102, 126, 234, 0.6);
    }

    .install-shine {
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transform: rotate(45deg);
        transition: all 0.6s;
        opacity: 0;
    }

    .pwa-install-floating:hover .install-shine {
        animation: shine 1.5s ease-in-out;
    }

    @keyframes shine {
        0% {
            transform: translateX(-100%) translateY(-100%) rotate(45deg);
            opacity: 0;
        }

        50% {
            opacity: 1;
        }

        100% {
            transform: translateX(100%) translateY(100%) rotate(45deg);
            opacity: 0;
        }
    }
</style>

<script src="{{ asset('/sw.js') }}"></script>
<script src="{{ asset('pwa-install.js') }}"></script>
<script>
    if ("serviceWorker" in navigator) {
        navigator.serviceWorker.register("/sw.js");
    }
</script>
