import { useState } from "react";
import { Sun, Moon } from "lucide-react";

export default function ThemeToggle({ className = "" }) {
    const [theme, setTheme] = useState(
        () => document.documentElement.getAttribute("data-theme") || "dark",
    );

    const applyTheme = (newTheme) => {
        setTheme(newTheme);
        localStorage.setItem("user-theme", newTheme);
        document.documentElement.setAttribute("data-theme", newTheme);
    };

    const toggleTheme = (event) => {
        const newTheme = theme === "dark" ? "light" : "dark";

        const prefersReduced = window.matchMedia(
            "(prefers-reduced-motion: reduce)"
        ).matches;

        if (!document.startViewTransition || prefersReduced) {
            applyTheme(newTheme);
            return;
        }

        const rect = event.currentTarget.getBoundingClientRect();
        const transitionScale = window.devicePixelRatio || 1;
        const viewportWidth = window.innerWidth * transitionScale;
        const viewportHeight = window.innerHeight * transitionScale;
        const x = (rect.left + rect.width / 2) * transitionScale;
        const y = (rect.top + rect.height / 2) * transitionScale;
        const radius = Math.hypot(
            Math.max(x, viewportWidth - x),
            Math.max(y, viewportHeight - y)
        );

        const root = document.documentElement;
        root.style.setProperty("--theme-x", `${x}px`);
        root.style.setProperty("--theme-y", `${y}px`);
        root.style.setProperty("--theme-r", `${radius}px`);
        root.setAttribute("data-theme-vt", "");

        const transition = document.startViewTransition(() => {
            applyTheme(newTheme);
        });

        transition.ready
            .then(() => {
                root.animate(
                    {
                        clipPath: [
                            `circle(0px at ${x}px ${y}px)`,
                            `circle(${radius}px at ${x}px ${y}px)`,
                        ],
                    },
                    {
                        duration: 500,
                        easing: "cubic-bezier(0.4, 0, 0.2, 1)",
                        pseudoElement: "::view-transition-new(root)",
                    }
                );
            })
            .finally(() => {
                transition.finished.finally(() => {
                    root.removeAttribute("data-theme-vt");
                });
            });
    };

    return (
        <button
            type="button"
            onClick={toggleTheme}
            aria-label="Ganti tema"
            title={theme === "dark" ? "Mode terang" : "Mode gelap"}
            className={
                "w-10 h-10 flex items-center justify-center rounded-lg border border-base-300 bg-base-100 text-base-content hover:text-yellow-500 hover:border-yellow-500/40 transition-colors " +
                className
            }
        >
            {theme === "dark" ? <Sun size={18} /> : <Moon size={18} />}
        </button>
    );
}
