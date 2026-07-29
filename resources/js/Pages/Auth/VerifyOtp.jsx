import GuestLayout from "@/Layouts/GuestLayout";
import { Head, useForm } from "@inertiajs/react";
import { useRef, useEffect, useState } from "react";

export default function VerifyOtp({ email, status, throttle, flash }) {
    // Receive throttle and flash
    const { data, setData, post, processing, errors } = useForm({
        otp: "",
    });

    const [digits, setDigits] = useState(["", "", "", "", "", ""]);
    const [countdown, setCountdown] = useState(throttle || 0); // Initialize with server throttle
    const inputRefs = useRef([]);

    useEffect(() => {
        // Update main data.otp when digits change
        setData("otp", digits.join(""));
    }, [digits]);

    // Update countdown if server throttle changes (e.g. on page refresh)
    useEffect(() => {
        if (throttle > 0) {
            setCountdown(throttle);
        }
    }, [throttle]);

    // Countdown Logic
    useEffect(() => {
        if (countdown > 0) {
            const timer = setTimeout(() => setCountdown(countdown - 1), 1000);
            return () => clearTimeout(timer);
        }
    }, [countdown]);

    const handleChange = (index, value) => {
        // Only numbers
        if (!/^\d*$/.test(value)) return;

        const newDigits = [...digits];
        newDigits[index] = value.substring(value.length - 1); // Only take last char
        setDigits(newDigits);

        // Auto focus next
        if (value && index < 5) {
            inputRefs.current[index + 1].focus();
        }
    };

    const handleKeyDown = (index, e) => {
        if (e.key === "Backspace" && !digits[index] && index > 0) {
            inputRefs.current[index - 1].focus();
        }
    };

    const handlePaste = (e) => {
        e.preventDefault();
        const pastedData = e.clipboardData
            .getData("text")
            .slice(0, 6)
            .split("");
        if (pastedData.every((char) => /^\d$/.test(char))) {
            const newDigits = [...digits];
            pastedData.forEach((val, i) => {
                if (i < 6) newDigits[i] = val;
            });
            setDigits(newDigits);
            inputRefs.current[Math.min(pastedData.length, 5)].focus();
        }
    };

    const submit = (e) => {
        e.preventDefault();
        post(route("verification.otp.store"));
    };

    const resendOtp = () => {
        post(route("verification.otp.resend"), {
            preserveScroll: true,
            onSuccess: (page) => {
                setDigits(["", "", "", "", "", ""]);
                inputRefs.current[0].focus();
                // If success, set countdown to 60 (or what the server says if we passed it back,
                // but usually status is enough, we can assume 60s hit or use the throttle prop if updated)
                setCountdown(60);
            },
            onError: () => {
                // If error (rate limit), Inertia might not auto-update props immediately in same way,
                // but page props should handle it.
                // However, RateLimiter error usually comes as a 'message' or 'error' in flash/errors
            },
        });
    };

    return (
        <GuestLayout>
            <Head title="Verifikasi OTP" />

            <div className="text-center mb-8">
                <h1 className="text-2xl font-bold text-yellow-500 mb-2">
                    Verifikasi Akun
                </h1>
                <p className="text-base-content/70 text-sm">
                    Masukkan 6 digit kode yang dikirim ke <br />{" "}
                    <span className="text-base-content font-medium">
                        {email}
                    </span>
                </p>
            </div>

            {status && (
                <div className="mb-6 p-3 bg-success/10 border border-success/20 text-success rounded-lg text-sm text-center">
                    {status}
                </div>
            )}

            {/* Display throttling error if it exists */}
            {flash && flash.error && (
                <div className="mb-6 p-3 bg-error/10 border border-error/20 text-error rounded-lg text-sm text-center">
                    {flash.error}
                </div>
            )}
            {/* Also check for flash error if passed via HandleInertiaRequests or useForm errors if applicable */}

            {errors.otp && (
                <div className="mb-6 p-3 bg-error/10 border border-error/20 text-error rounded-lg text-sm text-center">
                    {errors.otp}
                </div>
            )}

            <form onSubmit={submit}>
                <div className="flex justify-center gap-2 sm:gap-3 mb-8">
                    {digits.map((digit, index) => (
                        <input
                            key={index}
                            ref={(el) => (inputRefs.current[index] = el)}
                            type="text"
                            inputMode="numeric"
                            maxLength={1}
                            value={digit}
                            onChange={(e) =>
                                handleChange(index, e.target.value)
                            }
                            onKeyDown={(e) => handleKeyDown(index, e)}
                            onPaste={handlePaste}
                            className="w-10 h-12 sm:w-12 sm:h-14 text-center text-xl sm:text-2xl font-bold bg-base-200 border border-base-300 rounded-lg text-base-content focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500/50 outline-none transition-all"
                        />
                    ))}
                </div>

                <button
                    disabled={processing || digits.join("").length < 6}
                    className="w-full h-11 rounded-lg bg-yellow-500 hover:bg-yellow-600 text-black font-semibold tracking-wide transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    VERIFIKASI
                </button>
            </form>

            <div className="mt-8 text-center">
                <p className="text-base-content/70 text-sm">
                    Tidak menerima kode?{" "}
                    <button
                        onClick={resendOtp}
                        disabled={processing || countdown > 0}
                        className="text-yellow-500 hover:text-yellow-600 font-medium hover:underline disabled:opacity-50 disabled:no-underline disabled:cursor-not-allowed"
                    >
                        {countdown > 0
                            ? `Kirim Ulang (${Math.floor(countdown)}s)`
                            : "Kirim Ulang"}
                    </button>
                </p>
            </div>
        </GuestLayout>
    );
}
