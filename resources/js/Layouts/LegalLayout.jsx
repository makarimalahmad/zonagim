import { Link, Head } from "@inertiajs/react";
import { ArrowLeft } from "lucide-react";
import ProgressiveImage from "@/Components/ProgressiveImage";
import Footer from "@/Components/Footer";

export default function LegalLayout({ title, children }) {
    return (
        <div className="min-h-screen bg-base-100 text-base-content font-sans">
            <Head title={title} />

            <nav className="border-b border-base-300">
                <div className="max-w-5xl mx-auto px-5 sm:px-8">
                    <div className="flex items-center justify-between h-20">
                        <Link href="/" className="flex items-center gap-3 group">
                            <ProgressiveImage
                                src="/images/zonagim.png"
                                alt="Logo Zonagim"
                                width={40}
                                height={40}
                                loading="eager"
                                fetchPriority="high"
                                wrapperClassName="w-10 h-10 shrink-0"
                                className="object-contain"
                            />
                            <span className="text-xl font-bold text-base-content group-hover:text-yellow-500 transition-colors">
                                Zonagim
                            </span>
                        </Link>

                        <Link href="/" className="flex items-center gap-2 text-sm font-medium text-base-content/60 hover:text-base-content transition-colors">
                            <ArrowLeft size={16} />
                            <span className="hidden sm:inline">Kembali ke Beranda</span>
                            <span className="sm:hidden">Kembali</span>
                        </Link>
                    </div>
                </div>
            </nav>

            <main className="max-w-5xl mx-auto px-5 py-14 sm:px-8 sm:py-20">
                <header className="pb-10 border-b border-base-300 text-center sm:pb-12">
                    <h1 className="text-3xl sm:text-4xl font-bold tracking-tight text-base-content">
                        {title}
                    </h1>
                </header>

                <div className="max-w-4xl mx-auto">
                    {children}
                </div>
            </main>

            <Footer />
        </div>
    );
}
