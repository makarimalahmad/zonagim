import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import GuestLayout from "@/Layouts/GuestLayout";
import { usePage, Link, Head } from "@inertiajs/react";
import { useState } from "react";
import { motion } from "framer-motion";
import {
    ArrowLeft,
    MessageCircle,
    ShieldCheck,
    User,
} from "lucide-react";

export default function Show({ product }) {
    // 🛡️ GUARD: JANGAN CRASH
    if (!product) {
        return (
            <div className="min-h-screen flex items-center justify-center text-base-content/50">
                Produk tidak ditemukan.
            </div>
        );
    }

    const { auth } = usePage().props;
    const Layout = auth?.user ? AuthenticatedLayout : GuestLayout;

    // Logic layoutProps sama seperti Game.jsx agar konsisten
    const layoutProps = auth?.user ? {} : { withNavbar: true };

    const images = product.images ?? [];
    const activeImage = images[0] || null;
    const [currentImage, setCurrentImage] = useState(activeImage);

    return (
        <Layout {...layoutProps}>
            <Head title={product.title} />
            <div className="min-h-screen -mt-6 px-6 md:px-12 py-8 pt-4 md:pt-8 transition-colors duration-300 relative">
                <div className="max-w-7xl mx-auto relative z-10">
                    {/* BREADCRUMB / BACK BUTTON */}
                    <motion.div
                        initial={{ opacity: 0, x: -10 }}
                        animate={{ opacity: 1, x: 0 }}
                        className="mb-8"
                    >
                        <Link
                            href={route("market.category", product.slug)}
                            className="inline-flex items-center gap-2 text-base-content/60 hover:text-yellow-500 transition-colors font-medium group"
                        >
                            <ArrowLeft className="w-5 h-5 group-hover:-translate-x-1 transition-transform" />
                            Kembali
                        </Link>
                    </motion.div>

                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-10">
                        {/* ================= GALLERY (Left - 7 cols) ================= */}
                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.5 }}
                            className="lg:col-span-7 flex flex-col gap-4"
                        >
                            {/* Main Image */}
                            <div className="relative aspect-video w-full rounded-2xl overflow-hidden bg-base-200 border border-base-300 shadow-sm group">
                                {currentImage ? (
                                    <img
                                        src={currentImage}
                                        alt={product.title}
                                        className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                    />
                                ) : (
                                    <div className="w-full h-full flex items-center justify-center text-base-content/20">
                                        No Image
                                    </div>
                                )}
                            </div>

                            {/* Thumbnails */}
                            {images.length > 1 && (
                                <div className="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
                                    {images.map((img, i) => (
                                        <button
                                            key={i}
                                            onClick={() => setCurrentImage(img)}
                                            className={`relative w-20 h-20 flex-shrink-0 rounded-2xl overflow-hidden border-2 transition-all duration-300 ${
                                                currentImage === img
                                                    ? "border-yellow-500 scale-95 shadow-sm"
                                                    : "border-transparent opacity-60 hover:opacity-100 hover:scale-105"
                                            }`}
                                        >
                                            <img
                                                src={img}
                                                className="w-full h-full object-cover"
                                            />
                                        </button>
                                    ))}
                                </div>
                            )}
                        </motion.div>

                        {/* ================= DETAIL INFO (Right - 5 cols) ================= */}
                        <motion.div
                            initial={{ opacity: 0, x: 20 }}
                            animate={{ opacity: 1, x: 0 }}
                            transition={{ duration: 0.5, delay: 0.2 }}
                            className="lg:col-span-5 space-y-8 pl-0 lg:pl-6"
                        >
                            {/* HEADER: Title & Price */}
                            <div className="">
                                <div className="flex items-center gap-2 mb-3">
                                    <span className="badge badge-lg border-base-content/10 bg-base-200/50 text-base-content/70">
                                        {product.category}
                                    </span>
                                    <span className="badge badge-lg badge-warning text-yellow-950 font-bold border-none">
                                        Terverifikasi
                                    </span>
                                </div>
                                <div className="mb-1">
                                    <span className="text-xs font-bold text-base-content/40 uppercase tracking-widest">
                                        Nickname Akun
                                    </span>
                                </div>
                                <h1 className="text-3xl md:text-5xl font-black text-base-content mb-4 leading-tight tracking-tight">
                                    {product.title}
                                </h1>
                                <div className="flex items-baseline gap-2">
                                    <h2 className="text-4xl md:text-5xl font-black text-yellow-500 tracking-tight">
                                        Rp{" "}
                                        {Number(product.price).toLocaleString(
                                            "id-ID",
                                        )}
                                    </h2>
                                </div>
                            </div>

                            <div className="divider my-0"></div>

                            {/* DESCRIPTION */}
                            <div>
                                <h3 className="text-xs font-bold text-base-content/40 uppercase tracking-widest mb-3">
                                    Deskripsi Akun
                                </h3>
                                <div className="prose prose-base max-w-none text-base-content/80 leading-relaxed whitespace-pre-line font-light">
                                    {product.description}
                                </div>
                            </div>

                            <div className="divider my-0"></div>

                            {/* SELLER CARD */}
                            <div className="">
                                <h3 className="text-xs font-bold text-base-content/40 uppercase tracking-widest mb-3">
                                    Penjual Akun
                                </h3>
                                <div className="flex items-center gap-4 mb-6">
                                    <div className="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center text-base-content/30 border border-base-content/5 overflow-hidden">
                                        <User className="w-8 h-8" />
                                    </div>
                                    <div>
                                        <h3 className="font-bold text-xl text-base-content mb-1">
                                            {product.seller_name}
                                        </h3>
                                    </div>
                                </div>

                                <a
                                    href={`https://wa.me/${product.seller_whatsapp}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="btn btn-lg bg-yellow-500 hover:bg-yellow-600 text-black w-full border-none shadow-sm transition-all transform hover:-translate-y-1 rounded-2xl font-bold mb-8"
                                >
                                    <MessageCircle className="w-6 h-6 mr-1" />
                                    Chat Penjual Sekarang
                                </a>

                                {/* REKBER ALERT */}
                                <div className="bg-base-200 rounded-2xl p-5 border border-base-300">
                                    <div className="flex items-start gap-4">
                                        <div className="bg-info/10 p-2 rounded-xl text-info">
                                            <ShieldCheck className="w-6 h-6" />
                                        </div>
                                        <div className="flex-1">
                                            <h4 className="font-bold text-base-content mb-1">
                                                Transaksi Lebih Aman
                                            </h4>
                                            <p className="text-sm text-base-content/60 leading-relaxed mb-4">
                                                Disarankan menggunakan jasa{" "}
                                                <b>
                                                    Rekber (Rekening Bersama)
                                                    Admin
                                                </b>{" "}
                                                untuk menjamin keamanan uang &
                                                akun Anda 100%.
                                            </p>
                                            <a
                                                href="https://wa.me/6281234567890" // Placeholder admin WA
                                                target="_blank"
                                                className="btn btn-md w-full bg-base-100 hover:bg-base-200 text-base-content border border-base-300 shadow-sm font-medium gap-2 rounded-xl"
                                            >
                                                <MessageCircle className="w-5 h-5 text-success" />
                                                Hubungi Admin (Rekber)
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* SAFETY TIPS */}
                        </motion.div>
                    </div>
                </div>
            </div>
        </Layout>
    );
}
