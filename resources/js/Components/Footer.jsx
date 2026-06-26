import { Link } from "@inertiajs/react";

export default function Footer() {
    const currentYear = new Date().getFullYear();

    return (
        <footer className="border-t border-base-300 bg-base-100 py-10 relative z-10 text-sm mt-auto">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex flex-col md:flex-row justify-between items-center gap-6">
                    {/* Logo & Brand */}
                    <div className="flex items-center gap-3 opacity-70 hover:opacity-100 transition-opacity">
                        <img
                            src="/images/lapakgimid.png"
                            alt="Logo"
                            className="w-8 h-8"
                        />
                        <span className="font-bold text-base-content">
                            LapakGimID
                        </span>
                    </div>

                    {/* Copyright & Links */}
                    <div className="text-base-content/60 text-center md:text-right">
                        <p>
                            &copy; {currentYear} LapakGimID. All rights
                            reserved.
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
    );
}
