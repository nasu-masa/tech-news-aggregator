import { useSearchParams } from "react-router-dom";
import {
  parseArticleStatusFilter,
  type ArticleStatusFilter,
} from "../../lib/articleFilters";

const filters: {
  label: string;
  value?: ArticleStatusFilter;
}[] = [
  { label: "すべて" },
  { label: "未読", value: "unread" },
  { label: "お気に入り", value: "favorite" },
  { label: "あとで見る", value: "read_later" },
];

function MobileArticleFilters() {
  const [searchParams, setSearchParams] = useSearchParams();

  const currentStatus = parseArticleStatusFilter(searchParams.get("status"));

  const handleFilterChange = (status?: ArticleStatusFilter) => {
    const nextSearchParams = new URLSearchParams(searchParams);

    if (status) {
      nextSearchParams.set("status", status);
    } else {
      nextSearchParams.delete("status");
    }

    nextSearchParams.delete("page");

    setSearchParams(nextSearchParams);
  };

  return (
    <nav
      className="mb-6 overflow-x-auto lg:hidden"
      aria-label="記事の状態で絞り込む"
    >
      <div className="flex w-max gap-2 pb-1">
        {filters.map((filter) => {
          const isActive = currentStatus === filter.value;

          return (
            <button
              key={filter.value ?? "all"}
              type="button"
              onClick={() => handleFilterChange(filter.value)}
              aria-pressed={isActive}
              className={`shrink-0 rounded-full border px-4 py-2 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-700/40 ${
                isActive
                  ? "border-green-700 bg-green-700 text-white"
                  : "border-gray-200 bg-white text-gray-700 hover:border-green-200 hover:bg-green-50 hover:text-green-800"
              }`}
            >
              {filter.label}
            </button>
          );
        })}
      </div>
    </nav>
  );
}

export default MobileArticleFilters;
