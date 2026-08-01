import { useEffect, useRef, useState } from "react";
import { toastOptions } from "@/utils/notificationTheme";
import { AnimatePresence, motion } from "framer-motion";
import {
    Bot,
    Loader2,
    Send,
    Sparkles,
    Trash2,
    User,
    X,
} from "lucide-react";
import axios from "axios";

axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

const WELCOME = {
    role: "assistant",
    content:
        "Halo! Saya Assistant AI Zonagim. Saya bisa bantu cek stok & harga akun, jelaskan Rekber, garansi, dan cara transaksi. Ada yang bisa dibantu?",
};

const QUICK_REPLIES = [
    "Ada akun Mobile Legends?",
    "Gimana cara kerja Rekber?",
    "Apakah ada garansi akun?",
    "Saya mau titip jual akun",
];

export default function ChatPanel({ isOpen, onClose }) {
    const [isLoading, setIsLoading] = useState(false);
    const [messages, setMessages] = useState([WELCOME]);
    const [input, setInput] = useState("");
    const messagesEndRef = useRef(null);
    const inputRef = useRef(null);

    useEffect(() => {
        if (isOpen) {
            messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
        }
    }, [messages, isOpen]);

    useEffect(() => {
        if (!isOpen) {
            return;
        }

        const focusInputWithEnter = (event) => {
            if (
                event.key !== "Enter" ||
                event.defaultPrevented ||
                event.altKey ||
                event.ctrlKey ||
                event.metaKey ||
                event.shiftKey ||
                event.target instanceof HTMLInputElement ||
                event.target instanceof HTMLTextAreaElement ||
                event.target instanceof HTMLButtonElement ||
                event.target instanceof HTMLAnchorElement ||
                event.target?.isContentEditable
            ) {
                return;
            }

            event.preventDefault();
            inputRef.current?.focus();
        };

        window.addEventListener("keydown", focusInputWithEnter);

        return () => window.removeEventListener("keydown", focusInputWithEnter);
    }, [isOpen]);

    const sendMessage = async (text) => {
        const userMessage = (text ?? input).trim();

        if (!userMessage || isLoading) {
            return;
        }

        setInput("");

        const newMessages = [
            ...messages,
            { role: "user", content: userMessage },
        ];

        setMessages(newMessages);
        setIsLoading(true);

        try {
            const history = messages
                .filter((message) => message.role === "user")
                .slice(-6)
                .map((message) => ({
                    role: "user",
                    content: message.content,
                }));

            const response = await axios.post(route("ai.chat"), {
                message: userMessage,
                history,
            });

            setMessages([
                ...newMessages,
                { role: "assistant", content: response.data.reply },
            ]);
        } catch (error) {
            const status = error.response?.status;
            const serverReply = error.response?.data?.reply;
            const errorMessage =
                typeof serverReply === "string" && serverReply.trim()
                    ? serverReply
                    : status === 429
                      ? "Terlalu banyak permintaan. Silakan tunggu sebentar lalu coba lagi."
                      : status === 419
                        ? "Sesi Anda telah berakhir. Muat ulang halaman lalu coba lagi."
                        : status === 422
                          ? "Pesan tidak dapat diproses. Periksa isinya lalu coba lagi."
                          : !error.response
                            ? "Koneksi ke asisten AI terputus. Periksa jaringan lalu coba lagi."
                            : "Asisten AI sedang mengalami gangguan. Silakan coba lagi sebentar lagi.";

            setMessages([
                ...newMessages,
                {
                    role: "assistant",
                    content: errorMessage,
                },
            ]);
        } finally {
            setIsLoading(false);
        }
    };

    const handleSend = (event) => {
        event.preventDefault();
        sendMessage();
    };

    const clearChat = async () => {
        const { default: Swal } = await import("sweetalert2");
        const isDark =
            (localStorage.getItem("user-theme") || "dark") !== "light";
        const background = isDark ? "#0e1629" : "#ffffff";
        const color = isDark ? "#dbe4f0" : "#0b1221";

        const result = await Swal.fire({
            title: "Hapus Riwayat Chat?",
            text: "Percakapan akan dihapus permanen.",
            icon: "warning",
            iconColor: "#eab308",
            showCancelButton: true,
            confirmButtonColor: "#dc2626",
            customClass: {
                confirmButton: "app-delete-button",
            },
            cancelButtonColor: isDark ? "#1b2740" : "#e2e8f0",
            confirmButtonText: "Ya, Hapus",
            cancelButtonText: "Batal",
            background,
            color,
        });

        if (!result.isConfirmed) {
            return;
        }

        setMessages([WELCOME]);

        Swal.fire({
            ...toastOptions("success", "Chat berhasil dihapus"),
            timer: 2500,
        });
    };

    return (
        <AnimatePresence>
            {isOpen && (
                <motion.div
                    data-lenis-prevent
                    initial={{ opacity: 0, y: 50, scale: 0.9 }}
                    animate={{ opacity: 1, y: 0, scale: 1 }}
                    exit={{ opacity: 0, y: 20, scale: 0.95 }}
                    transition={{ duration: 0.2 }}
                    className="fixed bottom-24 right-4 z-50 flex h-[500px] max-h-[75vh] w-[90vw] flex-col overflow-hidden rounded-3xl border border-base-300 bg-base-100 md:right-8 md:w-[400px]"
                    role="dialog"
                    aria-label="Zonagim AI"
                >
                    <div className="flex shrink-0 items-center justify-between bg-yellow-500 p-4">
                        <div className="flex items-center gap-3">
                            <div className="rounded-full bg-black/10 p-2">
                                <Bot className="h-6 w-6 text-black" />
                            </div>
                            <div>
                                <h3 className="text-lg font-bold leading-tight text-black">
                                    Zonagim AI
                                </h3>
                                <div className="flex items-center gap-1.5">
                                    <span className="h-2 w-2 animate-pulse rounded-full bg-emerald-600" />
                                    <span className="text-xs font-medium text-black/80">
                                        Online
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div className="flex gap-1">
                            <button
                                type="button"
                                onClick={clearChat}
                                title="Hapus Chat"
                                aria-label="Hapus riwayat chat"
                                className="app-delete-button btn btn-circle btn-sm"
                            >
                                <Trash2 className="h-5 w-5" />
                            </button>
                            <button
                                type="button"
                                onClick={onClose}
                                aria-label="Tutup chat"
                                className="btn btn-circle btn-ghost btn-sm text-black/70 hover:bg-black/10"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <div
                        data-lenis-prevent
                        className="flex-1 space-y-4 overflow-y-auto overscroll-contain bg-base-200 p-4"
                    >
                        {messages.map((message, index) => (
                            <motion.div
                                key={`${message.role}-${index}`}
                                initial={{ opacity: 0, y: 10 }}
                                animate={{ opacity: 1, y: 0 }}
                                className={`flex w-full ${
                                    message.role === "user"
                                        ? "justify-end"
                                        : "justify-start"
                                }`}
                            >
                                <div
                                    className={`flex max-w-[85%] gap-3 ${
                                        message.role === "user"
                                            ? "flex-row-reverse"
                                            : "flex-row"
                                    }`}
                                >
                                    <div
                                        className={`flex h-8 w-8 shrink-0 items-center justify-center rounded-full ${
                                            message.role === "user"
                                                ? "bg-base-content/10 text-base-content"
                                                : "bg-yellow-500/15 text-yellow-600 dark:text-yellow-400"
                                        }`}
                                    >
                                        {message.role === "user" ? (
                                            <User className="h-4 w-4" />
                                        ) : (
                                            <Sparkles className="h-4 w-4" />
                                        )}
                                    </div>

                                    <div
                                        className={`whitespace-pre-wrap rounded-2xl p-3.5 text-sm leading-relaxed ${
                                            message.role === "user"
                                                ? "rounded-tr-none bg-yellow-500 text-[#0b1221]"
                                                : "rounded-tl-none border border-base-300 bg-base-100 text-base-content"
                                        }`}
                                    >
                                        {message.content}
                                    </div>
                                </div>
                            </motion.div>
                        ))}

                        {messages.length <= 1 && !isLoading && (
                            <div className="flex flex-wrap gap-2 pt-1">
                                {QUICK_REPLIES.map((reply) => (
                                    <button
                                        type="button"
                                        key={reply}
                                        onClick={() => sendMessage(reply)}
                                        className="rounded-full border border-base-300 bg-base-100 px-3 py-1.5 text-xs text-base-content/80 transition-colors hover:border-yellow-500 hover:text-yellow-600"
                                    >
                                        {reply}
                                    </button>
                                ))}
                            </div>
                        )}

                        {isLoading && (
                            <motion.div
                                initial={{ opacity: 0 }}
                                animate={{ opacity: 1 }}
                                className="flex w-full justify-start"
                            >
                                <div className="flex max-w-[85%] gap-3">
                                    <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-yellow-500/15 text-yellow-600 dark:text-yellow-400">
                                        <Loader2 className="h-4 w-4 animate-spin" />
                                    </div>
                                    <div className="flex items-center gap-1 rounded-2xl rounded-tl-none border border-base-300 bg-base-100 p-4 text-sm italic text-base-content/50">
                                        Mengetik
                                        <span className="animate-pulse">...</span>
                                    </div>
                                </div>
                            </motion.div>
                        )}

                        <div ref={messagesEndRef} />
                    </div>

                    <div className="shrink-0 border-t border-base-300 bg-base-100 p-4">
                        <form
                            onSubmit={handleSend}
                            className="relative flex items-center gap-2"
                        >
                            <input
                                ref={inputRef}
                                type="text"
                                value={input}
                                onChange={(event) =>
                                    setInput(event.target.value)
                                }
                                placeholder="Tanya sesuatu..."
                                disabled={isLoading}
                                className="input input-bordered w-full rounded-xl bg-base-200 py-6 pl-4 pr-12 text-sm text-base-content placeholder:text-base-content/40 focus:border-yellow-500 focus:outline-none"
                            />
                            <button
                                type="submit"
                                disabled={!input.trim() || isLoading}
                                aria-label="Kirim pesan"
                                className="btn btn-circle btn-ghost btn-sm absolute right-2 text-yellow-600 hover:bg-yellow-500/20 disabled:bg-transparent dark:text-yellow-400"
                            >
                                <Send className="h-5 w-5" />
                            </button>
                        </form>
                        <p className="mt-2 text-center text-[10px] text-base-content/40">
                            Powered by AI · Zonagim Assistant
                        </p>
                    </div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}