import LegalLayout from '@/Layouts/LegalLayout';
import { Lock, Eye, Database, Cookie, Mail } from 'lucide-react';

export default function Privacy() {
    const Section = ({ title, icon: Icon, children }) => (
        <div className="mb-12 relative pl-8 sm:pl-12">
            <div className="absolute left-0 top-1 flex flex-col items-center h-full">
                <div className="w-8 h-8 rounded-full bg-base-300 text-base-content/70 flex items-center justify-center border border-base-300">
                    <Icon size={14} />
                </div>
                <div className="w-px h-full bg-base-300 my-2 last:hidden"></div>
            </div>

            <h3 className="text-xl sm:text-2xl font-bold text-base-content mb-6 flex items-center gap-3">
                {title}
            </h3>

            <div className="text-base-content/70 leading-relaxed text-lg space-y-4">
                {children}
            </div>
        </div>
    );

    return (
        <LegalLayout title="Kebijakan Privasi">
            <div className="mt-8">
                <Section title="Informasi yang Kami Kumpulkan" icon={Database}>
                    <p>
                        LapakAkunID menghargai privasi Anda. Informasi yang kami kumpulkan terbatas pada data yang diperlukan untuk menyediakan layanan kami:
                    </p>
                    <div className="grid gap-4 mt-4 sm:grid-cols-2">
                        <div className="bg-base-200 p-5 rounded-xl border border-base-300">
                            <h4 className="font-bold text-base-content mb-2 text-base">👤 Data Pendaftaran</h4>
                            <p className="text-sm">Nama, alamat email, dan password (terenkripsi) saat Anda mendaftar.</p>
                        </div>
                        <div className="bg-base-200 p-5 rounded-xl border border-base-300">
                            <h4 className="font-bold text-base-content mb-2 text-base">💳 Data Transaksi</h4>
                            <p className="text-sm">Riwayat pembelian dan detail pesanan untuk keperluan arsip.</p>
                        </div>
                        <div className="bg-base-200 p-5 rounded-xl border border-base-300 sm:col-span-2">
                            <h4 className="font-bold text-base-content mb-2 text-base">💻 Log Teknis</h4>
                            <p className="text-sm">Alamat IP dan jenis perangkat untuk keamanan sistem & pencegahan penipuan.</p>
                        </div>
                    </div>
                </Section>

                <Section title="Penggunaan Informasi" icon={Eye}>
                    <p>
                        Kami menggunakan data Anda hanya untuk kepentingan operasional, meliputi:
                    </p>
                    <ul className="space-y-3 mt-2 list-none">
                        {[
                            "Memproses transaksi dan mengelola akun Anda.",
                            "Menyediakan layanan pelanggan dan dukungan teknis.",
                            "Mengirimkan notifikasi penting (kode OTP, reset password).",
                            "Meningkatkan kualitas layanan dan keamanan platform."
                        ].map((item, i) => (
                            <li key={i} className="flex items-start gap-3">
                                <span className="w-1.5 h-1.5 rounded-full bg-yellow-500 mt-2.5 flex-shrink-0"/>
                                <span>{item}</span>
                            </li>
                        ))}
                    </ul>
                </Section>

                <Section title="Perlindungan Data" icon={Lock}>
                    <p>
                        Kami menerapkan langkah-langkah keamanan standar industri untuk melindungi data pribadi Anda.
                    </p>
                    <div className="bg-success/10 border border-success/20 p-5 rounded-xl flex items-start gap-4 my-4">
                        <Lock className="w-6 h-6 text-success flex-shrink-0 mt-1" />
                        <div>
                            <strong className="text-success block mb-1">Enkripsi Tingkat Tinggi</strong>
                            <p className="text-base-content/70 text-sm">Password pengguna disimpan dalam bentuk hash (terenkripsi) dan tidak dapat dibaca oleh siapa pun, termasuk admin.</p>
                        </div>
                    </div>
                    <p>
                        Kami <strong className="text-base-content">TIDAK AKAN</strong> pernah menjual, menyewakan, atau memberikan data pribadi Anda kepada pihak ketiga untuk tujuan pemasaran tanpa persetujuan Anda.
                    </p>
                </Section>

                <Section title="Cookies & Pihak Ketiga" icon={Cookie}>
                    <p>
                        Website kami menggunakan cookies untuk menyimpan preferensi sesi login Anda agar Anda tidak perlu login berulang kali.
                    </p>
                    <p>
                        Kami juga mungkin menggunakan layanan pihak ketiga (seperti payment gateway atau analitik) yang memiliki kebijakan privasi mereka sendiri.
                    </p>
                </Section>

                <Section title="Hubungi Kami" icon={Mail}>
                    <p>
                        Jika Anda memiliki pertanyaan tentang bagaimana kami mengelola data privasi Anda, jangan ragu untuk menghubungi kami melalui kontak yang tersedia di website.
                    </p>
                </Section>
            </div>
        </LegalLayout>
    );
}
