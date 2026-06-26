import { Link, Head } from "@inertiajs/react";
import { useLayoutEffect, useRef, useState } from "react";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import Lenis from "lenis";
import {
    ShieldCheck,
    Zap,
    Users,
    ChevronRight,
    Gamepad2,
    Search,
    Menu,
    X,
    Star,
} from "lucide-react";
import ChatWidget from "@/Components/ChatWidget";
import ThemeToggle from "@/Components/ThemeToggle";

gsap.registerPlugin(ScrollTrigger);

export default function Landing({ gameLogos = [] }) {
    const containerRef = useRef(null);
    const heroRef = useRef(null);
    const featuresRef = useRef(null);
    const stepsRef = useRef(null);
    const [isMenuOpen, setIsMenuOpen] = useState(false);

    useLayoutEffect(() => {
        // Smooth scrolling
        const lenis = new Lenis({
            duration: 1.2,
            easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
            smoothWheel: true,
        });

        lenis.on("scroll", ScrollTrigger.update);
        gsap.ticker.add((time) => lenis.raf(time * 1000));
        gsap.ticker.lagSmoothing(0);

        const ctx = gsap.context(() => {
            // Hero subtle parallax
            gsap.to(".hero-text", {
                yPercent: -10,
                ease: "none",
                scrollTrigger: {
                    trigger: heroRef.current,
                    start: "top top",
                    end: "bottom top",
                    scrub: 1,
                },
            });
            gsap.to(".hero-image", {
                yPercent: -5,
                ease: "none",
                scrollTrigger: {
                    trigger: heroRef.current,
                    start: "top top",
                    end: "bottom top",
                    scrub: 1,
                },
            });

            // Features: clean stagger reveal (no pinning)
            gsap.from(".feature-card", {
                y: 40,
                autoAlpha: 0,
                duration: 0.7,
                stagger: 0.15,
                ease: "power2.out",
                scrollTrigger: {
                    trigger: featuresRef.current,
                    start: "top 78%",
                },
            });

            // Steps: clean stagger reveal (no pinning)
            gsap.from(".step-item", {
                y: 40,
                autoAlpha: 0,
                duration: 0.7,
                stagger: 0.18,
                ease: "power2.out",
                scrollTrigger: {
                    trigger: stepsRef.current,
                    start: "top 78%",
                },
            });

            // CTA reveal
            gsap.from(".cta-card", {
                scale: 0.96,
                autoAlpha: 0,
                duration: 0.7,
                ease: "power2.out",
                scrollTrigger: {
                    trigger: ".cta-card",
                    start: "top 85%",
                },
            });
        }, containerRef);

        return () => ctx.revert();
    }, []);

    // Logo game dibaca otomatis dari folder public/images/games (prop gameLogos).
    // Fallback ke CDN bila folder kosong, agar wall tidak pernah kosong.
    const fallbackLogos = [
        { name: "Valorant", src: "https://cdn.simpleicons.org/valorant/64748b" },
        { name: "PUBG", src: "https://cdn.simpleicons.org/pubg/64748b" },
        { name: "Dota 2", src: "https://cdn.simpleicons.org/dota2/64748b" },
        {
            name: "League of Legends",
            src: "https://cdn.simpleicons.org/leagueoflegends/64748b",
        },
        {
            name: "Counter-Strike",
            src: "https://cdn.simpleicons.org/counterstrike/64748b",
        },
        { name: "Fortnite", src: "https://cdn.simpleicons.org/fortnite/64748b" },
    ];

    const logos = gameLogos && gameLogos.length ? gameLogos : fallbackLogos;

    const rotate = (arr, n) =>
        arr.length
            ? [...arr.slice(n % arr.length), ...arr.slice(0, n % arr.length)]
            : arr;

    // Beberapa baris dengan urutan berbeda agar wall terlihat dinamis
    const logoRows = [
        logos,
        rotate(logos, 2),
        rotate(logos, 4),
        rotate(logos, 1),
    ];

    const features = [
        {
            icon: ShieldCheck,
            title: "Rekber Otomatis",
            desc: "Dana pembeli ditahan oleh sistem hingga akun berhasil diamankan. Anti tipu-tipu club.",
            accent: "text-warning bg-warning/10",
        },
        {
            icon: Zap,
            title: "Transaksi Kilat",
            desc: "Sistem otomatis yang memproses pesanan detik itu juga. Tidak perlu menunggu admin bangun tidur.",
            accent: "text-info bg-info/10",
        },
        {
            icon: Users,
            title: "Komunitas Verified",
            desc: "Penjual wajib verifikasi identitas (KYC). Stok akun berkualitas dari seller terpercaya.",
            accent: "text-secondary bg-secondary/10",
        },
    ];

    const steps = [
        {
            num: "1",
            title: "Daftar Akun",
            desc: "Buat akun gratis dalam hitungan detik. Cukup gunakan email aktif.",
            active: false,
        },
        {
            num: "2",
            title: "Pilih Akun",
            desc: "Cari akun favoritmu dari ribuan katalog yang tersedia.",
            active: true,
        },
        {
            num: "3",
            title: "Bayar & Main",
            desc: "Lakukan pembayaran dan terima data akun secara instan.",
            active: false,
        },
    ];

    return (
        <div
            ref={containerRef}
            className="bg-base-200 text-base-content font-sans overflow-x-hidden"
        >
            <Head title="Jual Beli Akun Game Aman & Terpercaya" />

            {/* Navbar */}
            <nav className="fixed w-full z-50 top-0 border-b border-base-300 bg-base-100">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center justify-between h-20">
                        {/* Logo */}
                        <div className="flex-shrink-0 flex items-center gap-3">
                            <img
                                src="/images/lapakakunid.png"
                                alt="Logo"
                                className="w-9 h-9 sm:w-10 sm:h-10"
                            />
                            <span className="text-lg sm:text-xl font-bold text-base-content">
                                LapakAkunID
                            </span>
                        </div>

                        {/* Desktop Menu */}
                        <div className="hidden md:flex items-center gap-4">
                            <ThemeToggle />
                            <Link
                                href={route("login")}
                                className="text-sm font-medium text-base-content/70 hover:text-yellow-500 transition-colors px-2"
                            >
                                Masuk
                            </Link>
                            <Link
                                href={route("register")}
                                className="px-5 py-2.5 text-sm font-bold text-[#0B1221] bg-yellow-500 rounded-lg hover:bg-yellow-400 transition-colors"
                            >
                                Daftar Sekarang
                            </Link>
                        </div>

                        {/* Mobile actions */}
                        <div className="md:hidden flex items-center gap-2">
                            <ThemeToggle />
                            <button
                                onClick={() => setIsMenuOpen(!isMenuOpen)}
                                className="w-10 h-10 flex items-center justify-center rounded-lg bg-base-100 border border-base-300 text-base-content/70 hover:text-yellow-500 transition-colors"
                                aria-label="Toggle menu"
                            >
                                {isMenuOpen ? <X size={20} /> : <Menu size={20} />}
                            </button>
                        </div>
                    </div>
                </div>

                {/* Mobile Menu */}
                <div
                    className={`md:hidden absolute w-full left-0 top-20 transition-all duration-300 ease-out ${
                        isMenuOpen
                            ? "opacity-100 translate-y-0 pointer-events-auto"
                            : "opacity-0 -translate-y-4 pointer-events-none"
                    }`}
                >
                    <div className="mx-4 bg-base-100 border border-base-300 rounded-2xl shadow-lg overflow-hidden">
                        <div className="px-4 py-5 space-y-3">
                            <Link
                                href={route("login")}
                                className="flex items-center justify-center gap-2 w-full px-4 py-3.5 text-sm font-bold text-base-content bg-base-200 border border-base-300 rounded-xl hover:bg-base-300 transition-colors"
                            >
                                Masuk
                            </Link>
                            <Link
                                href={route("register")}
                                className="flex items-center justify-center gap-2 w-full px-4 py-3.5 text-sm font-bold text-[#0B1221] bg-yellow-500 rounded-xl hover:bg-yellow-400 transition-colors"
                            >
                                Daftar Sekarang
                            </Link>
                        </div>
                    </div>
                </div>
            </nav>

            {/* Hero Section */}
            <section
                ref={heroRef}
                className="relative min-h-screen flex items-center pt-28 pb-12 lg:pt-20 lg:pb-10 w-full"
            >
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                    <div className="grid lg:grid-cols-2 gap-10 lg:gap-12 items-center">
                        <div className="hero-text text-center lg:text-left w-full">
                            <h1 className="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight text-base-content mb-6">
                                Jual Beli{" "}
                                <span className="text-yellow-500">Akun Game</span>{" "}
                                <br />
                                <span className="relative inline-block">
                                    Lebih Aman
                                    <svg
                                        className="absolute w-full h-3 -bottom-1 left-0 text-yellow-500 opacity-60"
                                        viewBox="0 0 200 9"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            d="M2.00025 6.99999C25.3336 2.66666 84.6 0.599998 198 1.99999"
                                            stroke="currentColor"
                                            strokeWidth="3"
                                        />
                                    </svg>
                                </span>
                            </h1>
                            <p className="text-lg text-base-content/60 mb-8 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                                Marketplace terpercaya dengan sistem Rekber
                                otomatis. Jaminan uang kembali 100% jika akun
                                bermasalah. Gabung bersama 50.000+ gamers lainnya.
                            </p>
                            <div className="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                                <Link
                                    href={route("register")}
                                    className="group px-8 py-4 bg-yellow-500 text-[#0B1221] font-bold rounded-xl hover:bg-yellow-400 transition-colors flex items-center justify-center gap-2"
                                >
                                    <Gamepad2 className="w-5 h-5" />
                                    Daftar Sekarang
                                    <ChevronRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                                </Link>
                                <Link
                                    href="/market"
                                    className="px-8 py-4 bg-base-100 text-base-content border border-base-300 font-bold rounded-xl hover:bg-base-300 transition-colors flex items-center justify-center gap-2"
                                >
                                    <Search className="w-5 h-5" />
                                    Cari Akun
                                </Link>
                            </div>

                            {/* Stats */}
                            <div className="mt-10 flex items-center justify-center lg:justify-start gap-6 sm:gap-8 border-t border-base-300 pt-6">
                                <div>
                                    <p className="text-3xl font-bold text-base-content">
                                        50K+
                                    </p>
                                    <p className="text-base-content/50 text-sm">
                                        Pengguna Aktif
                                    </p>
                                </div>
                                <div className="w-px h-10 bg-base-300" />
                                <div>
                                    <p className="text-3xl font-bold text-base-content">
                                        100K+
                                    </p>
                                    <p className="text-base-content/50 text-sm">
                                        Transaksi Sukses
                                    </p>
                                </div>
                                <div className="w-px h-10 bg-base-300" />
                                <div>
                                    <p className="text-3xl font-bold text-base-content">
                                        4.9/5
                                    </p>
                                    <div className="flex text-yellow-500 gap-0.5 mt-1">
                                        {[...Array(5)].map((_, i) => (
                                            <Star
                                                key={i}
                                                size={12}
                                                fill="currentColor"
                                            />
                                        ))}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Hero Visual Card */}
                        <div className="relative hidden lg:block hero-image">
                            <div className="bg-base-100 border border-base-300 rounded-2xl p-6 w-full">
                                <div className="flex items-center gap-3 mb-5">
                                    <div className="w-11 h-11 rounded-xl bg-yellow-500/10 flex items-center justify-center text-yellow-500">
                                        <Gamepad2 size={22} />
                                    </div>
                                    <div className="text-base-content font-bold text-lg">
                                        Game Terpopuler
                                    </div>
                                </div>

                                {/* Logo Wall - Animated Marquee */}
                                <div className="space-y-3">
                                    {logoRows.map((row, rowIndex) => (
                                        <div
                                            key={rowIndex}
                                            className="overflow-hidden"
                                        >
                                            <div
                                                className="marquee-track flex gap-3"
                                                style={{
                                                    animation: `${
                                                        rowIndex % 2 === 0
                                                            ? "marquee-ltr"
                                                            : "marquee-rtl"
                                                    } ${34 + rowIndex * 5}s linear infinite`,
                                                }}
                                            >
                                                {[...row, ...row].map((game, i) => (
                                                    <div
                                                        key={i}
                                                        title={game.name}
                                                        className="flex-shrink-0 w-16 h-16 rounded-2xl bg-base-200 border border-base-300 flex items-center justify-center hover:border-yellow-500/40 transition-colors duration-300 group"
                                                    >
                                                        <img
                                                            src={game.src}
                                                            alt={game.name}
                                                            loading="lazy"
                                                            className="w-8 h-8 opacity-70 group-hover:opacity-100 transition-opacity"
                                                            onError={(e) => {
                                                                e.currentTarget.style.visibility =
                                                                    "hidden";
                                                            }}
                                                        />
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Features Section */}
            <section
                ref={featuresRef}
                className="py-20 lg:py-28 border-t border-base-300"
            >
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                    <div className="text-center max-w-2xl mx-auto mb-14">
                        <h2 className="text-3xl sm:text-4xl font-bold text-base-content mb-4">
                            Kenapa Harus LapakAkunID?
                        </h2>
                        <p className="text-base-content/60 text-lg">
                            Platform kami dirancang khusus untuk kenyamanan dan
                            keamanan transaksi para gamers.
                        </p>
                    </div>

                    <div className="grid md:grid-cols-3 gap-6">
                        {features.map((f) => (
                            <div
                                key={f.title}
                                className="feature-card group p-8 rounded-2xl bg-base-100 border border-base-300 hover:border-yellow-500/50 transition-colors duration-300"
                            >
                                <div
                                    className={`w-14 h-14 rounded-xl flex items-center justify-center mb-6 ${f.accent}`}
                                >
                                    <f.icon size={30} />
                                </div>
                                <h3 className="text-xl font-bold text-base-content mb-3">
                                    {f.title}
                                </h3>
                                <p className="text-base-content/60 leading-relaxed">
                                    {f.desc}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* How It Works Section */}
            <section
                ref={stepsRef}
                className="py-20 lg:py-28 border-t border-base-300"
            >
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                    <div className="text-center max-w-2xl mx-auto mb-14">
                        <h2 className="text-3xl sm:text-4xl font-bold text-base-content mb-4">
                            Cara Memulai
                        </h2>
                        <p className="text-base-content/60 text-lg">
                            Hanya butuh 3 langkah mudah untuk mendapatkan akun
                            impianmu.
                        </p>
                    </div>

                    <div className="grid md:grid-cols-3 gap-8 relative">
                        {/* Connecting Line (Desktop) */}
                        <div className="hidden md:block absolute top-12 left-[16%] right-[16%] h-px bg-base-300" />

                        {steps.map((s) => (
                            <div
                                key={s.num}
                                className="step-item text-center relative z-10"
                            >
                                <div
                                    className={`w-24 h-24 mx-auto rounded-full flex items-center justify-center mb-6 bg-base-100 border-2 ${
                                        s.active
                                            ? "border-yellow-500"
                                            : "border-base-300"
                                    }`}
                                >
                                    <span
                                        className={`text-3xl font-bold ${
                                            s.active
                                                ? "text-yellow-500"
                                                : "text-base-content/30"
                                        }`}
                                    >
                                        {s.num}
                                    </span>
                                </div>
                                <h3 className="text-xl font-bold text-base-content mb-2">
                                    {s.title}
                                </h3>
                                <p className="text-base-content/60 px-6">
                                    {s.desc}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* CTA Section */}
            <section className="py-20 lg:py-28 border-t border-base-300">
                <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="cta-card bg-base-100 border border-base-300 rounded-3xl p-10 sm:p-16 text-center relative overflow-hidden">
                        <div className="absolute top-0 right-0 p-16 text-base-content/5 transform translate-x-1/3 -translate-y-1/3">
                            <ShieldCheck size={280} />
                        </div>
                        <h2 className="text-3xl sm:text-4xl font-extrabold text-base-content mb-6 relative z-10">
                            Butuh Jasa Rekber Aman atau Mau Titip Jual Akun Game
                            Kamu?
                        </h2>
                        <p className="text-base-content/60 text-lg font-medium mb-10 max-w-2xl mx-auto relative z-10">
                            Jangan ambil risiko. Gunakan layanan Rekber kami untuk
                            menjamin transaksi 100% aman dan segera titip jual akun
                            game kamu di LapakAkunID.
                        </p>
                        <a
                            href="https://wa.me/6281234567890"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="relative z-10 inline-flex items-center gap-2 px-10 py-4 bg-yellow-500 text-[#0B1221] font-bold rounded-xl hover:bg-yellow-400 transition-colors"
                        >
                            <svg
                                className="w-6 h-6"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                            </svg>
                            Hubungi Admin (WhatsApp)
                        </a>
                    </div>
                </div>
            </section>

            {/* Footer */}
            <footer className="border-t border-base-300 bg-base-200 py-12 text-sm">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex flex-col md:flex-row justify-between items-center gap-6">
                        <div className="flex items-center gap-3">
                            <img
                                src="/images/lapakakunid.png"
                                alt="Logo"
                                className="w-8 h-8"
                            />
                            <span className="font-bold text-base-content">
                                LapakAkunID
                            </span>
                        </div>
                        <div className="text-base-content/50 text-center md:text-right">
                            <p>
                                &copy; {new Date().getFullYear()} LapakAkunID. All
                                rights reserved.
                            </p>
                            <div className="flex gap-4 justify-center md:justify-end mt-2">
                                <Link
                                    href={route("privacy")}
                                    className="hover:text-yellow-500 transition-colors"
                                >
                                    Privacy Policy
                                </Link>
                                <Link
                                    href={route("terms")}
                                    className="hover:text-yellow-500 transition-colors"
                                >
                                    Terms of Service
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            <ChatWidget />
        </div>
    );
}
