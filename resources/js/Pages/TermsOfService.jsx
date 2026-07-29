import LegalLayout from '@/Layouts/LegalLayout';

export default function TermsOfService() {
    const Section = ({ title, children }) => (
        <section className="mb-10 last:mb-0">
            <h2 className="mb-4 text-xl font-semibold text-base-content">
                {title}
            </h2>
            <div className="text-base-content/70 leading-8 space-y-4">
                {children}
            </div>
        </section>
    );

    return (
        <LegalLayout title="Syarat & Ketentuan">
            <div className="py-10 sm:py-12">
                <p className="mb-8 text-2xl font-semibold text-base-content">Aturan Penggunaan Zonagim</p>

                <Section title="Pendahuluan">
                    <p>
                        Selamat datang di <strong className="font-semibold text-base-content">Zonagim</strong>. Dengan mengakses dan menggunakan layanan kami, Anda menyetujui syarat dan ketentuan yang tertulis di halaman ini. Zonagim bertindak sebagai <strong className="font-semibold text-base-content">platform penyedia layanan marketplace (perantara)</strong> yang mempertemukan Penjual dan Pembeli akun game.
                    </p>
                </Section>

                <Section title="Peran Zonagim">
                    <p>
                        Posisi Zonagim adalah sebagai <strong className="font-semibold text-base-content">Penyedia Platform</strong>. Kami menyediakan wadah untuk listing akun game dan memfasilitasi alur transaksi.
                    </p>
                    <ul className="list-disc pl-5 space-y-2 marker:text-yellow-500">
                        <li>Kami memegang kendali penuh atas pengelolaan website dan konten yang ditampilkan.</li>
                        <li>Deskripsi produk dan ketersediaan akun diinput oleh admin berdasarkan data yang diterima.</li>
                        <li>Kami berhak menolak atau menghapus listing yang melanggar aturan tanpa pemberitahuan sebelumnya.</li>
                    </ul>
                </Section>

                <Section title="Batasan Tanggung Jawab">
                    <p>
                        Zonagim <strong className="font-semibold text-base-content">mutlak tidak bertanggung jawab</strong> atas kejadian <i>hack back</i> atau pengambilan kembali akun oleh pemilik asli, <i>banned</i>, maupun masalah teknis lain setelah transaksi selesai. Ketentuan ini berlaku untuk transaksi langsung dan transaksi yang menggunakan Jasa Rekber kami.
                    </p>
                    <p>
                        Kami hanya bertindak sebagai penyedia lapak. Layanan <strong className="font-semibold text-base-content">Rekber (Rekening Bersama)</strong> hanya membantu proses serah terima uang dan data akun saat transaksi berlangsung. Rekber bukan garansi atau asuransi keamanan akun di masa depan. Seluruh risiko pasca-transaksi menjadi tanggung jawab pembeli.
                    </p>
                </Section>

                <Section title="Fungsi Jasa Rekber">
                    <p>
                        Rekber menahan dana selama proses transaksi hingga pembeli menerima data akun yang dinyatakan valid saat transaksi. Tugas Rekber selesai setelah transaksi tuntas dan tidak menjamin akun bebas dari risiko <i>hack back</i>.
                    </p>
                    <p>
                        Penggunaan Rekber membantu mengurangi risiko penyerahan dana sebelum data akun diterima. Risiko kualitas dan keamanan akun tetap berada di luar tanggung jawab kami.
                    </p>
                </Section>

                <Section title="Kewajiban Pengguna">
                    <div>
                        <h3 className="mb-1 font-semibold text-base-content">Pembeli</h3>
                        <p>Wajib membaca deskripsi akun dengan teliti sebelum membeli dan segera mengamankan akun dengan mengganti password serta mengaktifkan verifikasi 2FA setelah menerima data.</p>
                    </div>
                    <div>
                        <h3 className="mb-1 font-semibold text-base-content">Pengguna Umum</h3>
                        <p>Dilarang melakukan tindakan yang merugikan platform, termasuk spam, hacking, dan penyalahgunaan fitur.</p>
                    </div>
                </Section>

                <p className="pt-10 text-sm leading-6 text-base-content/50">
                    Kami berhak mengubah syarat dan ketentuan ini sewaktu-waktu tanpa pemberitahuan sebelumnya. Pengguna diharapkan memeriksa halaman ini secara berkala.
                </p>
            </div>
        </LegalLayout>
    );
}
