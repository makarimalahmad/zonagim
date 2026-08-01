import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import Modal from "@/Components/Modal";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import TextInput from "@/Components/TextInput";
import { toastOptions } from "@/utils/notificationTheme";
import { useForm } from "@inertiajs/react";
import { ChevronRight, X } from "lucide-react";
import axios from "axios";
import { useState } from "react";
import Swal from "sweetalert2";

export default function UpdateProfileInformation({
    user,
    mustVerifyEmail,
    status,
    className = "",
}) {
    const [editingField, setEditingField] = useState(null);
    const [regions, setRegions] = useState({
        provinces: [],
        cities: [],
        districts: [],
        villages: [],
    });
    const [loadingRegion, setLoadingRegion] = useState("");

    const initialAddress = {
        country: "Indonesia",
        province_code: "",
        province: "",
        city_code: "",
        city: "",
        district_code: "",
        district: "",
        village_code: "",
        village: "",
        street: "",
        zip: "",
        ...(typeof user.address === "object" && user.address !== null
            ? user.address
            : {}),
    };

    const { data, setData, patch, errors, processing, reset, clearErrors } =
        useForm({
            name: user.name,
            email: user.email,
            phone: user.phone || "",
            address: initialAddress,
        });

    const loadRegions = async (type, parent = "") => {
        setLoadingRegion(type);

        try {
            const response = await axios.get(route("profile.regions"), {
                params: { type, parent },
            });
            setRegions((current) => ({ ...current, [type]: response.data }));
        } catch {
            showErrorToast("Data wilayah gagal dimuat. Silakan coba lagi.");
        } finally {
            setLoadingRegion("");
        }
    };

    const openModal = (field) => {
        setEditingField(field);
        clearErrors();
        if (field === "address") {
            setData("address", initialAddress);
            loadRegions("provinces");
            if (initialAddress.province_code) {
                loadRegions("cities", initialAddress.province_code);
            }
            if (initialAddress.city_code) {
                loadRegions("districts", initialAddress.city_code);
            }
            if (initialAddress.district_code) {
                loadRegions("villages", initialAddress.district_code);
            }
        } else {
            setData(field, user[field] || "");
        }
    };

    const changeRegion = (type, event) => {
        const code = event.target.value;
        const name = event.target.selectedOptions[0]?.dataset.name || "";
        const resets = {
            province: {
                province_code: code,
                province: name,
                city_code: "",
                city: "",
                district_code: "",
                district: "",
                village_code: "",
                village: "",
                zip: "",
            },
            city: {
                city_code: code,
                city: name,
                district_code: "",
                district: "",
                village_code: "",
                village: "",
                zip: "",
            },
            district: {
                district_code: code,
                district: name,
                village_code: "",
                village: "",
                zip: "",
            },
            village: { village_code: code, village: name, zip: "" },
        };

        setData("address", { ...data.address, ...resets[type] });

        if (code && type === "province") loadRegions("cities", code);
        if (code && type === "city") loadRegions("districts", code);
        if (code && type === "district") loadRegions("villages", code);
    };

    const closeModal = () => {
        setEditingField(null);
        reset();
    };

    const showErrorToast = (message) => {
        Swal.fire(toastOptions("error", message));
    };

    const submit = (event) => {
        event.preventDefault();
        patch(route("profile.update"), {
            preserveScroll: true,
            onSuccess: () => {
                setEditingField(null);
            },
            onError: (formErrors) => {
                const fieldError =
                    formErrors.name ||
                    formErrors.phone ||
                    formErrors["address.zip"] ||
                    formErrors["address.province_code"] ||
                    formErrors["address.province"] ||
                    formErrors["address.city_code"] ||
                    formErrors["address.city"] ||
                    formErrors["address.district_code"] ||
                    formErrors["address.district"] ||
                    formErrors["address.village_code"] ||
                    formErrors["address.village"] ||
                    formErrors["address.street"] ||
                    formErrors.address;

                showErrorToast(
                    fieldError ||
                        "Informasi profil gagal diperbarui. Periksa kembali data yang Anda masukkan.",
                );
            },
        });
    };

    const formatDate = (dateString) => {
        if (!dateString) {
            return "-";
        }

        const date = new Date(dateString);

        if (Number.isNaN(date.getTime())) {
            return "-";
        }

        return new Intl.DateTimeFormat("id-ID", {
            day: "numeric",
            month: "long",
            year: "numeric",
        }).format(date);
    };

    const formatAddress = (addr) => {
        if (!addr || typeof addr !== "object") return "-";

        const lines = [
            addr.street,
            [addr.village, addr.district].filter(Boolean).join(", "),
            [addr.city, addr.province].filter(Boolean).join(", "),
            addr.zip ? `Kode Pos ${addr.zip}` : "",
            addr.country,
        ].filter(Boolean);

        if (lines.length === 0) return "-";

        return (
            <address className="flex max-w-xl flex-col items-start gap-1 text-left not-italic leading-relaxed sm:items-end sm:text-right">
                {lines.map((line, index) => (
                    <span
                        key={`${line}-${index}`}
                        className={index === 0 ? "font-semibold" : "font-normal"}
                    >
                        {line}
                    </span>
                ))}
            </address>
        );
    };

    const ListItem = ({ label, value, field, isEditable = true }) => (
        <div
            onClick={() => isEditable && openModal(field)}
            className={`grid grid-cols-[minmax(0,1fr)_1.25rem] items-center gap-x-3 gap-y-1.5 border-b border-base-300 p-4 transition-colors last:border-0 sm:flex sm:justify-between ${isEditable ? "cursor-pointer hover:bg-base-200/50" : ""}`}
        >
            <div className="min-w-0 text-sm font-medium text-base-content/70 sm:w-1/3 sm:text-base">
                {label}
            </div>
            <div className="col-start-1 row-start-2 min-w-0 text-left text-sm font-semibold text-base-content wrap-break-word sm:flex sm:flex-1 sm:justify-end sm:px-4 sm:text-right sm:text-base">
                {value}
            </div>
            <div
                className={`col-start-2 row-span-2 row-start-1 flex w-5 justify-end text-base-content/50 sm:w-6 ${!isEditable && "invisible"}`}
            >
                <ChevronRight className="w-5 h-5" />
            </div>
        </div>
    );

    return (
        <section className={className}>
            <header className="mb-4">
                <h2 className="text-lg font-semibold text-base-content">
                    Informasi Profil
                </h2>
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
                        <PrimaryButton
                            className="!bg-yellow-400 !text-yellow-950 hover:!bg-yellow-300 focus:!bg-yellow-300 active:!bg-yellow-500"
                            disabled={processing}
                        >
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
                        <InputLabel htmlFor="phone" value="Nomor WhatsApp" />
                        <div className="mt-1 flex h-12 items-stretch">
                            <span className="inline-flex w-16 shrink-0 items-center justify-center rounded-l-lg border border-r-0 border-base-300 bg-base-200 text-sm text-base-content/70">
                                +62
                            </span>
                            <TextInput
                                id="phone"
                                type="tel"
                                inputMode="numeric"
                                autoComplete="tel-national"
                                className="!mt-0 !h-12 min-w-0 flex-1 !rounded-l-none !rounded-r-lg"
                                value={data.phone}
                                onChange={(e) =>
                                    setData(
                                        "phone",
                                        e.target.value.replace(/\D/g, ""),
                                    )
                                }
                                placeholder="81234567890"
                            />
                        </div>
                        <p className="mt-2 text-xs text-base-content/50">
                            Gunakan format 8xxxxxxxxxx tanpa angka 0 atau +62.
                        </p>
                        <InputError className="mt-2" message={errors.phone} />
                    </div>

                    <div className="mt-6 flex justify-end gap-3">
                        <SecondaryButton onClick={closeModal}>
                            Batal
                        </SecondaryButton>
                        <PrimaryButton
                            className="!bg-yellow-400 !text-yellow-950 hover:!bg-yellow-300 focus:!bg-yellow-300 active:!bg-yellow-500"
                            disabled={processing}
                        >
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

                        <div>
                            <InputLabel htmlFor="province" value="Provinsi" />
                            <select
                                id="province"
                                className="mt-1 block w-full rounded-lg border-base-300 bg-base-100 cursor-pointer disabled:cursor-not-allowed"
                                value={data.address.province_code}
                                onChange={(event) => changeRegion("province", event)}
                                disabled={loadingRegion === "provinces"}
                            >
                                <option value="">Pilih provinsi</option>
                                {regions.provinces.map((region) => (
                                    <option key={region.code} value={region.code} data-name={region.name}>
                                        {region.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <InputLabel htmlFor="city" value="Kabupaten/Kota" />
                            <select
                                id="city"
                                className="mt-1 block w-full rounded-lg border-base-300 bg-base-100 cursor-pointer disabled:cursor-not-allowed"
                                value={data.address.city_code}
                                onChange={(event) => changeRegion("city", event)}
                                disabled={!data.address.province_code}
                            >
                                <option value="">
                                    {loadingRegion === "cities"
                                        ? "Memuat kabupaten/kota..."
                                        : "Pilih kabupaten/kota"}
                                </option>
                                {regions.cities.map((region) => (
                                    <option key={region.code} value={region.code} data-name={region.name}>
                                        {region.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <InputLabel htmlFor="district" value="Kecamatan" />
                            <select
                                id="district"
                                className="mt-1 block w-full rounded-lg border-base-300 bg-base-100 cursor-pointer disabled:cursor-not-allowed"
                                value={data.address.district_code}
                                onChange={(event) => changeRegion("district", event)}
                                disabled={!data.address.city_code}
                            >
                                <option value="">
                                    {loadingRegion === "districts"
                                        ? "Memuat kecamatan..."
                                        : "Pilih kecamatan"}
                                </option>
                                {regions.districts.map((region) => (
                                    <option key={region.code} value={region.code} data-name={region.name}>
                                        {region.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <InputLabel htmlFor="village" value="Kelurahan/Desa" />
                            <select
                                id="village"
                                className="mt-1 block w-full rounded-lg border-base-300 bg-base-100 cursor-pointer disabled:cursor-not-allowed"
                                value={data.address.village_code}
                                onChange={(event) => changeRegion("village", event)}
                                disabled={!data.address.district_code}
                            >
                                <option value="">
                                    {loadingRegion === "villages"
                                        ? "Memuat kelurahan/desa..."
                                        : "Pilih kelurahan/desa"}
                                </option>
                                {regions.villages.map((region) => (
                                    <option key={region.code} value={region.code} data-name={region.name}>
                                        {region.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Street Address */}
                        <div>
                            <InputLabel htmlFor="street" value="Alamat Lengkap" />
                            <TextInput
                                id="street"
                                autoComplete="street-address"
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
                            <InputLabel htmlFor="zip" value="Kode Pos" />
                            <TextInput
                                id="zip"
                                inputMode="numeric"
                                autoComplete="postal-code"
                                className="mt-1 block w-full"
                                value={data.address.zip}
                                onChange={(e) =>
                                    setData("address", {
                                        ...data.address,
                                        zip: e.target.value
                                            .replace(/\D/g, "")
                                            .slice(0, 5),
                                    })
                                }
                                placeholder="Kode Pos"
                            />
                        </div>
                    </div>
                    <InputError className="mt-2" message={errors.address} />
                    <InputError
                        className="mt-2"
                        message={
                            errors["address.zip"] ||
                            errors["address.province"] ||
                            errors["address.city"] ||
                            errors["address.street"]
                        }
                    />

                    <div className="mt-6 flex justify-end gap-3">
                        <SecondaryButton onClick={closeModal}>
                            Batal
                        </SecondaryButton>
                        <PrimaryButton
                            className="!bg-yellow-400 !text-yellow-950 hover:!bg-yellow-300 focus:!bg-yellow-300 active:!bg-yellow-500"
                            disabled={processing}
                        >
                            Simpan
                        </PrimaryButton>
                    </div>
                </form>
            </Modal>
        </section>
    );
}
