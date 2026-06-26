import { usePage } from "@inertiajs/react";
import { useEffect, useRef } from "react";
import Swal from "sweetalert2";

/**
 * Menampilkan flash message (status/success/error) sebagai toast/notif,
 * bukan kotak alert di dalam form. Disisipkan sekali di tiap layout.
 */
export default function FlashToaster() {
    const { flash } = usePage().props;
    const lastShown = useRef(null);

    useEffect(() => {
        if (!flash) return;

        const message = flash.success || flash.status || flash.error;
        if (!message) return;

        const isError = Boolean(flash.error);

        // Hindari toast ganda untuk pesan yang sama.
        const key = (isError ? "error:" : "ok:") + message;
        if (lastShown.current === key) return;
        lastShown.current = key;

        const isDark = (localStorage.getItem("theme") || "dark") !== "light";

        Swal.fire({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            icon: isError ? "error" : "success",
            iconColor: isError ? "#ef4444" : "#34d399",
            title: message,
            background: isDark ? "#0e1629" : "#ffffff",
            color: isDark ? "#dbe4f0" : "#0b1221",
        });
    }, [flash?.success, flash?.status, flash?.error]);

    return null;
}
