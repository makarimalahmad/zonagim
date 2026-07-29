export function getNotificationTheme() {
    return {
        background: "#facc15",
        color: "#422006",
        iconColor: "#713f12",
        timerProgressBarColor: "rgba(113,63,18,0.5)",
    };
}

export function toastOptions(icon, title) {
    return {
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        icon,
        titleText: title,
        ...getNotificationTheme(icon),
    };
}
