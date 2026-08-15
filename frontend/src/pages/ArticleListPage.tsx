import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { getArticles } from "../api/articles";
import { StatusBadge } from "../components/articles/ArticleStatus";
import { formatArticleDate } from "../lib/formatArticleDate";
import type { Article } from "../types/article";

function ArticleListPage() {
  const [articles, setArticles] = useState<Article[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState("");

  useEffect(() => {
    let ignore = false;

    const fetchArticles = async () => {
      try {
        const response = await getArticles();
        if (!ignore) setArticles(response.data);
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
  }, []);

  return (
    <main className="flex-1 px-4 py-8 text-left sm:px-6 sm:py-10">
      <div className="mx-auto max-w-6xl">
        <div className="mb-7 sm:mb-8">
          <p className="mb-1 text-sm font-medium text-green-700">MY NEWS</p>
          <h1 className="text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl">
            記事一覧
          </h1>
          <p className="mt-2 text-sm leading-relaxed text-gray-600">
            気になる技術ニュースを選び、詳細や保存状態を確認できます。
          </p>
        </div>

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
              表示できる記事はまだありません
            </h2>
            <p className="mt-2 text-sm text-gray-600">
              記事が取得されると、ここに一覧で表示されます。
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
      </div>
    </main>
  );
}

export default ArticleListPage;
