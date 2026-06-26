import { useState, useRef, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { X, Send, Sparkles, User, Bot, Loader2, Trash2 } from "lucide-react";
import axios from "axios";
import Swal from "sweetalert2";

const WELCOME = {
    role: "assistant",
    content:
        "Halo! Saya Assistant AI LapakGimID. Saya bisa bantu cek stok & harga akun, jelaskan Rekber, garansi, dan cara transaksi. Ada yang bisa dibantu?",
};

const QUICK_REPLIES = [
    "Ada akun Mobile Legends?",
    "Gimana cara kerja Rekber?",
    "Apakah ada garansi akun?",
    "Saya mau titip jual akun",
];

export default function ChatWidget() {
    const [isOpen, setIsOpen] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const messagesEndRef = useRef(null);

    const [messages, setMessages] = useState(() => {
        const saved = localStorage.getItem("chat_history");
        return saved ? JSON.parse(saved) : [WELCOME];
    });

    const [input, setInput] = useState("");

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
    };

    useEffect(() => {
        localStorage.setItem("chat_history", JSON.stringify(messages));
        scrollToBottom();
    }, [messages, isOpen]);

    const sendMessage = async (text) => {
        const userMsg = (text ?? input).trim();
        if (!userMsg || isLoading) return;

        setInput("");
        const newMessages = [...messages, { role: "user", content: userMsg }];
        setMessages(newMessages);
        setIsLoading(true);

        try {
            const contextHistory = newMessages.slice(-6).map((m) => ({
                role: m.role,
                content: m.content,
            }));

            const response = await axios.post(route("ai.chat"), {
                message: userMsg,
                history: contextHistory,
            });

            setMessages([
                ...newMessages,
                { role: "assistant", content: response.data.reply },
            ]);
        } catch (error) {
            console.error("Chat Error:", error);
            setMessages([
                ...newMessages,
                {
                    role: "assistant",
                    content:
                        "Maaf, terjadi kesalahan atau jaringan tidak stabil. Silakan coba lagi.",
                },
            ]);
        } finally {
            setIsLoading(false);
        }
    };

    const handleSend = (e) => {
        e.preventDefault();
        sendMessage();
    };

    const clearChat = () => {
        const isDark = (localStorage.getItem("theme") || "dark") !== "light";
        const bg = isDark ? "#0e1629" : "#ffffff";
        const fg = isDark ? "#dbe4f0" : "#0b1221";

        Swal.fire({
            title: "Hapus Riwayat Chat?",
            text: "Percakapan akan dihapus permanen.",
            icon: "warning",
            iconColor: "#eab308",
            showCancelButton: true,
            confirmButtonColor: "#ef4444",
            cancelButtonColor: isDark ? "#1b2740" : "#e2e8f0",
            confirmButtonText: "Ya, Hapus",
            cancelButtonText: "Batal",
            background: bg,
            color: fg,
        }).then((result) => {
            if (result.isConfirmed) {
                setMessages([WELCOME]);
                localStorage.setItem("chat_history", JSON.stringify([WELCOME]));

                Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true,
                    background: bg,
                    color: fg,
                    iconColor: "#34d399",
                }).fire({
                    icon: "success",
                    title: "Chat berhasil dihapus",
                });
            }
        });
    };

    return (
        <>
            {/* FLOATING BUTTON */}
            <motion.button
                onClick={() => setIsOpen(!isOpen)}
                initial={{ scale: 0, opacity: 0 }}
                animate={{ scale: 1, opacity: 1 }}
                whileHover={{ scale: 1.08 }}
                whileTap={{ scale: 0.92 }}
                className="fixed bottom-6 right-6 z-50 btn btn-circle btn-lg bg-yellow-500 hover:bg-yellow-600 text-black border-none shadow-lg"
                aria-label="Buka chat"
            >
                {isOpen ? <X className="w-8 h-8" /> : <Bot className="w-8 h-8" />}
            </motion.button>

            {/* CHAT WINDOW */}
            <AnimatePresence>
                {isOpen && (
                    <motion.div
                        data-lenis-prevent
                        initial={{ opacity: 0, y: 50, scale: 0.9 }}
                        animate={{ opacity: 1, y: 0, scale: 1 }}
                        exit={{ opacity: 0, y: 20, scale: 0.95 }}
                        transition={{ duration: 0.2 }}
                        className="fixed bottom-24 right-4 md:right-8 w-[90vw] md:w-[400px] h-[500px] max-h-[75vh] bg-base-100 rounded-3xl shadow-2xl z-50 border border-base-300 flex flex-col overflow-hidden"
                    >
                        {/* HEADER */}
                        <div className="bg-yellow-500 p-4 flex items-center justify-between shrink-0">
                            <div className="flex items-center gap-3">
                                <div className="bg-black/10 p-2 rounded-full">
                                    <Bot className="w-6 h-6 text-black" />
                                </div>
                                <div>
                                    <h3 className="font-bold text-black text-lg leading-tight">
                                        LapakGim AI
                                    </h3>
                                    <div className="flex items-center gap-1.5">
                                        <span className="w-2 h-2 bg-emerald-600 rounded-full animate-pulse" />
                                        <span className="text-xs font-medium text-black/80">
                                            Online
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div className="flex gap-1">
                                <button
                                    onClick={clearChat}
                                    title="Hapus Chat"
                                    className="btn btn-sm btn-ghost btn-circle text-black/70 hover:bg-black/10"
                                >
                                    <Trash2 className="w-5 h-5" />
                                </button>
                                <button
                                    onClick={() => setIsOpen(false)}
                                    className="btn btn-sm btn-ghost btn-circle text-black/70 hover:bg-black/10"
                                >
                                    <X className="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        {/* MESSAGES AREA */}
                        <div
                            data-lenis-prevent
                            className="flex-1 overflow-y-auto overscroll-contain p-4 space-y-4 bg-base-200"
                        >
                            {messages.map((msg, index) => (
                                <motion.div
                                    key={index}
                                    initial={{ opacity: 0, y: 10 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    className={`flex w-full ${msg.role === "user" ? "justify-end" : "justify-start"}`}
                                >
                                    <div
                                        className={`flex gap-3 max-w-[85%] ${msg.role === "user" ? "flex-row-reverse" : "flex-row"}`}
                                    >
                                        <div
                                            className={`w-8 h-8 rounded-full flex shrink-0 items-center justify-center ${
                                                msg.role === "user"
                                                    ? "bg-base-content/10 text-base-content"
                                                    : "bg-yellow-500/15 text-yellow-600 dark:text-yellow-400"
                                            }`}
                                        >
                                            {msg.role === "user" ? (
                                                <User className="w-4 h-4" />
                                            ) : (
                                                <Sparkles className="w-4 h-4" />
                                            )}
                                        </div>

                                        <div
                                            className={`p-3.5 rounded-2xl text-sm leading-relaxed shadow-sm whitespace-pre-wrap ${
                                                msg.role === "user"
                                                    ? "bg-yellow-500 text-[#0b1221] rounded-tr-none"
                                                    : "bg-base-100 text-base-content border border-base-300 rounded-tl-none"
                                            }`}
                                        >
                                            {msg.content}
                                        </div>
                                    </div>
                                </motion.div>
                            ))}

                            {/* Quick replies */}
                            {messages.length <= 1 && !isLoading && (
                                <div className="flex flex-wrap gap-2 pt-1">
                                    {QUICK_REPLIES.map((q) => (
                                        <button
                                            key={q}
                                            onClick={() => sendMessage(q)}
                                            className="text-xs px-3 py-1.5 rounded-full border border-base-300 bg-base-100 text-base-content/80 hover:border-yellow-500 hover:text-yellow-600 transition-colors"
                                        >
                                            {q}
                                        </button>
                                    ))}
                                </div>
                            )}

                            {isLoading && (
                                <motion.div
                                    initial={{ opacity: 0 }}
                                    animate={{ opacity: 1 }}
                                    className="flex justify-start w-full"
                                >
                                    <div className="flex gap-3 max-w-[85%]">
                                        <div className="w-8 h-8 rounded-full bg-yellow-500/15 flex shrink-0 items-center justify-center text-yellow-600 dark:text-yellow-400">
                                            <Loader2 className="w-4 h-4 animate-spin" />
                                        </div>
                                        <div className="bg-base-100 p-4 rounded-2xl rounded-tl-none border border-base-300 shadow-sm text-sm text-base-content/50 italic flex items-center gap-1">
                                            Mengetik
                                            <span className="animate-pulse">...</span>
                                        </div>
                                    </div>
                                </motion.div>
                            )}
                            <div ref={messagesEndRef} />
                        </div>

                        {/* INPUT AREA */}
                        <div className="p-4 bg-base-100 border-t border-base-300 shrink-0">
                            <form
                                onSubmit={handleSend}
                                className="relative flex items-center gap-2"
                            >
                                <input
                                    type="text"
                                    value={input}
                                    onChange={(e) => setInput(e.target.value)}
                                    placeholder="Tanya sesuatu..."
                                    disabled={isLoading}
                                    className="input input-bordered w-full rounded-xl pl-4 pr-12 py-6 focus:border-yellow-500 focus:outline-none bg-base-200 text-base-content text-sm placeholder:text-base-content/40"
                                />
                                <button
                                    type="submit"
                                    disabled={!input.trim() || isLoading}
                                    className="absolute right-2 btn btn-sm btn-circle btn-ghost text-yellow-600 dark:text-yellow-400 disabled:bg-transparent hover:bg-yellow-500/20"
                                >
                                    <Send className="w-5 h-5" />
                                </button>
                            </form>
                            <div className="text-center mt-2">
                                <p className="text-[10px] text-base-content/40">
                                    Powered by AI · LapakGimID Assistant
                                </p>
                            </div>
                        </div>
                    </motion.div>
                )}
            </AnimatePresence>
        </>
    );
}
