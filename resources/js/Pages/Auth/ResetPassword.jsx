import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';
import { Eye, EyeOff, Check } from 'lucide-react';
import { useState } from 'react';

export default function ResetPassword({ token, email }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token: token,
        email: email,
        password: '',
        password_confirmation: '',
    });

    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);

    // Password Strength Logic
    const strength = {
        length: data.password.length >= 8,
        mixedCase: /[a-z]/.test(data.password) && /[A-Z]/.test(data.password),
        number: /\d/.test(data.password),
        symbol: /[!@#$%^&*(),.?":{}|<>]/.test(data.password),
    };

    const submit = (e) => {
        e.preventDefault();

        post(route('password.store'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    const RequirementItem = ({ met, text }) => (
        <div className={`flex items-center gap-2 text-xs transition-colors ${met ? "text-success" : "text-base-content/50"}`}>
            {met ? <Check size={14} className="shrink-0" /> : <div className="w-3.5 h-3.5 rounded-full border border-base-content/30 shrink-0" />}
            <span>{text}</span>
        </div>
    );

    return (
        <GuestLayout>
            <Head title="Reset Password" />

            {/* TITLE */}
            <div className="mb-6 text-center">
                <h1 className="text-2xl font-bold text-yellow-500">
                    Atur Ulang Password
                </h1>
                <p className="text-sm text-base-content/60 mt-1">
                    Silakan buat password baru untuk akun Anda
                </p>
            </div>

            <form onSubmit={submit} className="space-y-4">
                <div>
                    <InputLabel htmlFor="email" value="Email" className="text-base-content/80 mb-2 block" />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="!mt-0 bg-base-200/50 text-base-content/50 cursor-not-allowed opacity-75"
                        autoComplete="username"
                        onChange={(e) => setData('email', e.target.value)}
                        readOnly
                        disabled
                    />

                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Password" className="text-base-content/80 mb-2 block" />

                    <div className="relative mt-2">
                        <TextInput
                            id="password"
                            type={showPassword ? "text" : "password"}
                            name="password"
                            value={data.password}
                            className="pr-12 !mt-0"
                            autoComplete="new-password"
                            isFocused={true}
                            onChange={(e) => setData('password', e.target.value)}
                             placeholder="Password baru"
                        />
                        <button
                            type="button"
                            onClick={() => setShowPassword(!showPassword)}
                            className="absolute inset-y-0 right-0 flex items-center pr-4 text-base-content/40 hover:text-base-content/70 transition"
                        >
                            {showPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                        </button>
                    </div>

                    {/* Check Strength Indicator */}
                    <div className="mt-3 grid grid-cols-2 gap-2 bg-base-200 p-3 rounded-lg border border-base-300">
                        <RequirementItem met={strength.length} text="Minimal 8 karakter" />
                        <RequirementItem met={strength.mixedCase} text="Huruf Besar & Kecil" />
                        <RequirementItem met={strength.number} text="Angka (0-9)" />
                        <RequirementItem met={strength.symbol} text="Simbol (!@#$)" />
                    </div>

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel
                        htmlFor="password_confirmation"
                        value="Confirm Password"
                        className="text-base-content/80 mb-2 block"
                    />

                    <div className="relative mt-2">
                        <TextInput
                            type={showConfirmPassword ? "text" : "password"}
                            id="password_confirmation"
                            name="password_confirmation"
                            value={data.password_confirmation}
                            className="pr-12 !mt-0"
                            autoComplete="new-password"
                            onChange={(e) =>
                                setData('password_confirmation', e.target.value)
                            }
                            placeholder="Ulangi password baru"
                        />
                         <button
                            type="button"
                            onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                             className="absolute inset-y-0 right-0 flex items-center pr-4 text-base-content/40 hover:text-base-content/70 transition"
                        >
                            {showConfirmPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                        </button>
                    </div>

                    <InputError
                        message={errors.password_confirmation}
                        className="mt-2"
                    />
                </div>

                <div className="flex items-center justify-end mt-4">
                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full py-3 rounded-lg bg-yellow-500 text-black font-semibold tracking-wide hover:bg-yellow-600 transition disabled:opacity-60"
                    >
                        {processing ? "Resetting..." : "RESET PASSWORD"}
                    </button>
                </div>
            </form>
        </GuestLayout>
    );
}
