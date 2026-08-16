import { useEffect, useState } from "react";
import { Link, useParams } from "react-router-dom";
import {
  getArticle,
  updateArticleStatus,
  type UpdateArticleStatusData,
} from "../api/articles";
import { StatusButton } from "../components/articles/ArticleStatus";
import { formatArticleDate } from "../lib/formatArticleDate";
import type { Article } from "../types/article";

function ArticleDetailPage() {
  const { id } = useParams();
  const articleId = Number(id);
  const [article, setArticle] = useState<Article | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isUpdating, setIsUpdating] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");
  const [updateError, setUpdateError] = useState("");

  useEffect(() => {
    let ignore = false;

    const fetchArticle = async () => {
      if (!Number.isInteger(articleId) || articleId <= 0) {
        setErrorMessage("記事が見つかりませんでした。");
        setIsLoading(false);
        return;
      }

      try {
        const response = await getArticle(articleId);
        if (!ignore) setArticle(response);
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

    void fetchArticle();

    return () => {
      ignore = true;
    };
  }, [articleId]);

  const handleStatusUpdate = async (data: UpdateArticleStatusData) => {
    if (!article || isUpdating) return;

    setIsUpdating(true);
    setUpdateError("");

    try {
      const updatedStatus = await updateArticleStatus(articleId, data);
      setArticle({ ...article, user_articles: [updatedStatus] });
    } catch {
      setUpdateError(
        "状態を更新できませんでした。時間をおいて再度お試しください。",
      );
    } finally {
      setIsUpdating(false);
    }
  };

  if (isLoading) {
    return (
      <main className="flex-1 px-4 py-8 sm:px-6 sm:py-10 lg:px-0">
        <div
          className="mx-auto max-w-4xl rounded-lg border border-gray-200 bg-white px-6 py-12 text-center text-sm text-gray-600 shadow-sm lg:mx-0"
          role="status"
        >
          記事を読み込んでいます...
        </div>
      </main>
    );
  }

  if (errorMessage || !article) {
    return (
      <main className="flex-1 px-4 py-8 text-left sm:px-6 sm:py-10 lg:px-0">
        <div className="mx-auto max-w-4xl lg:mx-0">
          <div
            className="rounded-lg border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700"
            role="alert"
          >
            {errorMessage || "記事が見つかりませんでした。"}
          </div>
          <Link
            to="/"
            className="mt-5 inline-flex rounded-md text-sm font-medium text-green-800 underline underline-offset-4 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-700/40"
          >
            記事一覧へ戻る
          </Link>
        </div>
      </main>
    );
  }

  const status = article.user_articles[0];
  const isRead = status?.is_read ?? false;
  const isFavorite = status?.is_favorite ?? false;
  const isReadLater = status?.is_read_later ?? false;

  return (
    <main className="flex-1 px-4 py-8 text-left sm:px-6 sm:py-10 lg:px-0">
      <div className="mx-auto max-w-4xl lg:mx-0">
        <Link
          to="/"
          className="mb-5 inline-flex rounded-md text-sm font-medium text-gray-600 transition-colors hover:text-green-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-700/40"
        >
          ← 記事一覧へ戻る
        </Link>

        <article className="rounded-lg border border-gray-200 bg-white p-5 shadow-sm sm:p-8">
          <div className="mb-4 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500">
            <span className="font-medium text-green-800">
              {article.source.name}
            </span>
            <span aria-hidden="true">·</span>
            <time dateTime={article.published_at ?? undefined}>
              {formatArticleDate(article.published_at)}
            </time>
          </div>

          <h1 className="text-2xl font-semibold leading-tight tracking-tight text-gray-900 sm:text-3xl">
            {article.title}
          </h1>

          <section
            className="mt-5 border-t border-gray-100 pt-4"
            aria-labelledby="summary-heading"
          >
            <h2
              id="summary-heading"
              className="mb-3 text-base font-semibold text-gray-900"
            >
              概要
            </h2>
            <p className="whitespace-pre-wrap break-words text-base leading-7 text-gray-700">
              {article.summary ?? "この記事の概要はありません。"}
            </p>

            <div className="mt-8">
              <a
                href={article.url}
                target="_blank"
                rel="noreferrer"
                className="inline-flex items-center justify-center rounded-md bg-green-700 px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-green-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-700/40 focus-visible:ring-offset-2"
              >
                元記事を見る（新しいタブ）
              </a>
            </div>
          </section>
        </article>

        <section
          className="mt-5 rounded-lg border border-gray-200 bg-white px-5 py-4 shadow-sm sm:px-6 sm:py-5"
          aria-labelledby="status-heading"
        >
          <h2 id="status-heading" className="text-base font-semibold text-gray-900">
            この記事の状態
          </h2>
          <p className="mt-1 text-sm text-gray-600">
            読書状況や保存状態をここで変更できます。
          </p>

          <div className="mt-5 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
            <StatusButton
              active={isRead}
              activeLabel="既読に設定済み"
              inactiveLabel="既読にする"
              disabled={isUpdating}
              onClick={() => void handleStatusUpdate({ is_read: !isRead })}
            />
            <StatusButton
              active={isFavorite}
              activeLabel="お気に入り解除"
              inactiveLabel="お気に入り"
              disabled={isUpdating}
              onClick={() =>
                void handleStatusUpdate({ is_favorite: !isFavorite })
              }
            />
            <StatusButton
              active={isReadLater}
              activeLabel="あとで見る解除"
              inactiveLabel="あとで見る"
              disabled={isUpdating}
              onClick={() =>
                void handleStatusUpdate({ is_read_later: !isReadLater })
              }
            />
          </div>

        </section>
          {isUpdating && (
            <p className="mt-3 text-sm text-gray-600" role="status">
              状態を更新しています...
            </p>
          )}
          {updateError && (
            <p className="mt-3 text-sm text-red-600" role="alert">
              {updateError}
            </p>
          )}
      </div>
    </main>
  );
}

export default ArticleDetailPage;
