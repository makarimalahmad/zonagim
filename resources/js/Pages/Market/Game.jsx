import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import GuestLayout from "@/Layouts/GuestLayout";
import { Link, usePage, Head, router } from "@inertiajs/react";
import { motion, AnimatePresence } from "framer-motion";
import {
    ArrowLeft,
    Gamepad2,
    Tag,
    ShoppingCart,
    Filter,
    X,
    ChevronDown,
    Search,
} from "lucide-react";
import { useState, useEffect } from "react";

export default function Game({
    products,
    activeGame,
    activeGameSlug,
    filters = {},
}) {
    const { auth } = usePage().props;
    const Layout = auth?.user ? AuthenticatedLayout : GuestLayout;

    // Helper to safely get filter values (Handles if filters is array [] or object {})
    const getSafeSort = (f) => {
        if (!f || Array.isArray(f) || typeof f.sort !== "string")
            return "latest";
        return f.sort;
    };
    const getSafeMin = (f) => {
        if (!f || Array.isArray(f) || !f.min_price) return "";
        return f.min_price;
    };
    const getSafeMax = (f) => {
        if (!f || Array.isArray(f) || !f.max_price) return "";
        return f.max_price;
    };

    // Filter State
    const [localFilters, setLocalFilters] = useState({
        sort: getSafeSort(filters),
        min_price: getSafeMin(filters),
        max_price: getSafeMax(filters),
    });

    // Sync local state with URL filters (Handle navigation/popstate)
    useEffect(() => {
        setLocalFilters({
            sort: getSafeSort(filters),
            min_price: getSafeMin(filters),
            max_price: getSafeMax(filters),
        });
    }, [filters]);

    // Robust check for active filters
    const isFiltered =
        (localFilters.min_price !== "" && localFilters.min_price !== null) ||
        (localFilters.max_price !== "" && localFilters.max_price !== null) ||
        localFilters.sort !== "latest";

    const [isFilterOpen, setIsFilterOpen] = useState(false);

    // Update filters immediately for Sort
    const handleSortChange = (e) => {
        const val = e.target.value;
        setLocalFilters((prev) => ({ ...prev, sort: val }));

        router.get(
            route("market.category", activeGameSlug),
            {
                ...localFilters,
                sort: val,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    // Apply Price Filter
    const applyPriceFilter = (e) => {
        e.preventDefault();
        router.get(route("market.category", activeGameSlug), localFilters, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const clearFilters = () => {
        setLocalFilters({
            sort: "latest",
            min_price: "",
            max_price: "",
        });
        router.get(
            route("market.category", activeGameSlug),
            {},
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };
    const layoutProps = auth?.user ? {} : { withNavbar: true };
    const isGuest = !auth?.user;

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
    };

    return (
        <Layout {...layoutProps}>
            <Head title={activeGame} />
            <div className="min-h-screen -mt-6 p-6 md:p-12 transition-colors duration-300 relative">
                {/* HEADER SECTION */}
                <motion.div
                    initial={{ opacity: 0, x: -20 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ duration: 0.5 }}
                    className="max-w-7xl mx-auto mb-8 relative z-10 pt-2"
                >
                    <Link
                        href={route("market")}
                        className="inline-flex items-center gap-2 text-base-content/60 hover:text-yellow-500 transition-colors font-medium mb-4 group"
                    >
                        <ArrowLeft className="w-5 h-5 group-hover:-translate-x-1 transition-transform" />
                        Kembali ke Market
                    </Link>

                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h1 className="text-4xl md:text-5xl font-extrabold tracking-tight text-base-content mb-2">
                                <span className="text-yellow-500">
                                    {activeGame}
                                </span>{" "}
                                Accounts
                            </h1>
                            <p className="text-base-content/60 max-w-2xl">
                                Daftar akun {activeGame} terlengkap, aman, dan
                                bergaransi.
                            </p>
                        </div>

                        <div className="bg-base-100 px-4 py-2 rounded-full border border-base-300 text-sm font-medium text-base-content/70 shadow-sm">
                            {products.length} Akun Tersedia
                        </div>
                    </div>
                </motion.div>

                {/* FILTER & SORT BAR */}
                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.1 }}
                    className="max-w-7xl mx-auto mb-8 relative z-20"
                >
                    <div className="bg-base-100 border border-base-300 rounded-2xl p-2 md:p-3 flex flex-col md:flex-row gap-3 items-center justify-between shadow-sm relative z-20">
                        {/* Left Side: Filter Toggle */}
                        <div className="w-full md:w-auto flex items-center gap-2">
                            <button
                                onClick={() => setIsFilterOpen(!isFilterOpen)}
                                className={`btn btn-sm md:btn-md rounded-xl normal-case font-medium pr-4 md:pr-6 transition-all duration-300 ${
                                    isFilterOpen
                                        ? "bg-base-content text-base-100 hover:bg-base-content/90 shadow-lg"
                                        : "bg-base-200/50 hover:bg-base-200 text-base-content border-transparent hover:border-base-content/10"
                                }`}
                            >
                                <div
                                    className={`w-8 h-8 rounded-lg flex items-center justify-center -ml-1 ${isFilterOpen ? "bg-base-100/20" : "bg-base-content/5"}`}
                                >
                                    <Filter className="w-4 h-4" />
                                </div>
                                <span>Filter Harga</span>
                                {(localFilters.min_price ||
                                    localFilters.max_price) && (
                                    <span className="ml-2 w-2 h-2 rounded-full bg-warning ring-4 ring-warning/20 animate-pulse"></span>
                                )}
                            </button>

                            {isFiltered && (
                                <button
                                    onClick={clearFilters}
                                    className="btn btn-sm md:btn-md btn-ghost text-error hover:bg-error/10 rounded-xl gap-2 font-medium normal-case"
                                >
                                    <X className="w-4 h-4" />
                                    <span className="hidden md:inline">
                                        Reset
                                    </span>
                                </button>
                            )}
                        </div>

                        {/* Right Side: Custom Sort Dropdown */}
                        <div className="w-full md:w-auto relative group">
                            <div className="dropdown dropdown-end w-full md:w-auto">
                                <div
                                    tabIndex={0}
                                    role="button"
                                    className="btn btn-sm md:btn-md w-full md:w-auto bg-base-100 border border-base-300 hover:border-yellow-500 hover:text-yellow-500 rounded-xl gap-3 normal-case font-medium shadow-sm transition-all text-base-content group"
                                >
                                    <span className="text-base-content/50 font-normal">
                                        Urutkan:
                                    </span>
                                    <span className="min-w-[100px] text-left">
                                        {localFilters.sort === "latest" &&
                                            "Terbaru"}
                                        {localFilters.sort === "lowest" &&
                                            "Harga Terendah"}
                                        {localFilters.sort === "highest" &&
                                            "Harga Tertinggi"}
                                        {/* Fallback for invalid state */}
                                        {localFilters.sort !== "latest" &&
                                            localFilters.sort !== "lowest" &&
                                            localFilters.sort !== "highest" &&
                                            "Terbaru"}
                                    </span>
                                    <ChevronDown className="w-4 h-4 text-base-content/30 group-hover:text-yellow-500 transition-colors" />
                                </div>
                                <ul
                                    tabIndex={0}
                                    className="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-2xl border border-base-300 w-full md:w-60 mt-2 gap-1 translate-y-2"
                                >
                                    <li>
                                        <button
                                            onClick={() =>
                                                handleSortChange({
                                                    target: { value: "latest" },
                                                })
                                            }
                                            className={`rounded-xl py-3 px-4 ${localFilters.sort === "latest" ? "bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 font-bold active" : "text-base-content/70 hover:bg-base-200"}`}
                                        >
                                            Terbaru
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            onClick={() =>
                                                handleSortChange({
                                                    target: { value: "lowest" },
                                                })
                                            }
                                            className={`rounded-xl py-3 px-4 ${localFilters.sort === "lowest" ? "bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 font-bold active" : "text-base-content/70 hover:bg-base-200"}`}
                                        >
                                            Harga Terendah
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            onClick={() =>
                                                handleSortChange({
                                                    target: {
                                                        value: "highest",
                                                    },
                                                })
                                            }
                                            className={`rounded-xl py-3 px-4 ${localFilters.sort === "highest" ? "bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 font-bold active" : "text-base-content/70 hover:bg-base-200"}`}
                                        >
                                            Harga Tertinggi
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {/* EXPANDABLE FILTER PANEL */}
                    <AnimatePresence>
                        {isFilterOpen && (
                            <motion.div
                                initial={{ height: 0, opacity: 0 }}
                                animate={{ height: "auto", opacity: 1 }}
                                exit={{ height: 0, opacity: 0 }}
                                className="overflow-hidden"
                            >
                                <form
                                    onSubmit={applyPriceFilter}
                                    className="bg-base-200 border border-base-300 rounded-2xl p-6 mt-4 grid grid-cols-1 md:grid-cols-3 gap-6 items-end"
                                >
                                    <div className="form-control">
                                        <label className="label">
                                            <span className="label-text">
                                                Harga Minimum
                                            </span>
                                        </label>
                                        <label className="input input-bordered flex items-center gap-2">
                                            Rp
                                            <input
                                                type="number"
                                                className="grow"
                                                placeholder="10000"
                                                value={localFilters.min_price}
                                                onChange={(e) =>
                                                    setLocalFilters({
                                                        ...localFilters,
                                                        min_price:
                                                            e.target.value,
                                                    })
                                                }
                                            />
                                        </label>
                                    </div>

                                    <div className="form-control">
                                        <label className="label">
                                            <span className="label-text">
                                                Harga Maksimum
                                            </span>
                                        </label>
                                        <label className="input input-bordered flex items-center gap-2">
                                            Rp
                                            <input
                                                type="number"
                                                className="grow"
                                                placeholder="500000"
                                                value={localFilters.max_price}
                                                onChange={(e) =>
                                                    setLocalFilters({
                                                        ...localFilters,
                                                        max_price:
                                                            e.target.value,
                                                    })
                                                }
                                            />
                                        </label>
                                    </div>

                                    <div className="flex gap-2">
                                        <button
                                            type="submit"
                                            className="btn btn-primary flex-1"
                                        >
                                            Terapkan
                                        </button>
                                    </div>
                                </form>
                            </motion.div>
                        )}
                    </AnimatePresence>
                </motion.div>

                {/* PRODUCTS GRID */}
                <motion.div
                    variants={containerVariants}
                    initial="hidden"
                    animate="visible"
                    className="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 relative z-10"
                >
                    {products.length > 0 ? (
                        products.map((product) => (
                            <motion.div
                                key={product.id}
                                variants={itemVariants}
                            >
                                <div className="card bg-base-100 shadow-sm border border-base-300 hover:border-yellow-500/50 hover:shadow-md transition-all duration-300 group h-full flex flex-col overflow-hidden rounded-2xl">
                                    {/* Image Section */}
                                    <figure className="relative aspect-[16/9] bg-base-200 overflow-hidden">
                                        <div className="absolute inset-0 bg-black/30 group-hover:bg-black/20 transition-colors duration-300 z-10" />

                                        {product.image ? (
                                            <img
                                                src={product.image}
                                                alt={product.game_name}
                                                className="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700"
                                            />
                                        ) : (
                                            <div className="w-full h-full flex items-center justify-center bg-base-300">
                                                <Gamepad2 className="w-12 h-12 text-base-content/20" />
                                            </div>
                                        )}

                                        {/* Badge Price */}
                                        <div className="absolute top-3 left-3 z-20">
                                            <div className="badge badge-warning font-bold shadow-md border-none bg-yellow-500 text-black">
                                                {activeGame}
                                            </div>
                                        </div>
                                    </figure>

                                    {/* Content Section */}
                                    <div className="card-body p-5 flex-1 relative">
                                        <h2 className="card-title text-lg font-bold text-base-content line-clamp-2 group-hover:text-yellow-500 transition-colors mb-2 leading-tight">
                                            {product.game_name}
                                        </h2>

                                        <div className="mt-auto pt-4 flex flex-col gap-3">
                                            <div className="flex flex-col">
                                                <span className="text-xs text-base-content/50 uppercase tracking-wider font-semibold mb-1">
                                                    Harga
                                                </span>
                                                <span className="text-xl font-extrabold text-yellow-500 whitespace-nowrap leading-none">
                                                    Rp{" "}
                                                    {Number(
                                                        product.price,
                                                    ).toLocaleString("id-ID")}
                                                </span>
                                            </div>

                                            {isGuest ? (
                                                <Link
                                                    href={route("login", {
                                                        message:
                                                            "Silakan login untuk melihat detail akun",
                                                    })}
                                                    className="btn btn-sm w-full bg-yellow-500 hover:bg-yellow-600 text-black font-bold border-none rounded-xl shadow-sm transition-all transform hover:-translate-y-0.5"
                                                    title="Login untuk detail"
                                                >
                                                    Detail Akun
                                                </Link>
                                            ) : (
                                                <Link
                                                    href={route("akun.show", [
                                                        activeGameSlug,
                                                        product.id,
                                                    ])}
                                                    className="btn btn-sm w-full bg-yellow-500 hover:bg-yellow-600 text-black font-bold border-none rounded-xl shadow-sm transition-all transform hover:-translate-y-0.5"
                                                    title="Lihat detail akun"
                                                >
                                                    Detail
                                                </Link>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </motion.div>
                        ))
                    ) : (
                        <div className="col-span-full py-20 text-center">
                            <Gamepad2 className="w-16 h-16 text-base-content/20 mx-auto mb-4" />
                            <h3 className="text-xl text-base-content/50 font-medium">
                                Belum ada akun untuk game ini.
                            </h3>
                            <Link
                                href={route("market")}
                                className="text-yellow-500 hover:underline mt-2 inline-block"
                            >
                                Cari game lain
                            </Link>
                        </div>
                    )}
                </motion.div>
            </div>
        </Layout>
    );
}
