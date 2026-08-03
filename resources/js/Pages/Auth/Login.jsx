import Checkbox from "@/Components/Checkbox";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";
import Turnstile from "@/Components/Turnstile";
import GuestLayout from "@/Layouts/GuestLayout";
import { Head, Link, useForm, usePage } from "@inertiajs/react";
import { CircleX, Eye, EyeOff } from "lucide-react";
import { useEffect, useState, useRef } from "react";

export default function Login({ canResetPassword, lockoutSeconds: initialLockout = 0 }) {
    const { turnstileSiteKey } = usePage().props;
    const [showPassword, setShowPassword] = useState(false);
    const [lockoutSeconds, setLockoutSeconds] = useState(initialLockout);

    const [turnstileToken, setTurnstileToken] = useState(null);
    const turnstileRef = useRef(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        password: "",
        remember: false,
        cf_turnstile_response: "",
    });

    const isLockedOut = lockoutSeconds > 0;

    useEffect(() => {
        if (!isLockedOut) {
            return undefined;
        }

        const timer = window.setInterval(() => {
            setLockoutSeconds((seconds) => Math.max(0, seconds - 1));
        }, 1000);

        return () => window.clearInterval(timer);
    }, [isLockedOut]);

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

        if (isLockedOut) {
            return;
        }

        post(route("login"), {
            onError: (responseErrors) => {
                const seconds = Number.parseInt(
                    responseErrors.throttle_seconds,
                    10,
                );

                if (Number.isFinite(seconds) && seconds > 0) {
                    setLockoutSeconds(seconds);
                }

                setTurnstileToken(null);
                setData("cf_turnstile_response", "");
                turnstileRef.current?.reset();
            },
            onFinish: () => {
                reset("password");
            },
        });
    };

    return (
        <GuestLayout>
            <Head title="Login" />

            {/* TITLE */}
            <div className="mb-6 text-center">
                <h1 className="text-2xl font-bold text-yellow-500">
                    Masuk ke Akun
                </h1>
                <p className="text-sm text-base-content/60 mt-1">
                    Silakan login untuk mengakses marketplace
                </p>
            </div>

            {(errors.suspended || isLockedOut) && (
                <div
                    role="alert"
                    aria-live="polite"
                    className="mb-5 flex items-center gap-3 rounded-xl border-2 border-red-600 bg-red-600 px-4 py-3.5 text-white"
                >
                    <CircleX
                        className="h-6 w-6 shrink-0 text-white"
                        aria-hidden="true"
                    />
                    <p className="text-sm font-medium leading-6 text-white">
                        {isLockedOut
                            ? `Login diblokir sementara. Coba lagi dalam ${String(
                                  Math.floor(lockoutSeconds / 60),
                              ).padStart(2, "0")}:${String(
                                  lockoutSeconds % 60,
                              ).padStart(2, "0")}.`
                            : errors.suspended}
                    </p>
                </div>
            )}

            <form onSubmit={submit} className="space-y-4">
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
                        isFocused={true}
                        disabled={isLockedOut}
                        onChange={(e) => setData("email", e.target.value)}
                        placeholder="email@example.com"
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
                            autoComplete="current-password"
                            disabled={isLockedOut}
                            onChange={(e) =>
                                setData("password", e.target.value)
                            }
                            placeholder="••••••••"
                        />
                        <button
                            type="button"
                            disabled={isLockedOut}
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

                    <InputError message={errors.password} className="mt-2" />
                </div>

                {/* REMEMBER */}
                <div className="flex items-center justify-between">
                    <label className="flex items-center gap-2">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            disabled={isLockedOut}
                            onChange={(e) =>
                                setData("remember", e.target.checked)
                            }
                        />
                        <span className="text-sm text-base-content/60">
                            Remember me
                        </span>
                    </label>

                    {canResetPassword && (
                        <Link
                            href={route("password.request")}
                            className="text-sm text-base-content/60 hover:text-yellow-500 transition"
                        >
                            Lupa password?
                        </Link>
                    )}
                </div>

                {/* TURNSTILE CAPTCHA */}
                <div
                    className={`mt-4 ${isLockedOut ? "pointer-events-none opacity-50" : ""}`}
                    aria-disabled={isLockedOut}
                >
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
                    disabled={
                        processing ||
                        isLockedOut ||
                        !data.email.trim() ||
                        !data.password ||
                        !turnstileToken
                    }
                    className="w-full mt-4 py-3 rounded-lg font-semibold tracking-wide transition bg-yellow-500 text-black hover:bg-yellow-600 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {processing ? "Loading..." : "LOG IN"}
                </button>

                {/* REGISTER */}
                <p className="text-center text-sm text-base-content/60 mt-6">
                    Belum punya akun?{" "}
                    <Link
                        href={route("register")}
                        className="text-yellow-500 hover:underline"
                    >
                        Daftar sekarang
                    </Link>
                </p>
            </form>
        </GuestLayout>
    );
}
