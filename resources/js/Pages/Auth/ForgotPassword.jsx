import InputError from "@/Components/InputError";
import TextInput from "@/Components/TextInput";
import GuestLayout from "@/Layouts/GuestLayout";
import { Head, useForm, Link } from "@inertiajs/react";

export default function ForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({
        email: "",
    });

    const submit = (e) => {
        e.preventDefault();
        post(route("password.email"));
    };

    return (
        <GuestLayout>
            <Head title="Lupa Password" />

            {/* TITLE */}
            <div className="mb-6 text-center">
                <h1 className="text-2xl font-bold text-yellow-500">
                    Lupa Password?
                </h1>
                <p className="text-sm text-base-content/60 mt-1">
                    Masukkan email Anda untuk menerima link reset password
                </p>
            </div>

            {/* STATUS */}
            {status && (
                <div className="mb-4 text-sm text-success bg-success/10 border border-success/20 rounded p-3">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="space-y-4">
                {/* EMAIL */}
                <div>
                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="!mt-0"
                        placeholder="Email aktif Anda"
                        isFocused={true}
                        onChange={(e) => setData("email", e.target.value)}
                        required
                    />

                    <InputError message={errors.email} className="mt-2" />
                </div>

                {/* BUTTON */}
                <button
                    type="submit"
                    disabled={processing}
                    className="w-full mt-4 py-3 rounded-lg bg-yellow-500 text-black font-semibold tracking-wide hover:bg-yellow-600 transition disabled:opacity-60"
                >
                    {processing ? "Mengirim..." : "Kirim Link Reset"}
                </button>

                {/* BACK TO LOGIN */}
                <p className="text-center text-sm text-base-content/60 mt-6">
                    Ingat password?{" "}
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
