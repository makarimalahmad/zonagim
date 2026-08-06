import { Link } from "@inertiajs/react";
import ChatWidget from "@/Components/ChatWidget";
import FlashToaster from "@/Components/FlashToaster";
import Footer from "@/Components/Footer";
import ThemeToggle from "@/Components/ThemeToggle";
import ProgressiveImage from "@/Components/ProgressiveImage";

export default function GuestLayout({ children, withNavbar = false }) {
    // Kalau withNavbar true, pakai layout dengan navbar (untuk market)
    if (withNavbar) {
        return (
            <div className="min-h-screen bg-base-200 flex flex-col">
                {/* Navbar untuk Guest - Mobile Responsive */}
                <nav className="bg-base-100 border-b border-base-300 shadow-sm sticky top-0 z-50">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 py-3 sm:py-4">
                        <div className="flex items-center justify-between gap-2">
                            {/* Logo & Brand */}
                            <Link
                                href="/"
                                className="group flex items-center gap-2 sm:gap-3 shrink-0"
                            >
                                <ProgressiveImage
                                    src="/images/zonagim-96.webp"
                                    alt="Logo Zonagim"
                                    width={40}
                                    height={40}
                                    loading="eager"
                                    fetchPriority="high"
                                    wrapperClassName="w-8 h-8 sm:w-10 sm:h-10 shrink-0"
                                    className="object-contain"
                                />
                                <span className="hidden text-lg font-bold text-base-content transition-colors group-hover:text-yellow-500 xs:inline sm:text-xl">
                                    Zonagim
                                </span>
                            </Link>

                            {/* Auth Buttons */}
                            <div className="flex items-center gap-2 sm:gap-3">
                                <ThemeToggle />
                                <Link
                                    href={route("login")}
                                    className="px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base text-base-content hover:text-yellow-500 transition font-medium"
                                >
                                    Login
                                </Link>
                                <Link
                                    href={route("register")}
                                    className="px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base bg-yellow-500 text-[#0B1221] font-semibold rounded-lg hover:bg-yellow-400 transition"
                                >
                                    Register
                                </Link>
                            </div>
                        </div>
                    </div>
                </nav>

                <main className="w-full p-6 max-w-7xl mx-auto grow">
                    {children}
                </main>
                <Footer />
                <ChatWidget />
                <FlashToaster />
            </div>
        );
    }

    // Layout default untuk auth (Login/Register) - 2 kolom
    return (
        <>
            <div className="fixed inset-0 overflow-hidden bg-base-200">
                {/* LEFT BRANDING */}
                <div className="fixed inset-y-0 left-0 hidden w-1/2 flex-col items-center justify-center border-r border-base-300 bg-base-100 lg:flex">
                    {/* Content */}
                    <div className="flex flex-col items-center text-center px-10">
                        <Link href="/">
                            <ProgressiveImage
                                src="/images/zonagim-nobg.png"
                                alt="Logo Zonagim"
                                width={208}
                                height={208}
                                loading="eager"
                                fetchPriority="high"
                                wrapperClassName="w-52 h-52 mb-8 mx-auto"
                                className="object-contain"
                            />

                            <h1 className="text-3xl font-extrabold text-yellow-500 tracking-wide mb-3">
                                Zonagim
                            </h1>
                        </Link>

                        <p className="text-base-content/70 max-w-sm leading-relaxed">
                            Marketplace jual beli akun game dengan opsi jasa
                            rekber.
                        </p>

                        <span className="mt-10 text-xs text-base-content/50">
                            © {new Date().getFullYear()} Zonagim
                        </span>
                    </div>
                </div>

                {/* RIGHT FORM */}
                <div
                    data-lenis-prevent
                    className="ml-auto h-dvh w-full overflow-x-hidden overflow-y-auto overscroll-contain px-6 lg:w-1/2"
                >
                    <div className="flex min-h-full items-center justify-center py-12">
                        <div className="w-full max-w-md bg-base-100 border border-base-300 rounded-2xl shadow-sm p-8">
                        {/* Mobile logo */}
                        <div className="flex flex-col items-center mb-6 lg:hidden">
                            <Link href="/">
                                <ProgressiveImage
                                    src="/images/zonagim-nobg.png"
                                    alt="Logo Zonagim"
                                    width={128}
                                    height={128}
                                    loading="eager"
                                    fetchPriority="high"
                                    wrapperClassName="w-32 h-32 mb-3 mx-auto"
                                    className="object-contain"
                                />
                                <h1 className="text-xl font-bold text-yellow-500 text-center">
                                    Zonagim
                                </h1>
                            </Link>
                        </div>

                            {children}
                        </div>
                    </div>
                </div>
            </div>
            {![
                "login",
                "register",
                "password.request",
                "password.reset",
            ].includes(route().current()) && <ChatWidget />}
            <FlashToaster />
        </>
    );
}
