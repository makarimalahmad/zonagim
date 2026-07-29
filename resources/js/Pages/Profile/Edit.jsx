import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link } from "@inertiajs/react";
import { ArrowLeft, Bell, Palette, Shield, User } from "lucide-react";
import { useState } from "react";
import DeleteUserForm from "./Partials/DeleteUserForm";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm";

export default function Edit({ mustVerifyEmail, status }) {
    const [activeTab, setActiveTab] = useState("profile");

    const tabs = [
        { id: "profile", label: "Profil", icon: User },
        { id: "security", label: "Keamanan Akun", icon: Shield },
        {
            id: "appearance",
            label: "Tampilan",
            icon: Palette,
            disabled: true,
        },
        {
            id: "notifications",
            label: "Notifikasi",
            icon: Bell,
            disabled: true,
        },
    ];

    return (
        <AuthenticatedLayout>
            <Head title="Pengaturan Akun" />

            <main className="min-h-screen px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <div className="mx-auto max-w-6xl">
                    <Link
                        href={route("market")}
                        className="inline-flex items-center gap-2 text-sm font-medium text-base-content/60 transition-colors hover:text-yellow-500"
                    >
                        <ArrowLeft className="h-4 w-4" aria-hidden="true" />
                        Kembali ke Market
                    </Link>

                    <div className="mt-5">
                        <h1 className="text-3xl font-extrabold tracking-tight text-base-content">
                            Pengaturan Akun
                        </h1>
                        <p className="mt-2 text-sm text-base-content/60">
                            Kelola informasi profil dan keamanan akun Anda.
                        </p>
                    </div>

                    <div className="mt-8 grid items-start gap-6 lg:grid-cols-[240px_minmax(0,1fr)] lg:gap-8">
                        <nav
                            className="overflow-hidden rounded-2xl border border-base-300 bg-base-100 p-2"
                            aria-label="Menu pengaturan akun"
                        >
                            <div className="grid grid-cols-2 gap-1 sm:grid-cols-4 lg:grid-cols-1">
                                {tabs.map((tab) => (
                                    <button
                                        key={tab.id}
                                        type="button"
                                        onClick={() =>
                                            !tab.disabled &&
                                            setActiveTab(tab.id)
                                        }
                                        disabled={tab.disabled}
                                        className={`flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold transition-colors ${
                                            activeTab === tab.id
                                                ? "bg-yellow-500/10 text-yellow-500"
                                                : "text-base-content/65 hover:bg-base-200 hover:text-base-content"
                                        } ${
                                            tab.disabled
                                                ? "cursor-not-allowed opacity-45"
                                                : ""
                                        }`}
                                    >
                                        <tab.icon
                                            className="h-4 w-4 shrink-0"
                                            aria-hidden="true"
                                        />
                                        <span className="truncate">
                                            {tab.label}
                                        </span>
                                        {tab.disabled && (
                                            <span className="ml-auto hidden rounded-md bg-base-300 px-2 py-0.5 text-[10px] font-medium text-base-content/50 sm:inline">
                                                Segera
                                            </span>
                                        )}
                                    </button>
                                ))}
                            </div>
                        </nav>

                        <div className="min-w-0">
                            {activeTab === "profile" && (
                                <div className="rounded-2xl border border-base-300 bg-base-100 p-5 sm:p-7">
                                    <UpdateProfileInformationForm
                                        mustVerifyEmail={mustVerifyEmail}
                                        status={status}
                                        className="w-full"
                                    />
                                </div>
                            )}

                            {activeTab === "security" && (
                                <div className="space-y-6">
                                    <div className="rounded-2xl border border-base-300 bg-base-100 p-5 sm:p-7">
                                        <UpdatePasswordForm className="max-w-xl" />
                                    </div>

                                    <div className="rounded-2xl border border-red-500/30 bg-base-100 p-5 sm:p-7">
                                        <DeleteUserForm className="max-w-xl" />
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </main>
        </AuthenticatedLayout>
    );
}
