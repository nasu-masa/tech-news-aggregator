import { useEffect, useState } from "react";
import { useSearchParams } from "react-router-dom";
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
  value?: ArticleStatusFilter;
}[] = [
  { label: "すべて" },
  { label: "未読", value: "unread" },
  { label: "既読", value: "read" },
  { label: "お気に入り", value: "favorite" },
  { label: "あとで見る", value: "read_later" },
];

function MobileArticleFilters() {
  const [searchParams, setSearchParams] = useSearchParams();

  const currentStatus = parseArticleStatusFilter(searchParams.get("status"));
  const currentSourceId = parsePositiveIntegerParam(searchParams.get("source_id"));

  const [sources, setSources] = useState<Source[]>([]);
  const [fetchKey, setFetchKey] = useState(0);

  useEffect(() => {
    const refetch = () => setFetchKey((k) => k + 1);
    window.addEventListener(SOURCES_UPDATED_EVENT, refetch);
    return () => window.removeEventListener(SOURCES_UPDATED_EVENT, refetch);
  }, []);

  useEffect(() => {
    let ignore = false;

    getSources()
      .then((data) => { if (!ignore) setSources(data); })
      .catch(() => {});

    return () => { ignore = true; };
  }, [fetchKey]);

  const handleStatusChange = (status?: ArticleStatusFilter) => {
    const next = new URLSearchParams(searchParams);
    if (status) {
      next.set("status", status);
    } else {
      next.delete("status");
    }
    next.delete("page");
    setSearchParams(next);
  };

  const handleSourceChange = (sourceId?: number) => {
    const next = new URLSearchParams(searchParams);
    if (sourceId !== undefined) {
      next.set("source_id", String(sourceId));
    } else {
      next.delete("source_id");
    }
    next.delete("page");
    setSearchParams(next);
  };

  const pillClass = (isActive: boolean) =>
    `shrink-0 rounded-full border px-4 py-2 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40 ${
      isActive
        ? "border-emerald-700 bg-emerald-700 text-white"
        : "border-stone-200 bg-white text-stone-700 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-800"
    }`;

  return (
    <div className="mb-6 space-y-3 lg:hidden">
      <nav className="overflow-x-auto" aria-label="記事の状態で絞り込む">
        <div className="flex w-max gap-2 pb-1">
          {statusFilters.map((filter) => {
            const isActive = currentStatus === filter.value;
            return (
              <button
                key={filter.value ?? "all"}
                type="button"
                onClick={() => handleStatusChange(filter.value)}
                aria-pressed={isActive}
                className={pillClass(isActive)}
              >
                {filter.label}
              </button>
            );
          })}
        </div>
      </nav>

      {sources.filter((s) => s.is_subscribed).length > 0 && (
        <nav className="overflow-x-auto" aria-label="配信元で絞り込む">
          <div className="flex w-max gap-2 pb-1">
            <button
              type="button"
              onClick={() => handleSourceChange(undefined)}
              aria-pressed={currentSourceId === undefined}
              className={pillClass(currentSourceId === undefined)}
            >
              すべての配信元
            </button>
            {sources.filter((s) => s.is_subscribed).map((source) => {
              const isActive = currentSourceId === source.id;
              return (
                <button
                  key={source.id}
                  type="button"
                  onClick={() => handleSourceChange(source.id)}
                  aria-pressed={isActive}
                  className={pillClass(isActive)}
                >
                  {source.name}
                </button>
              );
            })}
          </div>
        </nav>
      )}
    </div>
  );
}

export default MobileArticleFilters;
