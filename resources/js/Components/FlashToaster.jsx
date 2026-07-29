import { toastOptions } from "@/utils/notificationTheme";
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

        const icon = flash.error
            ? "error"
            : flash.status
              ? "warning"
              : "success";

        const key = flash.id || `${icon}:${message}`;
        if (lastShown.current === key) return;
        lastShown.current = key;

        Swal.fire(toastOptions(icon, message));
    }, [flash?.id, flash?.success, flash?.status, flash?.error]);

    return null;
}
