// Setelah deploy baru, nama file chunk (hash) berubah. Jika SPA yang sudah
// terbuka di browser mencoba memuat chunk lama yang tidak ada lagi (dynamic
// import gagal), muat ulang halaman sekali agar memakai aset terbaru. Ini
// mencegah navigasi (mis. setelah login) terasa "macet" sampai user refresh
// manual.
window.addEventListener('vite:preloadError', () => {
    if (!sessionStorage.getItem('vitePreloadReloaded')) {
        sessionStorage.setItem('vitePreloadReloaded', '1');
        window.location.reload();
    }
});
