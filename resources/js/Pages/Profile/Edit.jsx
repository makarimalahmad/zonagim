import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link } from "@inertiajs/react";
import { ArrowLeft, User, Shield, Bell, Palette } from "lucide-react";
import DeleteUserForm from "./Partials/DeleteUserForm";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm";
import { useState } from "react";

export default function Edit({ mustVerifyEmail, status }) {
    const [activeTab, setActiveTab] = useState("profile");

    const tabs = [
        { id: "profile", label: "Edit Profile", icon: User },
        { id: "security", label: "Account Security", icon: Shield },
        {
            id: "appearance",
            label: "Appearance",
            icon: Palette,
            disabled: true,
        },
        {
            id: "notifications",
            label: "Notifications",
            icon: Bell,
            disabled: true,
        },
    ];

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-4">
                    <Link
                        href={route("market")}
                        className="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-base-content/70 hover:text-yellow-500 hover:bg-base-200 rounded-lg transition-all"
                    >
                        <ArrowLeft className="w-4 h-4" />
                        Back to Market
                    </Link>
                    <h2 className="text-xl font-bold leading-tight text-base-content border-l border-base-300 pl-4">
                        Settings
                    </h2>
                </div>
            }
        >
            <Head title="Settings" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="flex flex-col lg:flex-row gap-6">
                        {/* Sidebar Navigation */}
                        <div className="w-full lg:w-64 flex-shrink-0 space-y-2">
                            <div className="bg-base-100 rounded-xl border border-base-300 overflow-hidden">
                                {tabs.map((tab) => (
                                    <button
                                        key={tab.id}
                                        onClick={() =>
                                            !tab.disabled &&
                                            setActiveTab(tab.id)
                                        }
                                        disabled={tab.disabled}
                                        className={`w-full flex items-center gap-3 px-4 py-3 text-sm font-medium transition-all duration-200
                                            ${
                                                activeTab === tab.id
                                                    ? "bg-yellow-500/10 text-yellow-500 border-l-4 border-yellow-500"
                                                    : "text-base-content/70 hover:bg-base-200 hover:text-base-content border-l-4 border-transparent"
                                            }
                                            ${tab.disabled ? "opacity-50 cursor-not-allowed" : "cursor-pointer"}
                                        `}
                                    >
                                        <tab.icon className="w-4 h-4" />
                                        {tab.label}
                                        {tab.disabled && (
                                            <span className="ml-auto text-xs bg-base-300 px-2 py-0.5 rounded text-base-content/50">
                                                Soon
                                            </span>
                                        )}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* Main Content */}
                        <div className="flex-1">
                            {activeTab === "profile" && (
                                <div className="space-y-6 animate-fadeIn">
                                    <div className="bg-base-100 p-4 border border-base-300 shadow-sm sm:rounded-xl sm:p-8">
                                        <UpdateProfileInformationForm
                                            mustVerifyEmail={mustVerifyEmail}
                                            status={status}
                                            className="w-full"
                                        />
                                    </div>
                                </div>
                            )}

                            {activeTab === "security" && (
                                <div className="space-y-6 animate-fadeIn">
                                    <div className="bg-base-100 p-4 border border-base-300 shadow-sm sm:rounded-xl sm:p-8">
                                        <UpdatePasswordForm className="max-w-xl" />
                                    </div>

                                    <div className="bg-base-100 p-4 border border-red-500/20 shadow-sm sm:rounded-xl sm:p-8">
                                        <DeleteUserForm className="max-w-xl" />
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
