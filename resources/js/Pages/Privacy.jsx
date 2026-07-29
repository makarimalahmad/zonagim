import LegalLayout from '@/Layouts/LegalLayout';

export default function Privacy() {
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
        <LegalLayout title="Kebijakan Privasi">
            <div className="py-10 sm:py-12">
                <p className="mb-8 text-2xl font-semibold text-base-content">Kebijakan Privasi Zonagim</p>

                <Section title="Informasi yang Kami Kumpulkan">
                    <p>
                        Zonagim menghargai privasi Anda. Informasi yang kami kumpulkan terbatas pada data yang diperlukan untuk menyediakan layanan kami.
                    </p>
                    <dl className="space-y-5">
                        <div>
                            <dt className="font-semibold text-base-content">Data Akun</dt>
                            <dd>Nama, alamat email, dan password dalam bentuk hash saat Anda mendaftar.</dd>
                        </div>
                        <div>
                            <dt className="font-semibold text-base-content">Data Profil Opsional</dt>
                            <dd>Nomor WhatsApp dan alamat yang Anda isi, termasuk provinsi, kota atau kabupaten, kecamatan, kelurahan, alamat jalan, serta kode pos.</dd>
                        </div>
                        <div>
                            <dt className="font-semibold text-base-content">Percakapan Asisten AI</dt>
                            <dd>Pesan yang Anda kirim ke chatbot dan riwayat terbatas yang diperlukan untuk memberikan jawaban.</dd>
                        </div>
                        <div>
                            <dt className="font-semibold text-base-content">Log Teknis</dt>
                            <dd>Alamat IP, informasi sesi, dan data teknis permintaan untuk keamanan sistem, pembatasan penyalahgunaan, serta pemecahan masalah.</dd>
                        </div>
                    </dl>
                </Section>

                <Section title="Penggunaan Informasi">
                    <p>Kami menggunakan data Anda hanya untuk kepentingan operasional, meliputi:</p>
                    <ul className="list-disc pl-5 space-y-2 marker:text-yellow-500">
                        <li>Membuat, mengamankan, dan mengelola akun Anda.</li>
                        <li>Menampilkan profil dan membantu komunikasi yang Anda pilih untuk isi.</li>
                        <li>Memberikan jawaban melalui asisten AI.</li>
                        <li>Mengirimkan notifikasi penting, termasuk kode OTP dan reset password.</li>
                        <li>Mencegah penyalahgunaan serta menjaga keamanan dan kestabilan platform.</li>
                    </ul>
                </Section>

                <Section title="Perlindungan Data">
                    <p>
                        Kami menerapkan langkah keamanan standar industri untuk melindungi data pribadi Anda. Password pengguna disimpan dalam bentuk hash dan tidak dapat dibaca oleh siapa pun, termasuk admin.
                    </p>
                    <p>
                        Kami <strong className="font-semibold text-base-content">tidak akan</strong> menjual, menyewakan, atau memberikan data pribadi Anda kepada pihak ketiga untuk tujuan pemasaran tanpa persetujuan Anda.
                    </p>
                </Section>

                <Section title="Cookies dan Penyedia Layanan">
                    <p>
                        Website menggunakan cookies sesi untuk autentikasi, keamanan, dan preferensi tampilan. Cookies ini diperlukan agar fungsi akun dapat berjalan.
                    </p>
                    <p>
                        Cloudflare Turnstile menerima token verifikasi dan dapat menerima alamat IP untuk membantu mencegah bot serta penyalahgunaan. Jika Anda menggunakan chatbot, isi pesan dan riwayat pengguna terbatas dikirim ke Groq untuk menghasilkan jawaban. Masing-masing penyedia memproses data sesuai kebijakan privasinya.
                    </p>
                </Section>

                <Section title="Penyimpanan dan Hak Anda">
                    <p>
                        Data akun disimpan selama akun aktif atau selama diperlukan untuk keamanan dan kewajiban yang berlaku. Riwayat chatbot pengguna terautentikasi disimpan secara terbatas hingga 24 jam, sedangkan riwayat tamu hanya berada di perangkat selama sesi penggunaan.
                    </p>
                    <p>
                        Anda dapat memperbarui data profil atau menghapus akun melalui menu profil. Permintaan lain terkait akses, koreksi, atau penghapusan data dapat disampaikan melalui kontak resmi yang tersedia di website.
                    </p>
                </Section>

                <Section title="Hubungi Kami">
                    <p>
                        Jika memiliki pertanyaan tentang pengelolaan data pribadi, Anda dapat menghubungi kami melalui kontak yang tersedia di website.
                    </p>
                </Section>
            </div>
        </LegalLayout>
    );
}
