{{-- PWA Floating Button untuk Admin --}}
<button id="pwa-install-btn" class="pwa-floating-admin" style="display: none;">
    <i class="bi bi-download"></i>
</button>

<style>
    .pwa-floating-admin {
        position: fixed !important;
        top: 80px !important;
        right: 20px !important;
        width: 50px !important;
        height: 50px !important;
        background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
        color: white !important;
        border: none !important;
        border-radius: 50% !important;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3) !important;
        cursor: pointer !important;
        transition: all 0.3s ease !important;
        z-index: 9999 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 18px !important;
    }

    .pwa-floating-admin:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4) !important;
    }
</style>
