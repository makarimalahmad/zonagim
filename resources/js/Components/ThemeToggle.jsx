import { useEffect, useRef, useState } from "react";
import { Sun, Moon } from "lucide-react";

export default function ThemeToggle({ className = "" }) {
    const [theme, setTheme] = useState("dark");
    const btnRef = useRef(null);

    useEffect(() => {
        const savedTheme = localStorage.getItem("user-theme") || "dark";
        setTheme(savedTheme);
        document.documentElement.setAttribute("data-theme", savedTheme);
    }, []);

    const applyTheme = (newTheme) => {
        setTheme(newTheme);
        localStorage.setItem("user-theme", newTheme);
        document.documentElement.setAttribute("data-theme", newTheme);
    };

    const toggleTheme = () => {
        const newTheme = theme === "dark" ? "light" : "dark";

        const prefersReduced = window.matchMedia(
            "(prefers-reduced-motion: reduce)"
        ).matches;

        if (!document.startViewTransition || prefersReduced) {
            applyTheme(newTheme);
            return;
        }

        const rect = btnRef.current?.getBoundingClientRect();
        const x = rect ? rect.left + rect.width / 2 : window.innerWidth / 2;
        const y = rect ? rect.top + rect.height / 2 : window.innerHeight / 2;
        const radius = Math.hypot(
            Math.max(x, window.innerWidth - x),
            Math.max(y, window.innerHeight - y)
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
            ref={btnRef}
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
