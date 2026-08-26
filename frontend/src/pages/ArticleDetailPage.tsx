import { useEffect, useState } from "react";
import { Link, useLocation, useParams } from "react-router-dom";
import {
  getArticle,
  updateArticleMemo,
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

  const [memo, setMemo] = useState("");
  const [isSavingMemo, setIsSavingMemo] = useState(false);
  const [memoMessage, setMemoMessage] = useState("");
  const [memoError, setMemoError] = useState("");

  const location = useLocation();

  const fromSearch =
    typeof location.state?.fromSearch === "string"
      ? location.state.fromSearch
      : "";

  const articleListUrl = fromSearch ? `/?${fromSearch}` : "/";

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

        if (!ignore) {
          setArticle(response);
          setMemo(response.user_articles[0]?.memo ?? "");
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
      setArticle((currentArticle) => {
        if (!currentArticle) return currentArticle;

        const currentStatus = currentArticle.user_articles[0];

        return {
          ...currentArticle,
          user_articles: [
            {
              ...updatedStatus,
              memo:
                currentStatus !== undefined
                  ? currentStatus.memo
                  : updatedStatus.memo,
            },
          ],
        };
      });
    } catch {
      setUpdateError(
        "状態を更新できませんでした。時間をおいて再度お試しください。",
      );
    } finally {
      setIsUpdating(false);
    }
  };

  const handleMemoSave = async () => {
    if (!article || isSavingMemo) return;

    setIsSavingMemo(true);
    setMemoMessage("");
    setMemoError("");

    try {
      const updatedUserArticle = await updateArticleMemo(articleId, {
        memo: memo.trim() === "" ? null : memo,
      });

      setArticle((currentArticle) => {
        if (!currentArticle) return currentArticle;

        const currentStatus = currentArticle.user_articles[0];

        return {
          ...currentArticle,
          user_articles: [
            {
              ...updatedUserArticle,
              is_favorite:
                currentStatus?.is_favorite ??
                updatedUserArticle.is_favorite,
              is_read:
                currentStatus?.is_read ?? updatedUserArticle.is_read,
              is_read_later:
                currentStatus?.is_read_later ??
                updatedUserArticle.is_read_later,
              read_at:
                currentStatus !== undefined
                  ? currentStatus.read_at
                  : updatedUserArticle.read_at,
              memo: updatedUserArticle.memo,
            },
          ],
        };
      });

      setMemo(updatedUserArticle.memo ?? "");
      setMemoMessage("メモを更新しました。");
    } catch {
      setMemoError(
        "メモを更新できませんでした。時間をおいて再度お試しください。",
      );
    } finally {
      setIsSavingMemo(false);
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
            to={articleListUrl}
            className="mt-5 inline-flex rounded-md text-sm font-medium text-green-800 underline underline-offset-4 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-700/40"
          >
            ← 記事一覧へ戻る
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
          to={articleListUrl}
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
            {article.translated_title ?? article.title}
          </h1>
          {article.translated_title && (
            <p className="mt-2 text-sm text-gray-500">
              {article.title}
            </p>
          )}

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
          <h2
            id="status-heading"
            className="text-base font-semibold text-gray-900"
          >
            記事を整理
          </h2>

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
        </section>

        <section
          className="mt-5 rounded-lg border border-gray-200 bg-white px-5 py-4 shadow-sm sm:px-6 sm:py-5"
          aria-labelledby="memo-heading"
        >
          <h2
            id="memo-heading"
            className="text-base font-semibold text-gray-900"
          >
            自分用メモ
          </h2>

          <p className="mt-1 text-sm text-gray-600">
            この記事について覚えておきたいことを残せます。
          </p>

          <textarea
            aria-labelledby="memo-heading"
            value={memo}
            onChange={(event) => setMemo(event.target.value)}
            maxLength={5000}
            rows={5}
            disabled={isSavingMemo}
            className="mt-4 w-full resize-y rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-green-700 focus:ring-2 focus:ring-green-700/20"
            placeholder="メモを入力してください"
          />

          <div className="mt-3 flex flex-wrap items-center gap-3">
            <button
              type="button"
              onClick={() => void handleMemoSave()}
              disabled={isSavingMemo}
              className="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-700/40 disabled:cursor-not-allowed disabled:opacity-60"
            >
              {isSavingMemo ? "保存中..." : "メモを保存"}
            </button>

            {memoMessage && (
              <p className="text-sm text-gray-600" aria-live="polite">
                {memoMessage}
              </p>
            )}

            {memoError && (
              <p className="text-sm text-red-600" role="alert">
                {memoError}
              </p>
            )}
          </div>
        </section>
      </div>
    </main>
  );
}

export default ArticleDetailPage;
