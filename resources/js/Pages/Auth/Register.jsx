import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";
import Turnstile from "@/Components/Turnstile";
import GuestLayout from "@/Layouts/GuestLayout";
import { Head, Link, useForm, usePage } from "@inertiajs/react";
import { Eye, EyeOff, Check } from "lucide-react";
import { useState, useRef } from "react";
import Checkbox from "@/Components/Checkbox";

export default function Register() {
    const { turnstileSiteKey } = usePage().props;
    const { data, setData, post, processing, errors, reset, clearErrors } =
        useForm({
            name: "",
            email: "",
            password: "",
            password_confirmation: "",
            cf_turnstile_response: "",
        });

    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);
    const [agreed, setAgreed] = useState(false);
    const [turnstileToken, setTurnstileToken] = useState(null);
    const turnstileRef = useRef(null);

    const strength = {
        length: data.password.length >= 8,
        mixedCase: /[a-z]/.test(data.password) && /[A-Z]/.test(data.password),
        number: /\d/.test(data.password),
        symbol: /[!@#$%^&*(),.?":{}|<>]/.test(data.password),
    };

    const handleTurnstileVerify = (token) => {
        setTurnstileToken(token);
        setData("cf_turnstile_response", token);
    };

    const handleTurnstileError = () => {
        setTurnstileToken(null);
        setData("cf_turnstile_response", "");
    };

    const handleTurnstileExpire = () => {
        setTurnstileToken(null);
        setData("cf_turnstile_response", "");
    };

    const submit = (e) => {
        e.preventDefault();
        post(route("register"), {
            onError: () => {
                // Token Turnstile sekali pakai & hangus setelah submit.
                // Reset widget supaya menerbitkan token baru → tombol aktif lagi.
                setTurnstileToken(null);
                setData("cf_turnstile_response", "");
                turnstileRef.current?.reset();
            },
        });
    };

    const RequirementItem = ({ met, text }) => (
        <div
            className={`flex items-center gap-2 text-xs transition-colors ${met ? "text-success" : "text-base-content/50"}`}
        >
            {met ? (
                <Check size={14} className="flex-shrink-0" />
            ) : (
                <div className="w-3.5 h-3.5 rounded-full border border-base-content/30 flex-shrink-0" />
            )}
            <span>{text}</span>
        </div>
    );

    return (
        <GuestLayout>
            <Head title="Register" />

            {/* TITLE */}
            <div className="mb-6 text-center">
                <h1 className="text-2xl font-bold text-yellow-500">
                    Daftar Akun Baru
                </h1>
                <p className="text-sm text-base-content/60 mt-1">
                    Buat akun untuk mulai jual & beli akun game
                </p>
            </div>

            <form onSubmit={submit} className="space-y-5">
                {/* NAME */}
                <div>
                    <InputLabel
                        htmlFor="name"
                        value="Nama Lengkap"
                        className="text-base-content/80 mb-2 block"
                    />

                    <TextInput
                        id="name"
                        name="name"
                        value={data.name}
                        className="!mt-0"
                        autoComplete="name"
                        isFocused={true}
                        onChange={(e) => setData("name", e.target.value)}
                        placeholder="Masukkan nama lengkap"
                        required
                    />

                    <InputError message={errors.name} className="mt-2" />
                </div>

                {/* EMAIL */}
                <div>
                    <InputLabel
                        htmlFor="email"
                        value="Email"
                        className="text-base-content/80 mb-2 block"
                    />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="!mt-0"
                        autoComplete="username"
                        onChange={(e) => setData("email", e.target.value)}
                        placeholder="email@example.com"
                        required
                    />

                    <InputError message={errors.email} className="mt-2" />
                </div>

                {/* PASSWORD */}
                <div>
                    <InputLabel
                        htmlFor="password"
                        value="Password"
                        className="text-base-content/80 mb-2 block"
                    />

                    <div className="relative mt-2">
                        <TextInput
                            id="password"
                            type={showPassword ? "text" : "password"}
                            name="password"
                            value={data.password}
                            className="pr-12 !mt-0"
                            autoComplete="new-password"
                            onChange={(e) => {
                                setData("password", e.target.value);
                                clearErrors("password");
                            }}
                            placeholder="Masukan Password"
                            required
                        />
                        <button
                            type="button"
                            onClick={() => setShowPassword(!showPassword)}
                            className="absolute inset-y-0 right-0 flex items-center pr-4 text-base-content/40 hover:text-base-content/70 transition"
                        >
                            {showPassword ? (
                                <EyeOff className="w-5 h-5" />
                            ) : (
                                <Eye className="w-5 h-5" />
                            )}
                        </button>
                    </div>

                    {/* Check Strength Indicator */}
                    <div className="mt-3 grid grid-cols-2 gap-2 bg-base-200 p-3 rounded-lg border border-base-300">
                        <RequirementItem
                            met={strength.length}
                            text="Minimal 8 karakter"
                        />
                        <RequirementItem
                            met={strength.mixedCase}
                            text="Huruf Besar & Kecil"
                        />
                        <RequirementItem
                            met={strength.number}
                            text="Angka (0-9)"
                        />
                        <RequirementItem
                            met={strength.symbol}
                            text="Simbol (!@#$)"
                        />
                    </div>

                    <InputError message={errors.password} className="mt-2" />
                </div>

                {/* CONFIRM PASSWORD */}
                <div>
                    <InputLabel
                        htmlFor="password_confirmation"
                        value="Konfirmasi Password"
                        className="text-base-content/80 mb-2 block"
                    />

                    <div className="relative mt-2">
                        <TextInput
                            id="password_confirmation"
                            type={showConfirmPassword ? "text" : "password"}
                            name="password_confirmation"
                            value={data.password_confirmation}
                            className="pr-12 !mt-0"
                            autoComplete="new-password"
                            onChange={(e) => {
                                setData("password_confirmation", e.target.value);
                                clearErrors("password_confirmation");
                            }}
                            placeholder="Ulangi password"
                            required
                        />
                        <button
                            type="button"
                            onClick={() =>
                                setShowConfirmPassword(!showConfirmPassword)
                            }
                            className="absolute inset-y-0 right-0 flex items-center pr-4 text-base-content/40 hover:text-base-content/70 transition"
                        >
                            {showConfirmPassword ? (
                                <EyeOff className="w-5 h-5" />
                            ) : (
                                <Eye className="w-5 h-5" />
                            )}
                        </button>
                    </div>

                    <InputError
                        message={errors.password_confirmation}
                        className="mt-2"
                    />
                </div>

                {/* TERMS OF SERVICE */}
                <div className="block mt-4">
                    <label className="flex items-center">
                        <Checkbox
                            name="tos"
                            checked={agreed}
                            onChange={(e) => setAgreed(e.target.checked)}
                            disabled={processing}
                        />
                        <span className="ms-2 text-sm text-base-content/60">
                            I agree to the{" "}
                            <a
                                target="_blank"
                                href={route("terms")}
                                className="underline text-sm text-yellow-500 hover:text-yellow-600 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                            >
                                Lapakakunid Terms of Service
                            </a>
                        </span>
                    </label>
                </div>

                {/* TURNSTILE CAPTCHA */}
                <div className="mt-4">
                    <Turnstile
                        ref={turnstileRef}
                        siteKey={turnstileSiteKey}
                        onVerify={handleTurnstileVerify}
                        onError={handleTurnstileError}
                        onExpire={handleTurnstileExpire}
                        theme="auto"
                    />
                    <InputError
                        message={errors.cf_turnstile_response}
                        className="mt-2"
                    />
                </div>

                {/* BUTTON */}
                <button
                    type="submit"
                    disabled={!agreed || processing || !turnstileToken}
                    className="w-full mt-4 py-3 rounded-lg bg-yellow-500 text-black font-semibold tracking-wide hover:bg-yellow-600 transition disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    {processing ? "Loading..." : "REGISTER"}
                </button>

                {/* LOGIN LINK */}
                <p className="text-center text-sm text-base-content/60 mt-6">
                    Sudah punya akun?{" "}
                    <Link
                        href={route("login")}
                        className="text-yellow-500 hover:underline"
                    >
                        Login
                    </Link>
                </p>
            </form>
        </GuestLayout>
    );
}
