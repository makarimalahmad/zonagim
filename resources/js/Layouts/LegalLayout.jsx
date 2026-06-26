import { Link, Head } from "@inertiajs/react";
import { ArrowLeft } from "lucide-react";

export default function LegalLayout({ title, children }) {
    return (
        <div className="min-h-screen bg-base-200 text-base-content font-sans">
            <Head title={title} />

            {/* Navbar */}
            <nav className="fixed w-full z-50 top-0 border-b border-base-300 bg-base-100">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center justify-between h-20">
                        <Link href="/" className="flex items-center gap-3 group">
                            <div className="w-10 h-10 rounded-lg bg-base-200 flex items-center justify-center group-hover:bg-base-300 transition-colors">
                                <img src="/images/lapakakunid.png" alt="Logo" className="w-8 h-8 object-contain" />
                            </div>
                            <span className="text-xl font-bold text-base-content group-hover:text-yellow-500 transition-colors">
                                LapakAkunID
                            </span>
                        </Link>

                        <Link href="/" className="flex items-center gap-2 text-sm font-medium text-base-content/60 hover:text-base-content transition-colors">
                            <ArrowLeft size={16} />
                            Kembali ke Beranda
                        </Link>
                    </div>
                </div>
            </nav>

            {/* Content */}
            <main className="pt-32 pb-24">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="bg-base-100 border border-base-300 rounded-3xl p-8 sm:p-12 shadow-sm">
                        <h1 className="text-3xl sm:text-4xl font-extrabold text-base-content mb-2">{title}</h1>
                        <p className="text-base-content/50 mb-10">Terakhir diperbarui: {new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })}</p>

                        <div className="max-w-none">
                            {children}
                        </div>
                    </div>
                </div>
            </main>

            {/* Footer */}
            <footer className="border-t border-base-300 bg-base-100 py-12 text-sm">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-base-content/50">
                    <p>&copy; {new Date().getFullYear()} LapakAkunID. All rights reserved.</p>
                </div>
            </footer>
        </div>
    );
}
