import Checkbox from "@/Components/Checkbox";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";
import Turnstile from "@/Components/Turnstile";
import GuestLayout from "@/Layouts/GuestLayout";
import { Head, Link, useForm, usePage } from "@inertiajs/react";
import { Eye, EyeOff } from "lucide-react";
import { useState, useEffect } from "react";

export default function Login({ status, canResetPassword }) {
    const { turnstileSiteKey } = usePage().props;
    const [showPassword, setShowPassword] = useState(false);
    const [message, setMessage] = useState(null);

    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        setMessage(params.get("message"));
    }, []);

    const [turnstileToken, setTurnstileToken] = useState(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        email: "",
        password: "",
        remember: false,
        cf_turnstile_response: "",
    });

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

        post(route("login"), {
            onFinish: () => {
                reset("password");
                setTurnstileToken(null);
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

            {status && (
                <div className="mb-4 text-sm text-success bg-success/10 border border-success/20 rounded p-3">
                    {status}
                </div>
            )}

            {message && (
                <div className="mb-6 text-sm text-yellow-600 dark:text-yellow-400 bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-3 flex items-center gap-2">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="h-5 w-5 flex-shrink-0"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fillRule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clipRule="evenodd"
                        />
                    </svg>
                    <span>{message}</span>
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
                            onChange={(e) =>
                                setData("password", e.target.value)
                            }
                            placeholder="••••••••"
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

                    <InputError message={errors.password} className="mt-2" />
                </div>

                {/* REMEMBER */}
                <div className="flex items-center justify-between">
                    <label className="flex items-center gap-2">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
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
                <div className="mt-4">
                    <Turnstile
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
