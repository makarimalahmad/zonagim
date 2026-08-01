import { Link, usePage } from "@inertiajs/react";
import ThemeToggle from "@/Components/ThemeToggle";
import Dropdown from "@/Components/Dropdown";
import ChatWidget from "@/Components/ChatWidget";
import FlashToaster from "@/Components/FlashToaster";
import Footer from "@/Components/Footer";
import ProgressiveImage from "@/Components/ProgressiveImage";

export default function AuthenticatedLayout({ header, children }) {
    const { auth } = usePage().props;

    return (
        <div className="min-h-screen bg-base-200 flex flex-col">
            <nav className="bg-base-100 border-b border-base-300">
                <div className="max-w-7xl mx-auto flex items-center justify-between px-4 py-3 sm:px-6 sm:py-4">
                    <Link
                        href={route("market")}
                        className="flex items-center gap-3 group"
                    >
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

                    <div className="flex items-center gap-4">
                    <ThemeToggle />

                    <div className="relative">
                        <Dropdown>
                            <Dropdown.Trigger>
                                <span className="inline-flex rounded-md">
                                    <button
                                        type="button"
                                        className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-full text-base-content hover:bg-base-200 focus:outline-none transition ease-in-out duration-150"
                                    >
                                        <span
                                            className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-yellow-500 text-sm font-bold text-[#0b1221] sm:mr-2"
                                            aria-hidden="true"
                                        >
                                            {auth.user.name
                                                .trim()
                                                .charAt(0)
                                                .toUpperCase()}
                                        </span>
                                        <span className="hidden sm:inline font-semibold">
                                            {auth.user.name}
                                        </span>

                                        <svg
                                            className="ms-2 -me-0.5 h-4 w-4 hidden sm:block"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fillRule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clipRule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </span>
                            </Dropdown.Trigger>

                            <Dropdown.Content contentClasses="py-1 bg-base-100 border border-base-300 ring-1 ring-black ring-opacity-5">
                                <div className="px-4 py-3 border-b border-base-200">
                                    <p className="text-sm font-medium text-base-content truncate">
                                        {auth.user.name}
                                    </p>
                                    <p className="text-xs text-base-content/70 truncate">
                                        {auth.user.email}
                                    </p>
                                </div>

                                <Dropdown.Link
                                    href={route("profile.edit")}
                                    className="text-base-content hover:bg-base-200 flex items-center gap-2"
                                >
                                    Pengaturan
                                </Dropdown.Link>

                                <div className="border-t border-base-200 my-1"></div>

                                <Dropdown.Link
                                    href={route("logout")}
                                    method="post"
                                    as="button"
                                    className="text-error hover:bg-error/10 w-full text-left flex items-center gap-2"
                                >
                                    Keluar
                                </Dropdown.Link>
                            </Dropdown.Content>
                        </Dropdown>
                    </div>
                </div>
                </div>
            </nav>

            {header && (
                <header className="bg-base-100 shadow border-b border-base-300">
                    <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}

            <main className="p-6 grow">{children}</main>
            <Footer />
            <ChatWidget />
            <FlashToaster />
        </div>
    );
}
