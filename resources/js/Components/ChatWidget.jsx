import { lazy, Suspense, useState } from "react";
import { Bot, Loader2, X } from "lucide-react";
import { usePage } from "@inertiajs/react";

const ChatPanel = lazy(() => import("./ChatPanel"));

export default function ChatWidget() {
    const { auth } = usePage().props;
    const [isOpen, setIsOpen] = useState(false);
    const [hasOpened, setHasOpened] = useState(false);

    const toggleChat = () => {
        setHasOpened(true);
        setIsOpen((open) => !open);
    };

    return (
        <>
            <button
                type="button"
                onClick={toggleChat}
                className="btn btn-circle btn-lg fixed bottom-6 right-6 z-50 border-none bg-yellow-500 text-black transition-transform duration-150 hover:scale-105 hover:bg-yellow-600 active:scale-95"
                aria-label={isOpen ? "Tutup chat" : "Buka chat"}
                aria-expanded={isOpen}
            >
                {isOpen ? (
                    <X className="h-8 w-8" />
                ) : (
                    <Bot className="h-8 w-8" />
                )}
            </button>

            {hasOpened && (
                <Suspense
                    fallback={
                        <div
                            className="fixed bottom-24 right-4 z-50 flex h-20 w-[90vw] items-center justify-center rounded-3xl border border-base-300 bg-base-100 md:right-8 md:w-[400px]"
                            role="status"
                            aria-label="Memuat chat"
                        >
                            <Loader2 className="h-6 w-6 animate-spin text-yellow-500" />
                        </div>
                    }
                >
                    <ChatPanel
                        isOpen={isOpen}
                        onClose={() => setIsOpen(false)}
                        userId={auth?.user?.id ?? null}
                    />
                </Suspense>
            )}
        </>
    );
}