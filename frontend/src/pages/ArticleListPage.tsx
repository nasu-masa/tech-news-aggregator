import { useEffect, useState } from "react";
import { Link, useSearchParams } from "react-router-dom";
import { getArticles } from "../api/articles";
import { StatusBadge } from "../components/articles/ArticleStatus";
import { parseArticleStatusFilter } from "../lib/articleFilters";
import { formatArticleDate } from "../lib/formatArticleDate";
import { parsePositiveIntegerParam } from "../lib/parsePositiveIntegerParam";
import MobileArticleFilters from "../components/articles/MobileArticleFilters";
import type { Article } from "../types/article";

function ArticleListPage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const sourceId = parsePositiveIntegerParam(searchParams.get("source_id"));
  const status = parseArticleStatusFilter(searchParams.get("status"));
  const keyword = searchParams.get("keyword") ?? undefined;
  const page = parsePositiveIntegerParam(searchParams.get("page")) ?? 1;

  const [inputValue, setInputValue] = useState(keyword ?? "");

  useEffect(() => {
    setInputValue(keyword ?? "");
  }, [keyword]);

  const [articles, setArticles] = useState<Article[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);

  const hasFilters = sourceId !== undefined || status !== undefined || keyword !== undefined;

  useEffect(() => {
    let ignore = false;

    const fetchArticles = async () => {
      setArticles([]);
      setIsLoading(true);
      setErrorMessage("");

      try {
        const response = await getArticles({
          source_id: sourceId,
          status,
          keyword,
          page,
        });
        if (!ignore) {
          if (
            response.total > 0 &&
            page > response.last_page
          ) {
            setSearchParams(
              (currentParams) => {
                const nextSearchParams = new URLSearchParams(currentParams);

                if (response.last_page <= 1) {
                  nextSearchParams.delete("page");
                } else {
                  nextSearchParams.set("page", String(response.last_page));
                }

                return nextSearchParams;
              },
              { replace: true },
            );

            return;
          }

          setArticles(response.data);
          setCurrentPage(response.current_page);
          setLastPage(response.last_page);
        }
      } catch {
        if (!ignore) {
          setErrorMessage(
            "記事を読み込めませんでした。時間をおいて再度お試しください。",
          );
        }
      } finally {
        if (!ignore) setIsLoading(false);
      }
    };

    void fetchArticles();

    return () => {
      ignore = true;
    };
  }, [sourceId, status, keyword, page, setSearchParams]);

  const handleSearch = (e: React.SyntheticEvent) => {
    e.preventDefault();
    const next = new URLSearchParams(searchParams);
    const trimmed = inputValue.trim();
    if (trimmed) {
      next.set("keyword", trimmed);
    } else {
      next.delete("keyword");
    }
    next.delete("page");
    setSearchParams(next);
  };

  const handlePageChange = (nextPage: number) => {
    const nextSearchParams = new URLSearchParams(searchParams);

    if (nextPage <= 1) {
      nextSearchParams.delete("page");
    } else {
      nextSearchParams.set("page", String(nextPage));
    }

    setSearchParams(nextSearchParams);
  };

  return (
    <main className="flex-1 px-4 py-8 text-left sm:px-6 sm:py-10 lg:px-0">
      <div className="w-full">
        <div className="mb-7 sm:mb-8">
          <p className="mb-1 text-sm font-medium text-green-700">MY NEWS</p>
          <h1 className="text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl">
            記事一覧
          </h1>
          <p className="mt-2 text-sm leading-relaxed text-gray-600">
            気になる技術ニュースを選び、詳細や保存状態を確認できます。
          </p>
        </div>

        <form onSubmit={handleSearch} className="mb-6">
          <div className="flex gap-2">
            <input
              type="search"
              value={inputValue}
              onChange={(e) => setInputValue(e.target.value)}
              placeholder="キーワードで検索..."
              aria-label="記事をキーワードで検索"
              className="min-w-0 flex-1 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 transition-colors focus:border-green-600 focus:outline-none focus:ring-2 focus:ring-green-700/30"
            />
            <button
              type="submit"
              className="shrink-0 rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-green-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-700/40"
            >
              検索
            </button>
          </div>
        </form>

        <MobileArticleFilters />

        {isLoading && (
          <div
            className="rounded-lg border border-gray-200 bg-white px-6 py-12 text-center text-sm text-gray-600 shadow-sm"
            role="status"
          >
            記事を読み込んでいます...
          </div>
        )}

        {!isLoading && errorMessage && (
          <div
            className="rounded-lg border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700"
            role="alert"
          >
            {errorMessage}
          </div>
        )}

        {!isLoading && !errorMessage && articles.length === 0 && (
          <div className="rounded-lg border border-gray-200 bg-white px-6 py-12 text-center shadow-sm">
            <h2 className="text-base font-semibold text-gray-900">
              {hasFilters
                ? "条件に一致する記事がありません。"
                : "まだ記事がありません。"}
            </h2>

            <p className="mt-2 text-sm text-gray-600">
              {hasFilters
                ? "絞り込み条件を変更して、もう一度お試しください。"
                : "記事が取得されると、ここに一覧で表示されます。"}
            </p>
          </div>
        )}

        {!isLoading && !errorMessage && articles.length > 0 && (
          <div className="grid gap-4">
            {articles.map((article) => {
              const status = article.user_articles[0];

              return (
                <article
                  key={article.id}
                  className="rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition-colors hover:border-green-200 sm:p-6"
                >
                  <div className="mb-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500">
                    <span className="font-medium text-gray-700">
                      {article.source.name}
                    </span>
                    <span aria-hidden="true">·</span>
                    <time dateTime={article.published_at ?? undefined}>
                      {formatArticleDate(article.published_at)}
                    </time>
                  </div>

                  <h2 className="text-lg font-semibold leading-snug tracking-tight text-gray-900 sm:text-xl">
                    <Link
                      to={`/articles/${article.id}`}
                      state={{ fromSearch: searchParams.toString() }}
                      className="rounded-sm transition-colors hover:text-green-800 hover:underline hover:underline-offset-4 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-700/40"
                    >
                      {article.title}
                    </Link>
                  </h2>

                  <div
                    className="mt-5 flex flex-wrap gap-2 border-t border-gray-100 pt-4"
                    aria-label="記事の状態"
                  >
                    <StatusBadge
                      active={status?.is_read ?? false}
                      activeLabel="既読"
                      inactiveLabel="未読"
                    />
                    <StatusBadge
                      active={status?.is_favorite ?? false}
                      activeLabel="お気に入り済み"
                      inactiveLabel="お気に入り未登録"
                    />
                    <StatusBadge
                      active={status?.is_read_later ?? false}
                      activeLabel="あとで見るに追加済み"
                      inactiveLabel="あとで見る未登録"
                    />
                  </div>
                </article>
              );
            })}
          </div>
        )}

        {!isLoading && !errorMessage && articles.length > 0 && (
          <nav
            className="mt-8 flex items-center justify-center gap-4"
            aria-label="記事一覧のページネーション"
          >
            <button
              type="button"
              onClick={() => handlePageChange(currentPage - 1)}
              disabled={currentPage <= 1}
              className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
            >
              前へ
            </button>

            <span className="text-sm text-gray-600">
              {currentPage} / {lastPage}
            </span>

            <button
              type="button"
              onClick={() => handlePageChange(currentPage + 1)}
              disabled={currentPage >= lastPage}
              className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
            >
              次へ
            </button>
          </nav>
        )}
      </div>
    </main>
  );
}

export default ArticleListPage;
