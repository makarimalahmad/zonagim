import DangerButton from "@/Components/DangerButton";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import Modal from "@/Components/Modal";
import SecondaryButton from "@/Components/SecondaryButton";
import TextInput from "@/Components/TextInput";
import { useForm } from "@inertiajs/react";
import { useRef, useState } from "react";

export default function DeleteUserForm({ className = "" }) {
    const [confirmingUserDeletion, setConfirmingUserDeletion] = useState(false);
    const passwordInput = useRef();
    const {
        data,
        setData,
        delete: destroy,
        processing,
        reset,
        errors,
        clearErrors,
    } = useForm({
        password: "",
    });

    const confirmUserDeletion = () => {
        setConfirmingUserDeletion(true);
    };

    const closeModal = () => {
        setConfirmingUserDeletion(false);
        clearErrors();
        reset();
    };

    const deleteUser = (event) => {
        event.preventDefault();

        destroy(route("profile.destroy"), {
            preserveScroll: true,
            onSuccess: closeModal,
            onError: () => passwordInput.current.focus(),
            onFinish: () => reset(),
        });
    };

    return (
        <section className={`space-y-6 ${className}`}>
            <header>
                <h2 className="text-lg font-semibold text-base-content">
                    Hapus Akun
                </h2>
                <p className="mt-1 text-sm leading-6 text-base-content/60">
                    Penghapusan bersifat permanen. Seluruh data akun Anda akan dihapus dan tidak dapat dipulihkan.
                </p>
            </header>

            <DangerButton onClick={confirmUserDeletion}>
                Hapus Akun
            </DangerButton>

            <Modal show={confirmingUserDeletion} onClose={closeModal}>
                <form
                    onSubmit={deleteUser}
                    className="bg-base-100 p-6 text-base-content"
                >
                    <h2 className="text-lg font-semibold text-base-content">
                        Hapus akun secara permanen?
                    </h2>
                    <p className="mt-2 text-sm leading-6 text-base-content/60">
                        Tindakan ini tidak dapat dibatalkan. Masukkan kata sandi untuk mengonfirmasi penghapusan akun.
                    </p>

                    <div className="mt-6">
                        <InputLabel
                            htmlFor="delete_account_password"
                            value="Kata Sandi"
                        />
                        <TextInput
                            id="delete_account_password"
                            type="password"
                            name="password"
                            ref={passwordInput}
                            value={data.password}
                            onChange={(event) =>
                                setData("password", event.target.value)
                            }
                            className="mt-1 block w-full"
                            isFocused
                            placeholder="Masukkan kata sandi"
                            autoComplete="current-password"
                        />
                        <InputError
                            message={errors.password}
                            className="mt-2"
                        />
                    </div>

                    <div className="mt-6 flex justify-end gap-3">
                        <SecondaryButton onClick={closeModal}>
                            Batal
                        </SecondaryButton>
                        <DangerButton disabled={processing}>
                            Hapus Permanen
                        </DangerButton>
                    </div>
                </form>
            </Modal>
        </section>
    );
}
