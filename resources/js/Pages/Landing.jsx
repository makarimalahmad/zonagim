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
} from "lucide-react";
import ChatWidget from "@/Components/ChatWidget";
import ThemeToggle from "@/Components/ThemeToggle";
import ProgressiveImage from "@/Components/ProgressiveImage";
import Footer from "@/Components/Footer";

gsap.registerPlugin(ScrollTrigger);

export default function Landing({ contactUrl = null }) {
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

        const updateLenis = (time) => lenis.raf(time * 1000);

        lenis.on("scroll", ScrollTrigger.update);
        gsap.ticker.add(updateLenis);
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

        return () => {
            ctx.revert();
            gsap.ticker.remove(updateLenis);
            lenis.destroy();
        };
    }, []);

    const popularGames = [
        ["Counter-Strike 2", "counter-strike-2.png"],
        ["PUBG: Battlegrounds", "pubg-battlegrounds.png"],
        ["Dota 2", "dota-2.png"],
        ["Apex Legends", "apex-legends.png"],
        ["Grand Theft Auto V", "grand-theft-auto-v.png"],
        ["War Thunder", "war-thunder.png"],
        ["Rainbow Six Siege", "rainbow-six-siege.png"],
        ["ARC Raiders", "arc-raiders.png"],
        ["Delta Force", "delta-force.png"],
        ["Overwatch 2", "overwatch-2.png"],
        ["Marvel Rivals", "marvel-rivals.png"],
        ["Rust", "rust.png"],
        ["Dead by Daylight", "dead-by-daylight.png"],
        ["Deadlock", "deadlock.png"],
        ["Warframe", "warframe.png"],
        ["Valorant", "valorant.png"],
        ["Fortnite", "fortnite.svg"],
        ["League of Legends", "league-of-legends.svg"],
        ["Minecraft", "minecraft.svg"],
        ["Roblox", "roblox.png"],
        ["Mobile Legends", "mobile-legends.svg"],
        ["Free Fire", "free-fire.png"],
        ["Genshin Impact", "genshin-impact.png"],
        ["Call of Duty: Warzone", "call-of-duty-warzone.png"],
    ].map(([name, file]) => ({
        name,
        src: `/images/games/${file}`,
    }));

    const logoRows = Array.from({ length: 4 }, (_, rowIndex) =>
        popularGames.slice(rowIndex * 6, rowIndex * 6 + 6),
    );

    const features = [
        {
            icon: ShieldCheck,
            title: "Opsi Rekber",
            desc: "Zonagim membantu alur serah terima dana dan data akun selama transaksi berlangsung.",
            accent: "text-warning bg-warning/10",
        },
        {
            icon: Zap,
            title: "Marketplace P2P",
            desc: "Penjual menawarkan akun dan pembeli memilih listing berdasarkan informasi yang tersedia.",
            accent: "text-info bg-info/10",
        },
        {
            icon: Users,
            title: "Penjual Asli",
            desc: "Kredensial akun dikirim langsung oleh penjual kepada pembeli, bukan disimpan oleh Zonagim.",
            accent: "text-secondary bg-secondary/10",
        },
    ];

    const steps = [
        {
            num: "1",
            title: "Daftar Akun",
            desc: "Buat akun dengan menggunakan email aktif.",
            active: false,
        },
        {
            num: "2",
            title: "Pilih Akun",
            desc: "Cari akun berdasarkan game dan informasi listing yang tersedia.",
            active: true,
        },
        {
            num: "3",
            title: "Bayar & Terima Akun",
            desc: "Lakukan pembayaran, lalu ikuti tahapan penyerahan data akun.",
            active: false,
        },
    ];

    return (
        <div
            ref={containerRef}
            className="bg-base-200 text-base-content font-sans overflow-x-hidden"
        >
            <Head title="Marketplace Jual Beli Akun Game" />

            {/* Navbar */}
            <nav className="fixed w-full z-50 top-0 border-b border-base-300 bg-base-100">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center justify-between h-20">
                        {/* Logo */}
                        <Link
                            href="/"
                            className="group flex shrink-0 items-center gap-3"
                        >
                            <ProgressiveImage
                                src="/images/zonagim.png"
                                alt="Logo Zonagim"
                                width={40}
                                height={40}
                                loading="eager"
                                fetchPriority="high"
                                wrapperClassName="w-9 h-9 sm:w-10 sm:h-10 shrink-0"
                                className="object-contain"
                            />
                            <span className="text-lg font-bold text-base-content transition-colors group-hover:text-yellow-500 sm:text-xl">
                                Zonagim
                            </span>
                        </Link>

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
                    <div className="mx-4 bg-base-100 border border-base-300 rounded-2xl overflow-hidden">
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
                                <span>dengan Sistem Rekber</span>
                            </h1>
                            <p className="text-lg text-base-content/60 mb-8 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                                Marketplace P2P akun game yang mempertemukan
                                penjual dan pembeli. Gunakan opsi Rekber Zonagim
                                sebagai perantara alur transaksi.
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

                        </div>

                        {/* Animated game logo wall */}
                        <div className="hero-image relative hidden lg:block">
                            <div className="game-logo-wall relative w-full overflow-hidden rounded-4xl border border-base-300 py-7">
                                <div className="game-logo-fade space-y-3.5">
                                    {logoRows.map((row, rowIndex) => (
                                        <div
                                            key={rowIndex}
                                            className="overflow-hidden py-0.5"
                                        >
                                            <div
                                                className="marquee-track flex"
                                                style={{
                                                    animation: `${
                                                        rowIndex % 2 === 0
                                                            ? "marquee-ltr"
                                                            : "marquee-rtl"
                                                    } ${
                                                        30 + rowIndex * 5
                                                    }s linear infinite`,
                                                }}
                                            >
                                                {[...row, ...row].map(
                                                    (game, gameIndex) => (
                                                        <Link
                                                            key={`${rowIndex}-${gameIndex}`}
                                                            href="/market"
                                                            title={game.name}
                                                            aria-label={`Lihat akun ${game.name}`}
                                                            className="game-logo-tile group relative mr-3 flex h-[4.35rem] w-28 shrink-0 items-center justify-center overflow-hidden rounded-2xl border px-3 outline-none"
                                                        >
                                                            <ProgressiveImage
                                                                src={game.src}
                                                                alt=""
                                                                width={96}
                                                                height={48}
                                                                loading="eager"
                                                                fetchPriority="low"
                                                                wrapperClassName="h-12 w-full bg-transparent"
                                                                className="game-logo-image object-contain p-1"
                                                                fallback={
                                                                    <Gamepad2
                                                                        className="h-6 w-6"
                                                                        aria-hidden="true"
                                                                    />
                                                                }
                                                            />
                                                        </Link>
                                                    ),
                                                )}
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
                            Kenapa Harus Zonagim?
                        </h2>
                        <p className="text-base-content/60 text-lg">
                            Zonagim menyediakan fitur pencarian akun, pembayaran
                            melalui Rekber, dan penyerahan data akun.
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
                            Proses transaksi terdiri dari tiga langkah utama.
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
                            Butuh Jasa Rekber atau Mau Titip Jual Akun Game Kamu?
                        </h2>
                        <p className="text-base-content/60 text-lg font-medium mb-10 max-w-2xl mx-auto relative z-10">
                            Gunakan layanan Rekber untuk membantu alur pembayaran
                            dan penyerahan akun, atau titip jual akun game kamu di
                            Zonagim.
                        </p>
                        {contactUrl && (
                            <a
                                href={contactUrl}
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
                        )}
                    </div>
                </div>
            </section>

            <Footer />
            <ChatWidget />
        </div>
    );
}
