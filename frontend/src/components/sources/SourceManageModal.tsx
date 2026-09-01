import axios from "axios";
import { useEffect, useRef, useState } from "react";
import { getSources, registerSource, subscribeSource, unsubscribeSource } from "../../api/sources";
import { SOURCES_UPDATED_EVENT } from "../../lib/sourceEvents";
import type { Source } from "../../types/source";

type Props = {
    isOpen: boolean;
    onClose: () => void;
};

function SourceManageModal({ isOpen, onClose }: Props) {
    const [sources, setSources] = useState<Source[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [loadError, setLoadError] = useState("");
    const [actionError, setActionError] = useState("");
    const [pendingIds, setPendingIds] = useState<Set<number>>(new Set());
    const [feedUrl, setFeedUrl] = useState("");
    const [isRegistering, setIsRegistering] = useState(false);
    const [registerError, setRegisterError] = useState("");
    const backdropRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (!isOpen) return;

        let ignore = false;
        setIsLoading(true);
        setLoadError("");
        setActionError("");
        setFeedUrl("");
        setRegisterError("");

        getSources()
            .then((data) => {
                if (!ignore) {
                    setSources(data);
                    setIsLoading(false);
                }
            })
            .catch(() => {
                if (!ignore) {
                    setLoadError("配信元を取得できませんでした。");
                    setIsLoading(false);
                }
            });

        return () => {
            ignore = true;
        };
    }, [isOpen]);

    useEffect(() => {
        if (!isOpen) return;
        const handleKeyDown = (e: KeyboardEvent) => {
            if (e.key === "Escape") onClose();
        };
        document.addEventListener("keydown", handleKeyDown);
        return () => document.removeEventListener("keydown", handleKeyDown);
    }, [isOpen, onClose]);

    const handleRegister = async () => {
        if (!feedUrl.trim() || isRegistering) return;
        setRegisterError("");
        setIsRegistering(true);
        try {
            await registerSource(feedUrl.trim());
            setFeedUrl("");
            const data = await getSources();
            setSources(data);
            window.dispatchEvent(new CustomEvent(SOURCES_UPDATED_EVENT));
        } catch (err) {
            if (axios.isAxiosError(err) && err.response?.status === 422) {
                const msgs = (err.response.data as { errors?: { feed_url?: string[] } })?.errors
                    ?.feed_url;
                setRegisterError(msgs?.[0] ?? "登録に失敗しました。");
            } else {
                setRegisterError("登録に失敗しました。もう一度お試しください。");
            }
        } finally {
            setIsRegistering(false);
        }
    };

    const handleToggle = async (source: Source) => {
        if (pendingIds.has(source.id)) return;
        setActionError("");
        setPendingIds((prev) => new Set(prev).add(source.id));

        try {
            if (source.is_subscribed) {
                await unsubscribeSource(source.id);
            } else {
                await subscribeSource(source.id);
            }
            setSources((prev) =>
                prev.map((s) =>
                    s.id === source.id ? { ...s, is_subscribed: !s.is_subscribed } : s,
                ),
            );
            window.dispatchEvent(new CustomEvent(SOURCES_UPDATED_EVENT));
        } catch {
            setActionError("操作に失敗しました。もう一度お試しください。");
        } finally {
            setPendingIds((prev) => {
                const next = new Set(prev);
                next.delete(source.id);
                return next;
            });
        }
    };

    const handleBackdropClick = (e: React.MouseEvent<HTMLDivElement>) => {
        if (e.target === backdropRef.current) onClose();
    };

    if (!isOpen) return null;

    return (
        <div
            ref={backdropRef}
            onClick={handleBackdropClick}
            className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            role="dialog"
            aria-modal="true"
            aria-label="配信元を管理"
        >
            <div className="flex max-h-[80vh] w-full max-w-md flex-col rounded-xl bg-white shadow-xl">
                <div className="flex items-center justify-between border-b border-stone-200 px-5 py-4">
                    <h2 className="text-base font-semibold text-stone-900">配信元を管理</h2>
                    <button
                        type="button"
                        onClick={onClose}
                        aria-label="閉じる"
                        className="rounded-md p-1.5 text-stone-400 transition-colors hover:bg-stone-100 hover:text-stone-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            className="h-5 w-5"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                fillRule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clipRule="evenodd"
                            />
                        </svg>
                    </button>
                </div>

                <div className="border-b border-stone-200 px-5 py-4">
                    <div className="flex gap-2">
                        <input
                            type="url"
                            value={feedUrl}
                            onChange={(e) => setFeedUrl(e.target.value)}
                            onKeyDown={(e) => {
                                if (e.key === "Enter") void handleRegister();
                            }}
                            placeholder="https://example.com/feed.xml"
                            disabled={isRegistering}
                            className="min-w-0 flex-1 rounded-lg border border-stone-300 px-3 py-2 text-sm placeholder-stone-400 focus:border-emerald-700 focus:outline-none focus:ring-1 focus:ring-emerald-700/40 disabled:bg-stone-50 disabled:text-stone-400"
                        />
                        <button
                            type="button"
                            onClick={() => void handleRegister()}
                            disabled={isRegistering || !feedUrl.trim()}
                            className="shrink-0 rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-emerald-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {isRegistering ? "登録中..." : "追加"}
                        </button>
                    </div>
                    {registerError && (
                        <p className="mt-1.5 text-xs text-red-700" role="alert">
                            {registerError}
                        </p>
                    )}
                </div>

                <div className="min-h-0 flex-1 overflow-y-auto p-5">
                    {isLoading && (
                        <p className="text-sm text-stone-500" role="status">
                            読み込み中...
                        </p>
                    )}

                    {!isLoading && loadError && (
                        <p className="text-sm text-red-700" role="alert">
                            {loadError}
                        </p>
                    )}

                    {!isLoading && !loadError && (
                        <>
                            {actionError && (
                                <p
                                    className="mb-4 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700"
                                    role="alert"
                                >
                                    {actionError}
                                </p>
                            )}
                            <ul className="space-y-2">
                                {sources.map((source) => {
                                    const isPending = pendingIds.has(source.id);
                                    return (
                                        <li
                                            key={source.id}
                                            className="flex items-center justify-between gap-3 rounded-lg border border-stone-100 px-4 py-3"
                                        >
                                            <span className="min-w-0 break-words text-sm font-medium text-stone-800">
                                                {source.name}
                                            </span>
                                            <button
                                                type="button"
                                                onClick={() => void handleToggle(source)}
                                                disabled={isPending}
                                                className={`shrink-0 rounded-full px-3 py-1.5 text-xs font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40 disabled:cursor-not-allowed disabled:opacity-50 ${
                                                    source.is_subscribed
                                                        ? "border border-stone-300 bg-white text-stone-700 hover:border-red-200 hover:bg-red-50 hover:text-red-700"
                                                        : "bg-emerald-700 text-white hover:bg-emerald-800"
                                                }`}
                                            >
                                                {isPending ? "..." : source.is_subscribed ? "解除" : "追加"}
                                            </button>
                                        </li>
                                    );
                                })}
                            </ul>
                        </>
                    )}
                </div>
            </div>
        </div>
    );
}

export default SourceManageModal;
