import { Link, useForm, usePage } from "@inertiajs/react";
import ThemeToggle from "@/Components/ThemeToggle";
import Dropdown from "@/Components/Dropdown";
import ChatWidget from "@/Components/ChatWidget";
import Footer from "@/Components/Footer";

export default function AuthenticatedLayout({ header, children }) {
    const { auth } = usePage().props;
    const { post } = useForm();

    const logout = () => {
        post(route("logout"));
    };

    return (
        <div className="min-h-screen bg-base-200 flex flex-col">
            <nav className="flex justify-between items-center px-6 py-4 bg-base-100 border-b border-base-300">
                <Link
                    href={route("market")}
                    className="flex items-center gap-3 group"
                >
                    <div className="w-10 h-10 rounded-lg bg-base-200 flex items-center justify-center group-hover:bg-base-300 transition-colors">
                        <img
                            src="/images/lapakakunid.png"
                            alt="LapakAkunID Logo"
                            className="w-8 h-8 object-contain"
                        />
                    </div>
                    <span className="text-xl font-bold text-base-content group-hover:text-yellow-500 transition-colors">
                        LapakAkunID
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
                                        <img
                                            src={`https://ui-avatars.com/api/?name=${auth.user.name}&background=random&color=fff`}
                                            alt={auth.user.name}
                                            className="h-8 w-8 rounded-full object-cover sm:mr-2"
                                        />
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
                                    Settings
                                </Dropdown.Link>

                                <div className="border-t border-base-200 my-1"></div>

                                <Dropdown.Link
                                    href={route("logout")}
                                    method="post"
                                    as="button"
                                    onClick={() =>
                                        localStorage.removeItem("chat_history")
                                    }
                                    className="text-error hover:bg-error/10 w-full text-left flex items-center gap-2"
                                >
                                    Sign Out
                                </Dropdown.Link>
                            </Dropdown.Content>
                        </Dropdown>
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

            <main className="p-6 flex-grow">{children}</main>
            <Footer />
            <ChatWidget />
        </div>
    );
}
