import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import Modal from "@/Components/Modal";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import TextInput from "@/Components/TextInput";
import { useForm, usePage } from "@inertiajs/react";
import { ChevronRight, X } from "lucide-react";
import { useState } from "react";

export default function UpdateProfileInformation({
    mustVerifyEmail,
    status,
    className = "",
}) {
    const user = usePage().props.auth.user;
    const [editingField, setEditingField] = useState(null);

    // Ensure address is an object, handle potential null or string legacy data
    const initialAddress =
        typeof user.address === "object" && user.address !== null
            ? user.address
            : {
                  country: "Indonesia",
                  province: "",
                  city: "",
                  street: "",
                  zip: "",
              };

    const { data, setData, patch, errors, processing, reset, clearErrors } =
        useForm({
            name: user.name,
            email: user.email,
            phone: user.phone || "",
            address: initialAddress,
        });

    const openModal = (field) => {
        setEditingField(field);
        clearErrors();
        if (field === "address") {
            // For address, we need to ensure we set the object correctly from user props if it changed,
            // but 'user.address' from props might be stale if we didn't full reload.
            // Actually, 'reset()' in closeModal handles reverting to initial props.
            // Here we might just want to set data to current user state.
            setData("address", initialAddress);
        } else {
            setData(field, user[field] || "");
        }
    };

    const closeModal = () => {
        setEditingField(null);
        reset();
    };

    const submit = (e) => {
        e.preventDefault();
        patch(route("profile.update"), {
            onSuccess: () => {
                setEditingField(null);
            },
        });
    };

    const formatDate = (dateString) => {
        const options = {
            day: "numeric",
            month: "long",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        };
        return new Date(dateString)
            .toLocaleDateString("id-ID", options)
            .replace("pukul", "")
            .replace(".", ":")
            .trim();
    };

    const formatAddress = (addr) => {
        if (!addr || typeof addr !== "object") return "-";
        const parts = [
            addr.street,
            addr.city,
            addr.province,
            addr.country,
        ].filter(Boolean);
        return parts.length > 0 ? parts.join(", ") : "-";
    };

    const ListItem = ({ label, value, field, isEditable = true }) => (
        <div
            onClick={() => isEditable && openModal(field)}
            className={`flex items-center justify-between p-4 border-b border-base-300 last:border-0 transition-colors ${isEditable ? "cursor-pointer hover:bg-base-200/50" : ""}`}
        >
            <div className="w-1/3 text-base-content/70 font-medium break-words">
                {label}
            </div>
            <div className="flex-1 text-right text-base-content font-semibold break-words px-4">
                {value}
            </div>
            <div
                className={`w-6 flex justify-end text-base-content/50 ${!isEditable && "invisible"}`}
            >
                <ChevronRight className="w-5 h-5" />
            </div>
        </div>
    );

    return (
        <section className={className}>
            <header className="mb-6">
                <h2 className="text-lg font-medium text-base-content">
                    Profile Information
                </h2>
                <p className="mt-1 text-sm text-base-content/70">
                    Detail berikut akan disertakan dalam invoice transaksi Anda.
                </p>
            </header>

            <div className="bg-base-100 rounded-lg border border-base-300 overflow-hidden">
                <ListItem label="Nama" value={user.name} field="name" />
                <ListItem
                    label="Email"
                    value={user.email}
                    field="email"
                    isEditable={false}
                />
                <ListItem
                    label="Nomor Telepon"
                    value={user.phone ? `+62 ${user.phone}` : "-"}
                    field="phone"
                />
                <ListItem
                    label="Alamat"
                    value={formatAddress(user.address)}
                    field="address"
                />
                <ListItem
                    label="Bergabung Sejak"
                    value={formatDate(user.created_at)}
                    isEditable={false}
                />
            </div>

            {/* MODAL EDIT NAMA */}
            <Modal show={editingField === "name"} onClose={closeModal}>
                <form onSubmit={submit} className="p-6">
                    <div className="flex justify-between items-center mb-4">
                        <h2 className="text-lg font-medium text-base-content">
                            Perbarui Nama Anda
                        </h2>
                        <button
                            type="button"
                            onClick={closeModal}
                            className="text-base-content/50 hover:text-base-content"
                        >
                            <X className="w-5 h-5" />
                        </button>
                    </div>

                    <div className="mt-4">
                        <InputLabel htmlFor="name" value="Nama Lengkap" />
                        <TextInput
                            id="name"
                            className="mt-1 block w-full"
                            value={data.name}
                            onChange={(e) => setData("name", e.target.value)}
                            required
                            isFocused
                        />
                        <InputError className="mt-2" message={errors.name} />
                    </div>

                    <div className="mt-6 flex justify-end gap-3">
                        <SecondaryButton onClick={closeModal}>
                            Batal
                        </SecondaryButton>
                        <PrimaryButton disabled={processing}>
                            Simpan
                        </PrimaryButton>
                    </div>
                </form>
            </Modal>

            {/* MODAL EDIT TELEPON */}
            <Modal show={editingField === "phone"} onClose={closeModal}>
                <form onSubmit={submit} className="p-6">
                    <div className="flex justify-between items-center mb-4">
                        <h2 className="text-lg font-medium text-base-content">
                            Perbarui Nomor Telepon
                        </h2>
                        <button
                            type="button"
                            onClick={closeModal}
                            className="text-base-content/50 hover:text-base-content"
                        >
                            <X className="w-5 h-5" />
                        </button>
                    </div>

                    <div className="mt-4">
                        <InputLabel htmlFor="phone" value="WhatsApp Number" />
                        <div className="mt-1 flex rounded-md shadow-sm">
                            <span className="inline-flex items-center px-3 rounded-l-md border border-r-0 border-base-300 bg-base-200 text-base-content/70 sm:text-sm">
                                +62
                            </span>
                            <TextInput
                                id="phone"
                                type="text"
                                className="flex-1 block w-full rounded-none rounded-r-md"
                                value={data.phone}
                                onChange={(e) =>
                                    setData("phone", e.target.value)
                                }
                                placeholder="81234567890"
                            />
                        </div>
                        <InputError className="mt-2" message={errors.phone} />
                    </div>

                    <div className="mt-6 flex justify-end gap-3">
                        <SecondaryButton onClick={closeModal}>
                            Batal
                        </SecondaryButton>
                        <PrimaryButton disabled={processing}>
                            Simpan
                        </PrimaryButton>
                    </div>
                </form>
            </Modal>

            {/* MODAL EDIT ALAMAT */}
            <Modal show={editingField === "address"} onClose={closeModal}>
                <form onSubmit={submit} className="p-6">
                    <div className="flex justify-between items-center mb-4">
                        <h2 className="text-lg font-medium text-base-content">
                            Perbarui Alamat Anda
                        </h2>
                        <button
                            type="button"
                            onClick={closeModal}
                            className="text-base-content/50 hover:text-base-content"
                        >
                            <X className="w-5 h-5" />
                        </button>
                    </div>

                    <div className="mt-4 space-y-4">
                        {/* Country */}
                        <div>
                            <InputLabel htmlFor="country" value="Negara" />
                            <TextInput
                                id="country"
                                className="mt-1 block w-full opacity-70 bg-base-200 cursor-not-allowed text-base-content/70"
                                value="Indonesia"
                                readOnly
                                disabled
                            />
                        </div>

                        {/* Province */}
                        <div>
                            <TextInput
                                id="province"
                                className="mt-1 block w-full"
                                value={data.address.province}
                                onChange={(e) =>
                                    setData("address", {
                                        ...data.address,
                                        province: e.target.value,
                                    })
                                }
                                placeholder="Kabupaten/Provinsi/Negara (opsional)"
                            />
                        </div>

                        {/* City */}
                        <div>
                            <TextInput
                                id="city"
                                className="mt-1 block w-full"
                                value={data.address.city}
                                onChange={(e) =>
                                    setData("address", {
                                        ...data.address,
                                        city: e.target.value,
                                    })
                                }
                                placeholder="Kota"
                            />
                        </div>

                        {/* Street Address */}
                        <div>
                            <TextInput
                                id="street"
                                className="mt-1 block w-full"
                                value={data.address.street}
                                onChange={(e) =>
                                    setData("address", {
                                        ...data.address,
                                        street: e.target.value,
                                    })
                                }
                                placeholder="Alamat (Jalan, No. Rumah)"
                            />
                        </div>

                        {/* Zip Code */}
                        <div>
                            <TextInput
                                id="zip"
                                className="mt-1 block w-full"
                                value={data.address.zip}
                                onChange={(e) =>
                                    setData("address", {
                                        ...data.address,
                                        zip: e.target.value,
                                    })
                                }
                                placeholder="Kode Pos"
                            />
                        </div>
                    </div>

                    <div className="mt-6 flex justify-end gap-3">
                        <SecondaryButton onClick={closeModal}>
                            Batal
                        </SecondaryButton>
                        <PrimaryButton disabled={processing}>
                            Lanjutkan
                        </PrimaryButton>
                    </div>
                </form>
            </Modal>
        </section>
    );
}
