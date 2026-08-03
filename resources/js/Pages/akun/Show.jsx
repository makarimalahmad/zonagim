import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import GuestLayout from "@/Layouts/GuestLayout";
import ProgressiveImage from "@/Components/ProgressiveImage";
import { Head, Link, usePage } from "@inertiajs/react";
import {
    ArrowLeft,
    ChevronLeft,
    ChevronRight,
    Maximize2,
    MessageCircle,
    ShieldCheck,
    User,
    X,
    ZoomIn,
    ZoomOut,
} from "lucide-react";
import { useEffect, useRef, useState } from "react";

export default function Show({ product }) {
    const { auth } = usePage().props;
    const Layout = auth?.user ? AuthenticatedLayout : GuestLayout;
    const layoutProps = auth?.user ? {} : { withNavbar: true };
    const images = product?.images ?? [];
    const [currentIndex, setCurrentIndex] = useState(0);
    const [isPreviewOpen, setIsPreviewOpen] = useState(false);
    const [zoom, setZoom] = useState(1);
    const [position, setPosition] = useState({ x: 0, y: 0 });
    const [isDragging, setIsDragging] = useState(false);
    const dragOriginRef = useRef({ x: 0, y: 0 });
    const currentImage = images[currentIndex] ?? null;

    useEffect(() => {
        setCurrentIndex(0);
    }, [product?.id]);

    useEffect(() => {
        if (!isPreviewOpen) {
            return;
        }

        const handleKeyDown = (event) => {
            if (event.key === "Escape") {
                setIsPreviewOpen(false);
            }

            if (event.key === "ArrowLeft" && images.length > 1) {
                setCurrentIndex((index) =>
                    index === 0 ? images.length - 1 : index - 1,
                );
                setZoom(1);
                setPosition({ x: 0, y: 0 });
            }

            if (event.key === "ArrowRight" && images.length > 1) {
                setCurrentIndex((index) => (index + 1) % images.length);
                setZoom(1);
                setPosition({ x: 0, y: 0 });
            }
        };

        document.body.style.overflow = "hidden";
        window.addEventListener("keydown", handleKeyDown);

        return () => {
            document.body.style.overflow = "";
            window.removeEventListener("keydown", handleKeyDown);
        };
    }, [images.length, isPreviewOpen]);

    if (!product) {
        return (
            <div className="flex min-h-screen items-center justify-center text-base-content/50">
                Produk tidak ditemukan.
            </div>
        );
    }

    const showPrevious = () => {
        setCurrentIndex((index) =>
            index === 0 ? images.length - 1 : index - 1,
        );
        setZoom(1);
        setPosition({ x: 0, y: 0 });
    };

    const showNext = () => {
        setCurrentIndex((index) => (index + 1) % images.length);
        setZoom(1);
        setPosition({ x: 0, y: 0 });
    };

    const openPreview = () => {
        if (!currentImage) {
            return;
        }

        setZoom(1);
        setPosition({ x: 0, y: 0 });
        setIsPreviewOpen(true);
    };

    const handlePreviewWheel = (event) => {
        if (event.ctrlKey || event.metaKey) {
            return;
        }

        event.preventDefault();
        const direction = event.deltaY < 0 ? 0.25 : -0.25;
        setZoom((value) => {
            const nextZoom = Math.min(3, Math.max(1, value + direction));

            if (nextZoom === 1) {
                setPosition({ x: 0, y: 0 });
            }

            return nextZoom;
        });
    };

    const handlePointerDown = (event) => {
        if (zoom <= 1 || event.button !== 0) {
            return;
        }

        event.currentTarget.setPointerCapture(event.pointerId);
        dragOriginRef.current = {
            x: event.clientX - position.x,
            y: event.clientY - position.y,
        };
        setIsDragging(true);
    };

    const handlePointerMove = (event) => {
        if (!isDragging) {
            return;
        }

        setPosition({
            x: event.clientX - dragOriginRef.current.x,
            y: event.clientY - dragOriginRef.current.y,
        });
    };

    const stopDragging = (event) => {
        if (event.currentTarget.hasPointerCapture?.(event.pointerId)) {
            event.currentTarget.releasePointerCapture(event.pointerId);
        }

        setIsDragging(false);
    };

    return (
        <Layout {...layoutProps}>
            <Head title={product.title || product.game_name} />

            <main className="min-h-screen px-4 py-7 sm:px-6 lg:px-8 lg:py-9">
                <div className="mx-auto max-w-6xl">
                    <Link
                        href={route("market.category", product.slug)}
                        className="mb-5 inline-flex items-center gap-2 text-sm font-medium text-base-content/60 transition-colors hover:text-yellow-500"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Kembali ke {product.category}
                    </Link>

                    <div className="grid items-start gap-6 lg:grid-cols-[minmax(0,620px)_minmax(320px,1fr)] lg:justify-center lg:gap-8">
                        <section
                            aria-label="Galeri akun"
                            className="min-w-0 lg:max-w-[620px]"
                        >
                            <button
                                type="button"
                                onClick={openPreview}
                                disabled={!currentImage}
                                className="group relative flex aspect-[16/10] w-full items-center justify-center overflow-hidden rounded-2xl border border-base-300 bg-base-100 p-3 disabled:cursor-default sm:p-4"
                                aria-label="Buka preview gambar"
                            >
                                <ProgressiveImage
                                    src={currentImage}
                                    alt={product.title || product.game_name}
                                    width={16}
                                    height={10}
                                    loading="eager"
                                    fetchPriority="high"
                                    wrapperClassName="h-full w-full"
                                    className="object-contain"
                                    fallback={
                                        <span className="text-sm text-base-content/40">
                                            Gambar belum tersedia
                                        </span>
                                    }
                                />
                                {currentImage && (
                                    <span className="absolute bottom-3 right-3 inline-flex items-center gap-2 rounded-lg border border-base-300 bg-base-100 px-3 py-2 text-xs font-semibold text-base-content transition-colors group-hover:border-yellow-500 group-hover:text-yellow-500">
                                        <Maximize2 className="h-4 w-4" />
                                        Preview
                                    </span>
                                )}
                            </button>

                            {images.length > 1 && (
                                <div className="mt-3 flex gap-2 overflow-x-auto pb-1">
                                    {images.map((image, index) => (
                                        <button
                                            key={image}
                                            type="button"
                                            onClick={() => {
                                                setCurrentIndex(index);
                                                setZoom(1);
                                            }}
                                            className={`h-16 w-20 shrink-0 overflow-hidden rounded-lg border-2 bg-base-100 transition-colors ${
                                                currentIndex === index
                                                    ? "border-yellow-500"
                                                    : "border-base-300 hover:border-base-content/30"
                                            }`}
                                            aria-label={`Tampilkan gambar ${index + 1}`}
                                            aria-current={
                                                currentIndex === index
                                                    ? "true"
                                                    : undefined
                                            }
                                        >
                                            <ProgressiveImage
                                                src={image}
                                                alt=""
                                                width={6}
                                                height={5}
                                                wrapperClassName="h-full w-full"
                                                className="object-cover"
                                            />
                                        </button>
                                    ))}
                                </div>
                            )}
                        </section>

                        <aside className="rounded-2xl border border-base-300 bg-base-100 p-5 sm:p-6 lg:sticky lg:top-24">
                            <p className="mb-2 text-xs font-bold uppercase tracking-wide text-yellow-500">
                                {product.category}
                            </p>
                            <h1 className="text-2xl font-extrabold leading-tight text-base-content sm:text-3xl">
                                {product.title || product.game_name}
                            </h1>
                            <p className="mt-4 text-2xl font-extrabold text-yellow-500 sm:text-3xl">
                                Rp {Number(product.price).toLocaleString("id-ID")}
                            </p>

                            <div className="my-5 border-t border-base-300" />

                            <section>
                                <h2 className="text-sm font-bold text-base-content">
                                    Deskripsi akun
                                </h2>
                                <p className="mt-3 whitespace-pre-line text-sm leading-6 text-base-content/65">
                                    {product.description ||
                                        "Penjual belum menambahkan deskripsi akun."}
                                </p>
                            </section>

                            <div className="my-5 border-t border-base-300" />

                            <section>
                                <h2 className="text-sm font-bold text-base-content">
                                    Penjual
                                </h2>
                                <div className="mt-3 flex items-center gap-3">
                                    <div className="flex h-11 w-11 items-center justify-center rounded-full border border-base-300 bg-base-200 text-base-content/50">
                                        <User className="h-5 w-5" />
                                    </div>
                                    <span className="font-semibold text-base-content">
                                        {product.seller_name || "Penjual Zonagim"}
                                    </span>
                                </div>

                                {product.seller_whatsapp && (
                                    <a
                                        href={`https://wa.me/${product.seller_whatsapp}`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="mt-4 inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-yellow-500 px-4 text-sm font-bold text-black transition-colors hover:bg-yellow-400"
                                    >
                                        <MessageCircle className="h-5 w-5" />
                                        Chat Penjual
                                    </a>
                                )}
                            </section>

                            {product.rekber_contact_url && (
                                <section className="mt-5 rounded-xl border border-base-300 bg-base-200 p-4">
                                    <div className="flex items-start gap-3">
                                        <ShieldCheck className="mt-0.5 h-5 w-5 shrink-0 text-info" />
                                        <div>
                                            <h2 className="text-sm font-bold text-base-content">
                                                Opsi Rekber
                                            </h2>
                                            <p className="mt-1 text-xs leading-5 text-base-content/60">
                                                Admin membantu alur pembayaran dan
                                                penyerahan data selama transaksi.
                                            </p>
                                        </div>
                                    </div>
                                    <a
                                        href={product.rekber_contact_url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="mt-4 inline-flex h-10 w-full items-center justify-center gap-2 rounded-lg border border-base-300 bg-base-100 text-sm font-semibold text-base-content transition-colors hover:border-yellow-500 hover:text-yellow-500"
                                    >
                                        <MessageCircle className="h-4 w-4" />
                                        Hubungi Admin Rekber
                                    </a>
                                </section>
                            )}
                        </aside>
                    </div>
                </div>
            </main>

            {isPreviewOpen && currentImage && (
                <div
                    className="fixed inset-0 z-[120] flex items-center justify-center bg-black/90 p-4 sm:p-8"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Preview gambar akun"
                    onClick={() => setIsPreviewOpen(false)}
                >
                    <div
                        className="relative flex h-full w-full max-w-7xl items-center justify-center overflow-hidden"
                        onClick={(event) => event.stopPropagation()}
                        onWheel={handlePreviewWheel}
                    >
                        <ProgressiveImage
                            src={currentImage}
                            alt={product.title || product.game_name}
                            width={16}
                            height={10}
                            loading="eager"
                            wrapperClassName="h-full w-full bg-transparent"
                            className={`select-none object-contain ${
                                isDragging
                                    ? "cursor-grabbing"
                                    : zoom > 1
                                      ? "cursor-grab"
                                      : "cursor-default"
                            }`}
                            style={{
                                transform: `translate(${position.x}px, ${position.y}px) scale(${zoom})`,
                                transition: isDragging
                                    ? "none"
                                    : "transform 200ms ease",
                            }}
                            onPointerDown={handlePointerDown}
                            onPointerMove={handlePointerMove}
                            onPointerUp={stopDragging}
                            onPointerCancel={stopDragging}
                            draggable="false"
                        />

                        <div className="absolute right-0 top-0 flex items-center gap-2">
                            <button
                                type="button"
                                onClick={() =>
                                    setZoom((value) => {
                                        const nextZoom = Math.max(1, value - 0.25);

                                        if (nextZoom === 1) {
                                            setPosition({ x: 0, y: 0 });
                                        }

                                        return nextZoom;
                                    })
                                }
                                disabled={zoom <= 1}
                                className="flex h-11 w-11 items-center justify-center rounded-lg border border-white/20 bg-black/60 text-white hover:bg-black/80 disabled:opacity-40"
                                aria-label="Perkecil gambar"
                            >
                                <ZoomOut className="h-5 w-5" />
                            </button>
                            <button
                                type="button"
                                onClick={() =>
                                    setZoom((value) => Math.min(3, value + 0.25))
                                }
                                disabled={zoom >= 3}
                                className="flex h-11 w-11 items-center justify-center rounded-lg border border-white/20 bg-black/60 text-white hover:bg-black/80 disabled:opacity-40"
                                aria-label="Perbesar gambar"
                            >
                                <ZoomIn className="h-5 w-5" />
                            </button>
                            <button
                                type="button"
                                onClick={() => setIsPreviewOpen(false)}
                                className="flex h-11 w-11 items-center justify-center rounded-lg border border-white/20 bg-black/60 text-white hover:bg-black/80"
                                aria-label="Tutup preview"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        {images.length > 1 && (
                            <>
                                <button
                                    type="button"
                                    onClick={showPrevious}
                                    className="absolute left-0 flex h-12 w-12 items-center justify-center rounded-full border border-white/20 bg-black/60 text-white hover:bg-black/80"
                                    aria-label="Gambar sebelumnya"
                                >
                                    <ChevronLeft className="h-6 w-6" />
                                </button>
                                <button
                                    type="button"
                                    onClick={showNext}
                                    className="absolute right-0 flex h-12 w-12 items-center justify-center rounded-full border border-white/20 bg-black/60 text-white hover:bg-black/80"
                                    aria-label="Gambar berikutnya"
                                >
                                    <ChevronRight className="h-6 w-6" />
                                </button>
                            </>
                        )}

                        <span className="absolute bottom-0 rounded-lg bg-black/60 px-3 py-2 text-xs text-white">
                            {currentIndex + 1} / {images.length} · {Math.round(zoom * 100)}% · Scroll untuk zoom
                        </span>
                    </div>
                </div>
            )}
        </Layout>
    );
}
