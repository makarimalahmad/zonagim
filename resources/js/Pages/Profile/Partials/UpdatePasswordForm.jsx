import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import { toastOptions } from "@/utils/notificationTheme";
import { useForm } from "@inertiajs/react";
import { Eye, EyeOff } from "lucide-react";
import { useRef, useState } from "react";
import Swal from "sweetalert2";

export default function UpdatePasswordForm({ className = "" }) {
    const passwordInput = useRef();
    const currentPasswordInput = useRef();
    const passwordConfirmationInput = useRef();
    const [clientErrors, setClientErrors] = useState({});
    const [visiblePasswords, setVisiblePasswords] = useState({
        current_password: false,
        password: false,
        password_confirmation: false,
    });

    const togglePasswordVisibility = (field) => {
        setVisiblePasswords((current) => ({
            ...current,
            [field]: !current[field],
        }));
    };

    const {
        data,
        setData,
        errors,
        put,
        reset,
        processing,
    } = useForm({
        current_password: "",
        password: "",
        password_confirmation: "",
    });

    const showToast = (icon, title) => {
        Swal.fire(toastOptions(icon, title));
    };

    const PasswordVisibilityButton = ({ field, label }) => (
        <button
            type="button"
            onClick={() => togglePasswordVisibility(field)}
            className="absolute inset-y-0 right-0 flex items-center px-4 text-base-content/40 transition-colors hover:text-base-content focus:outline-none focus-visible:text-yellow-500"
            aria-label={`${visiblePasswords[field] ? "Sembunyikan" : "Tampilkan"} ${label}`}
            aria-pressed={visiblePasswords[field]}
        >
            {visiblePasswords[field] ? (
                <EyeOff className="h-5 w-5" aria-hidden="true" />
            ) : (
                <Eye className="h-5 w-5" aria-hidden="true" />
            )}
        </button>
    );

    const updatePassword = (event) => {
        event.preventDefault();

        const requiredErrors = {
            current_password: data.current_password
                ? ""
                : "Kata sandi saat ini wajib diisi.",
            password: data.password ? "" : "Kata sandi baru wajib diisi.",
            password_confirmation: data.password_confirmation
                ? ""
                : "Konfirmasi kata sandi wajib diisi.",
        };
        const firstInvalidField = Object.keys(requiredErrors).find(
            (field) => requiredErrors[field],
        );

        if (firstInvalidField) {
            setClientErrors(requiredErrors);
            const inputRefs = {
                current_password: currentPasswordInput,
                password: passwordInput,
                password_confirmation: passwordConfirmationInput,
            };
            inputRefs[firstInvalidField].current?.focus();
            showToast("warning", "Lengkapi semua kolom kata sandi.");

            return;
        }

        setClientErrors({});
        put(route("password.update"), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setClientErrors({});
            },
            onError: (formErrors) => {
                const errorMessage = formErrors.current_password
                    ? formErrors.current_password
                    : "Kata sandi gagal diperbarui. Isi ulang semua kolom dan periksa kembali data Anda.";

                reset(
                    "current_password",
                    "password",
                    "password_confirmation",
                );
                setClientErrors({});
                requestAnimationFrame(() => {
                    currentPasswordInput.current?.focus();
                });
                showToast("error", errorMessage);
            },
        });
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-semibold text-base-content">
                    Perbarui Kata Sandi
                </h2>
                <p className="mt-1 text-sm leading-6 text-base-content/60">
                    Gunakan kata sandi yang panjang, unik, dan tidak digunakan di akun lain.
                </p>
            </header>

            <form onSubmit={updatePassword} className="mt-6 space-y-5">
                <div>
                    <InputLabel
                        htmlFor="current_password"
                        value="Kata Sandi Saat Ini"
                    />
                    <div className="relative mt-1">
                        <TextInput
                            id="current_password"
                            ref={currentPasswordInput}
                            value={data.current_password}
                            onChange={(event) => {
                                setData("current_password", event.target.value);
                                setClientErrors((current) => ({
                                    ...current,
                                    current_password: "",
                                }));
                            }}
                            type={
                                visiblePasswords.current_password
                                    ? "text"
                                    : "password"
                            }
                            className="!mt-0 block w-full pr-12"
                            autoComplete="current-password"
                        />
                        <PasswordVisibilityButton
                            field="current_password"
                            label="kata sandi saat ini"
                        />
                    </div>
                    <InputError
                        message={
                            clientErrors.current_password ||
                            errors.current_password
                        }
                        className="mt-2"
                    />
                </div>

                <div>
                    <InputLabel htmlFor="password" value="Kata Sandi Baru" />
                    <div className="relative mt-1">
                        <TextInput
                            id="password"
                            ref={passwordInput}
                            value={data.password}
                            onChange={(event) => {
                                setData("password", event.target.value);
                                setClientErrors((current) => ({
                                    ...current,
                                    password: "",
                                }));
                            }}
                            type={visiblePasswords.password ? "text" : "password"}
                            className="!mt-0 block w-full pr-12"
                            autoComplete="new-password"
                        />
                        <PasswordVisibilityButton
                            field="password"
                            label="kata sandi baru"
                        />
                    </div>
                    <InputError
                        message={clientErrors.password || errors.password}
                        className="mt-2"
                    />
                </div>

                <div>
                    <InputLabel
                        htmlFor="password_confirmation"
                        value="Konfirmasi Kata Sandi Baru"
                    />
                    <div className="relative mt-1">
                        <TextInput
                            id="password_confirmation"
                            ref={passwordConfirmationInput}
                            value={data.password_confirmation}
                            onChange={(event) => {
                                setData(
                                    "password_confirmation",
                                    event.target.value,
                                );
                                setClientErrors((current) => ({
                                    ...current,
                                    password_confirmation: "",
                                }));
                            }}
                            type={
                                visiblePasswords.password_confirmation
                                    ? "text"
                                    : "password"
                            }
                            className="!mt-0 block w-full pr-12"
                            autoComplete="new-password"
                        />
                        <PasswordVisibilityButton
                            field="password_confirmation"
                            label="konfirmasi kata sandi baru"
                        />
                    </div>
                    <InputError
                        message={
                            clientErrors.password_confirmation ||
                            errors.password_confirmation
                        }
                        className="mt-2"
                    />
                </div>

                <div className="flex items-center gap-4">
                    <PrimaryButton
                        disabled={processing}
                        className="min-h-11 rounded-xl bg-yellow-400 px-5 text-sm font-bold normal-case tracking-normal text-slate-950 hover:bg-yellow-300 focus:bg-yellow-300 active:bg-yellow-400"
                    >
                        Simpan Kata Sandi
                    </PrimaryButton>
                </div>
            </form>
        </section>
    );
}
