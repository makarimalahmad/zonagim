import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import GuestLayout from "@/Layouts/GuestLayout";
import { Head, Link, usePage } from "@inertiajs/react";
import { ArrowLeft, SearchX } from "lucide-react";

export default function NotFound() {
    const { auth } = usePage().props;
    const Layout = auth?.user ? AuthenticatedLayout : GuestLayout;
    const layoutProps = auth?.user ? {} : { withNavbar: true };

    return (
        <Layout {...layoutProps}>
            <Head title="404 - Halaman Tidak Ditemukan" />

            <main className="flex min-h-[calc(100vh-15rem)] items-center justify-center px-4 py-16 sm:px-6">
                <section className="w-full max-w-xl text-center" aria-labelledby="not-found-title">
                    <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-base-300 bg-base-100 text-yellow-500">
                        <SearchX className="h-8 w-8" aria-hidden="true" />
                    </div>

                    <p className="mt-7 text-sm font-bold uppercase tracking-[0.25em] text-yellow-500">
                        Error 404
                    </p>
                    <h1
                        id="not-found-title"
                        className="mt-3 text-3xl font-extrabold tracking-tight text-base-content sm:text-4xl"
                    >
                        Halaman tidak ditemukan
                    </h1>
                    <p className="mx-auto mt-4 max-w-md text-base leading-7 text-base-content/60">
                        Alamat mungkin salah, halaman sudah dipindahkan, atau konten tidak lagi tersedia.
                    </p>

                    <div className="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                        <Link
                            href={route("market")}
                            className="inline-flex h-12 w-full items-center justify-center rounded-xl bg-yellow-500 px-6 text-sm font-bold text-black transition-colors hover:bg-yellow-400 sm:w-auto"
                        >
                            Lihat Market
                        </Link>
                        <Link
                            href={route("landing")}
                            className="inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl border border-base-300 bg-base-100 px-6 text-sm font-semibold text-base-content transition-colors hover:border-yellow-500 hover:text-yellow-500 sm:w-auto"
                        >
                            <ArrowLeft className="h-4 w-4" aria-hidden="true" />
                            Kembali ke Beranda
                        </Link>
                    </div>
                </section>
            </main>
        </Layout>
    );
}
