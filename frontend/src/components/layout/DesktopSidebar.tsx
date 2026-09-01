import { useEffect, useState } from "react";
import { Link, useSearchParams } from "react-router-dom";
import { getSources } from "../../api/sources";
import { SOURCES_UPDATED_EVENT } from "../../lib/sourceEvents";
import {
  parseArticleStatusFilter,
  type ArticleStatusFilter,
} from "../../lib/articleFilters";
import { parsePositiveIntegerParam } from "../../lib/parsePositiveIntegerParam";
import type { Source } from "../../types/source";

const statusFilters: {
  label: string;
  value: ArticleStatusFilter;
}[] = [
  { label: "未読", value: "unread" },
  { label: "既読", value: "read" },
  { label: "お気に入り", value: "favorite" },
  { label: "あとで見る", value: "read_later" },
];

function DesktopSidebar() {
  const [searchParams] = useSearchParams();
  const selectedSourceId = parsePositiveIntegerParam(
    searchParams.get("source_id"),
  );
  const status = parseArticleStatusFilter(searchParams.get("status"));

  const [sources, setSources] = useState<Source[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState("");
  const [fetchKey, setFetchKey] = useState(0);

  const createFilterUrl = ({
    sourceId,
    status: nextStatus,
  }: {
    sourceId?: number | null;
    status?: ArticleStatusFilter | null;
  }) => {
    const nextSearchParams = new URLSearchParams(searchParams);

    if (sourceId === null) {
      nextSearchParams.delete("source_id");
    } else if (sourceId !== undefined) {
      nextSearchParams.set("source_id", String(sourceId));
    }

    if (nextStatus === null) {
      nextSearchParams.delete("status");
    } else if (nextStatus !== undefined) {
      nextSearchParams.set("status", nextStatus);
    }

    nextSearchParams.delete("page");

    const query = nextSearchParams.toString();

    return query ? `/?${query}` : "/";
  };

  useEffect(() => {
    const refetch = () => setFetchKey((k) => k + 1);
    window.addEventListener(SOURCES_UPDATED_EVENT, refetch);
    return () => window.removeEventListener(SOURCES_UPDATED_EVENT, refetch);
  }, []);

  useEffect(() => {
    let ignore = false;

    const fetchSources = async () => {
      try {
        const data = await getSources();
        if (!ignore) setSources(data);
      } catch {
        if (!ignore) setErrorMessage("配信元を取得できませんでした。");
      } finally {
        if (!ignore) setIsLoading(false);
      }
    };

    void fetchSources();

    return () => {
      ignore = true;
    };
  }, [fetchKey]);

  return (
    <aside className="hidden w-56 shrink-0 py-10 lg:block">
      <nav
        aria-label="記事ナビゲーション"
        className="max-h-[calc(100vh-8rem)] overflow-y-auto rounded-lg border border-stone-200 bg-white p-3"
      >
        <p className="mb-2 px-3 text-xs font-semibold tracking-wide text-stone-500">
          記事メニュー
        </p>
        <Link
          to={createFilterUrl({ sourceId: null, status: null })}
          aria-current={
            selectedSourceId === undefined && status === undefined
              ? "page"
              : undefined
          }
          className={`block rounded-md px-3 py-2.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40 ${
            selectedSourceId === undefined && status === undefined
              ? "bg-emerald-50 text-emerald-800"
              : "text-stone-700 hover:bg-stone-50 hover:text-emerald-800"
          }`}
        >
          すべての記事
        </Link>

        {statusFilters.map((filter) => {
          const isActive = status === filter.value;

          return (
            <Link
              key={filter.value}
              to={createFilterUrl({ status: filter.value })}
              aria-current={isActive ? "page" : undefined}
              className={`block rounded-md px-3 py-2.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40 ${
                isActive
                  ? "bg-emerald-50 text-emerald-800"
                  : "text-stone-700 hover:bg-stone-50 hover:text-emerald-800"
              }`}
            >
              {filter.label}
            </Link>
          );
        })}

        <section className="mt-4 border-t border-stone-100 pt-4">
          <h2 className="mb-2 px-3 text-xs font-semibold tracking-wide text-stone-500">
            配信元
          </h2>

          {isLoading && (
            <p className="px-3 py-2 text-sm text-stone-500" role="status">
              読み込み中...
            </p>
          )}

          {!isLoading && errorMessage && (
            <p
              className="px-3 py-2 text-sm leading-relaxed text-red-700"
              role="alert"
            >
              {errorMessage}
            </p>
          )}

          {!isLoading && !errorMessage && sources.filter((s) => s.is_subscribed).length === 0 && (
            <p className="px-3 py-2 text-sm text-stone-500">
              配信元がありません。
            </p>
          )}

          {!isLoading && !errorMessage && sources.filter((s) => s.is_subscribed).length > 0 && (
            <ul className="space-y-1">
              {sources.filter((s) => s.is_subscribed).map((source) => (
                <li key={source.id}>
                  <Link
                    to={createFilterUrl({ sourceId: source.id })}
                    aria-current={
                      selectedSourceId === source.id ? "page" : undefined
                    }
                    className={`block rounded-md px-3 py-2 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40 ${
                      selectedSourceId === source.id
                        ? "bg-emerald-50 text-emerald-800"
                        : "text-stone-700 hover:bg-stone-50 hover:text-emerald-800"
                    }`}
                  >
                    <span className="min-w-0 break-words">{source.name}</span>
                  </Link>
                </li>
              ))}
            </ul>
          )}
        </section>
      </nav>
    </aside>
  );
}

export default DesktopSidebar;
