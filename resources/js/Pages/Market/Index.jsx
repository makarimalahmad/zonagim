import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import GuestLayout from "@/Layouts/GuestLayout";
import { Link, usePage, Head } from "@inertiajs/react";
import { motion, AnimatePresence } from "framer-motion";
import { Search, Gamepad2, ArrowRight } from "lucide-react";
import { useState } from "react";

export default function Index({ games }) {
    const { auth } = usePage().props;
    const Layout = auth?.user ? AuthenticatedLayout : GuestLayout;
    
    // Auth-based layout props; GuestLayout handles its own navbar
    const layoutProps = auth?.user ? {} : { withNavbar: true };

    // Search State
    const [query, setQuery] = useState("");

    // Filter games based on search query
    const filteredGames = games.filter((game) =>
        game.name.toLowerCase().includes(query.toLowerCase())
    );

    const containerVariants = {
        hidden: { opacity: 0 },
        visible: {
            opacity: 1,
            transition: {
                staggerChildren: 0.1,
            },
        },
    };

    const itemVariants = {
        hidden: { y: 20, opacity: 0 },
        visible: {
            y: 0,
            opacity: 1,
            transition: {
                type: "spring",
                stiffness: 100,
            },
        },
        exit: {
            opacity: 0,
            scale: 0.9,
            transition: { duration: 0.2 }
        }
    };

    return (
        <Layout {...layoutProps}>
            <Head title="Market" />
            {/* 
                Structure Changes for Theme Compatibility:
                - Use base-200/base-300 for backgrounds
                - Use base-content for text
                - Keep specific gradients only if they look good in both or use dark: modifier
             */}
            <div className="min-h-screen -mt-6 p-6 md:p-12 transition-colors duration-300 relative overflow-hidden">
                
                {/* HERO SECTION */}
                <motion.div 
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.8, ease: "easeOut" }}
                    className="max-w-7xl mx-auto mb-10 text-center relative pt-8 pb-2 z-10"
                >
                    
                    <h1 className="relative text-5xl md:text-7xl font-extrabold mb-6 tracking-tight">
                        <span className="text-base-content transition-colors duration-300">
                            Marketplace
                        </span>{" "}
                        <span className="text-yellow-500">
                            Akun Game
                        </span>
                    </h1>
                    <p className="relative text-lg md:text-xl text-base-content/70 max-w-2xl mx-auto mb-8 font-light leading-relaxed transition-colors duration-300">
                        Temukan akun game impianmu dengan harga terbaik dan transaksi aman.
                        Legal, terpercaya, dan bergaransi.
                    </p>

                    {/* SEARCH BAR (Functional) */}
                    <div className="relative max-w-xl mx-auto z-20 group">
                        <div className="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <Search className="h-5 w-5 text-base-content/40 group-focus-within:text-yellow-500 transition-colors" />
                        </div>
                        <input
                            type="text"
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            className="block w-full pl-12 pr-12 py-4 border border-base-300 rounded-full leading-5 bg-base-100 text-base-content placeholder-base-content/40 focus:outline-none focus:ring-2 focus:ring-yellow-500/50 focus:border-yellow-500 sm:text-base transition-all shadow-sm hover:border-base-content/20"
                            placeholder="Cari akun game (misal: Valorant, Genshin)..."
                        />
                    </div>
                </motion.div>

                {/* GAMES GRID */}
                <motion.div
                    variants={containerVariants}
                    initial="hidden"
                    animate="visible"
                    className="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
                >
                    <AnimatePresence mode="popLayout">
                        {filteredGames.map((game) => (
                            <motion.div 
                                key={game.id} 
                                variants={itemVariants}
                                layout
                                initial="hidden"
                                animate="visible"
                                exit="exit"
                            >
                                <Link
                                    href={`/market/${game.slug}`}
                                    className="group relative block h-full rounded-2xl overflow-hidden bg-base-100 border border-base-300 hover:border-yellow-500/50 hover:shadow-md transition-all duration-300 transform"
                                >
                                    {/* Image Container */}
                                    <div className="aspect-[16/9] w-full overflow-hidden relative">
                                        <div className="absolute inset-0 bg-black/30 z-10 group-hover:bg-black/20 transition-colors duration-300" />
                                        {game.image ? (
                                            <img
                                                src={game.image}
                                                alt={game.name}
                                                className="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700 ease-in-out"
                                            />
                                        ) : (
                                            <div className="w-full h-full flex items-center justify-center bg-base-300">
                                                <Gamepad2 className="w-12 h-12 text-base-content/30" />
                                            </div>
                                        )}
                                        
                                        {/* Overlay Content */}
                                        <div className="absolute bottom-4 left-4 z-20">
                                            <h2 className="text-2xl font-bold text-white uppercase tracking-wider mb-1 group-hover:text-yellow-400 transition-colors drop-shadow-md">
                                                {game.name}
                                            </h2>
                                            <div className="flex items-center gap-2 text-sm text-gray-200 group-hover:translate-x-2 transition-transform duration-300">
                                                <span>Lihat Akun</span>
                                                <ArrowRight className="w-4 h-4 text-yellow-500" />
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            </motion.div>
                        ))}
                    </AnimatePresence>
                </motion.div>
                
                {/* Empty State */}
                {filteredGames.length === 0 && (
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        className="text-center py-20"
                    >
                        <Gamepad2 className="w-16 h-16 text-base-content/20 mx-auto mb-4" />
                        {games.length === 0 ? (
                            <>
                                <h3 className="text-xl text-base-content/60 font-semibold">
                                    Belum ada game yang tersedia
                                </h3>
                                <p className="text-base-content/40 mt-2 text-sm">
                                    Stok akun game akan segera tersedia. Cek lagi nanti ya.
                                </p>
                            </>
                        ) : (
                            <h3 className="text-xl text-base-content/40 font-medium">
                                Game "{query}" tidak ditemukan
                            </h3>
                        )}
                    </motion.div>
                )}
            </div>
        </Layout>
    );
}
