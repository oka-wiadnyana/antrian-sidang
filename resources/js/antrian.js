import './bootstrap';

window.Echo.channel('antrian-umum')
    .listen('AntrianUpdated', (event) => {
        console.log("📢 Event diterima:", event);
    });
