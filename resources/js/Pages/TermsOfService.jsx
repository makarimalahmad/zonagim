import LegalLayout from '@/Layouts/LegalLayout';
import { ShieldAlert, Scale, HandCoins, UserCheck, ScrollText } from 'lucide-react';

export default function TermsOfService() {
    const Section = ({ number, title, icon: Icon, children }) => (
        <div className="mb-12 relative pl-8 sm:pl-12">
            <div className="absolute left-0 top-1 flex flex-col items-center">
                <div className="w-8 h-8 rounded-full bg-yellow-500/15 text-yellow-600 dark:text-yellow-400 font-bold flex items-center justify-center border border-yellow-500/30 text-sm">
                    {number}
                </div>
                <div className="w-px h-full bg-base-300 my-2"></div>
            </div>

            <div className="flex items-center gap-3 mb-4">
                {Icon && <Icon className="w-6 h-6 text-base-content/60" />}
                <h3 className="text-xl sm:text-2xl font-bold text-base-content">{title}</h3>
            </div>

            <div className="text-base-content/70 leading-relaxed text-lg space-y-4">
                {children}
            </div>
        </div>
    );

    return (
        <LegalLayout title="Syarat & Ketentuan">
            <div className="mt-8">
                <Section number="1" title="Pendahuluan" icon={ScrollText}>
                    <p>
                        Selamat datang di <strong className="text-base-content">LapakGimID</strong>. Dengan mengakses dan menggunakan layanan kami, Anda menyetujui syarat dan ketentuan yang tertulis di halaman ini.
                        LapakGimID bertindak sebagai <strong className="text-base-content">platform penyedia layanan marketplace (perantara)</strong> yang mempertemukan Penjual dan Pembeli akun game.
                    </p>
                </Section>

                <Section number="2" title="Peran LapakGimID" icon={Scale}>
                    <p>
                        Harap dipahami bahwa posisi LapakGimID adalah sebagai <strong className="text-base-content">Penyedia Platform</strong>. Kami menyediakan wadah untuk listing akun game dan memfasilitasi transaksi yang aman.
                    </p>
                    <ul className="list-disc pl-5 space-y-2 mt-4 marker:text-yellow-500">
                        <li>Kami memegang kendali penuh atas pengelolaan website dan konten yang ditampilkan.</li>
                        <li>Deskripsi produk dan ketersediaan akun diinput oleh admin berdasarkan data yang diterima.</li>
                        <li>Kami berhak menolak atau menghapus listing yang melanggar aturan tanpa pemberitahuan sebelumnya.</li>
                    </ul>
                </Section>

                <Section number="3" title="Batasan Tanggung Jawab" icon={ShieldAlert}>
                    <p>
                        Ini adalah poin yang sangat penting. Mohon diperhatikan bahwa:
                    </p>
                    <div className="bg-error/10 border border-error/20 p-6 rounded-2xl my-6">
                        <div className="flex items-start gap-4">
                            <ShieldAlert className="w-12 h-12 text-error flex-shrink-0" />
                            <div>
                                <h4 className="text-error font-bold text-lg mb-2">DISCLAIMER PENTING</h4>
                                <p className="text-base-content/80 text-base leading-relaxed">
                                    LapakGimID <strong>MUTLAK TIDAK BERTANGGUNG JAWAB</strong> atas kejadian <strong>Hack Back</strong> (pengambilan kembali akun oleh pemilik asli), <i>banned</i>, atau masalah teknis lainnya yang terjadi setelah transaksi selesai. Ketentuan ini berlaku <strong>BAIK</strong> untuk transaksi langsung <strong>MAUPUN</strong> transaksi yang menggunakan Jasa Rekber kami.
                                </p>
                            </div>
                        </div>
                    </div>
                    <p>
                        Kami hanya bertindak sebagai penyedia lapak. Layanan <strong className="text-base-content">Rekber (Rekening Bersama)</strong> kami HANYA berfungsi untuk mengamankan proses serah terima uang dan data akun saat transaksi berlangsung. <strong className="text-base-content">Rekber BUKAN merupakan garansi atau asuransi terhadap keamanan akun di masa depan.</strong> Segala risiko pasca-transaksi sepenuhnya menjadi tanggung jawab pembeli.
                    </p>
                </Section>

                <Section number="4" title="Fungsi Jasa Rekber" icon={HandCoins}>
                    <p>
                        Untuk menjamin keamanan <strong className="text-base-content">proses transaksi</strong> (serah terima), kami menyediakan layanan Jasa Rekber.
                    </p>
                    <div className="grid sm:grid-cols-2 gap-4 mt-4">
                        <div className="bg-base-200 p-5 rounded-xl border border-base-300 hover:border-yellow-500/40 transition-colors">
                            <h5 className="font-bold text-base-content mb-2">🔒 Lingkup Keamanan</h5>
                            <p className="text-sm">Rekber hanya menjamin <strong>DANA</strong> aman sampai pembeli menerima data akun yang valid saat transaksi.</p>
                        </div>
                        <div className="bg-base-200 p-5 rounded-xl border border-base-300 hover:border-yellow-500/40 transition-colors">
                            <h5 className="font-bold text-base-content mb-2">🚫 Bukan Garansi Akun</h5>
                            <p className="text-sm">Rekber <strong>TIDAK MENJAMIN</strong> akun anti-hackback. Tugas Rekber selesai setelah transaksi tuntas.</p>
                        </div>
                    </div>
                    <p className="mt-4">
                        Kami sangat menyarankan penggunaan Rekber untuk menghindari penipuan saat transaksi (uang dibawa lari), namun harap diingat bahwa risiko kualitas akun (hackback) tetap ada dan di luar tanggung jawab kami.
                    </p>
                </Section>

                <Section number="5" title="Kewajiban Pengguna" icon={UserCheck}>
                    <ul className="space-y-4">
                        <li className="bg-base-200 p-4 rounded-xl border border-dashed border-base-300">
                            <strong className="text-base-content block mb-1">Bagi Pembeli:</strong>
                            Wajib membaca deskripsi akun dengan teliti sebelum membeli. Wajib segera mengamankan akun (mengganti password, verifikasi 2FA) setelah menerima data.
                        </li>
                        <li className="bg-base-200 p-4 rounded-xl border border-dashed border-base-300">
                            <strong className="text-base-content block mb-1">Bagi Pengguna Umum:</strong>
                            Dilarang melakukan tindakan yang merugikan platform, seperti spam, hacking, atau penyalahgunaan fitur.
                        </li>
                    </ul>
                </Section>

                <div className="pt-8 border-t border-base-300 text-center">
                    <p className="text-sm text-base-content/50">
                        Kami berhak untuk mengubah syarat dan ketentuan ini sewaktu-waktu tanpa pemberitahuan sebelumnya. Pengguna diharapkan untuk memeriksa halaman ini secara berkala.
                    </p>
                </div>
            </div>
        </LegalLayout>
    );
}
