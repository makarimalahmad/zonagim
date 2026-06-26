import { Link } from "@inertiajs/react";
import ChatWidget from "@/Components/ChatWidget";
import FlashToaster from "@/Components/FlashToaster";
import Footer from "@/Components/Footer";
import ThemeToggle from "@/Components/ThemeToggle";

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
                                className="flex items-center gap-2 sm:gap-3 flex-shrink-0"
                            >
                                <img
                                    src="/images/lapakgimid.png"
                                    alt="LapakGimID"
                                    className="w-8 h-8 sm:w-10 sm:h-10"
                                />
                                <span className="text-lg sm:text-xl font-bold text-yellow-500 hidden xs:inline">
                                    LapakGimID
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

                <main className="p-6 max-w-7xl mx-auto flex-grow">
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
            <div className="min-h-screen grid grid-cols-1 lg:grid-cols-2 bg-base-200">
                {/* LEFT BRANDING */}
                <div className="hidden lg:flex flex-col justify-center items-center bg-base-100 border-r border-base-300">
                    {/* Content */}
                    <div className="flex flex-col items-center text-center px-10">
                        <Link href="/">
                            <img
                                src="/images/lapakgimid.png"
                                alt="LapakGimID"
                                className="w-52 mb-8 mx-auto"
                            />

                            <h1 className="text-3xl font-extrabold text-yellow-500 tracking-wide mb-3">
                                LapakGimID
                            </h1>
                        </Link>

                        <p className="text-base-content/70 max-w-sm leading-relaxed">
                            Marketplace akun game terpercaya. Transaksi lebih
                            aman dengan opsi jasa rekber.
                        </p>

                        <span className="mt-10 text-xs text-base-content/50">
                            © {new Date().getFullYear()} LapakGimID
                        </span>
                    </div>
                </div>

                {/* RIGHT FORM */}
                <div className="flex items-center justify-center px-6 py-12">
                    <div className="w-full max-w-md bg-base-100 border border-base-300 rounded-2xl shadow-sm p-8">
                        {/* Mobile logo */}
                        <div className="flex flex-col items-center mb-6 lg:hidden">
                            <Link href="/">
                                <img
                                    src="/images/lapakgimid.png"
                                    alt="LapakGimID"
                                    className="w-32 mb-3 mx-auto"
                                />
                                <h1 className="text-xl font-bold text-yellow-500 text-center">
                                    LapakGimID
                                </h1>
                            </Link>
                        </div>

                        {children}
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
