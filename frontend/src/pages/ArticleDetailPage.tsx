import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { getArticle } from "../api/articles";
import type { Article } from "../types/article";

function ArticleDetailPage() {
    const { id } = useParams();
    const [article, setArticle] = useState<Article | null>(null);

    useEffect(() => {
        if (!id) return;

        const fetchArticle = async () => {
        const response = await getArticle(Number(id));

        setArticle(response);
        };

        fetchArticle();
    }, [id]);

    if (!article) {
        return <p>読み込み中...</p>;
    }

    const userArticle = article.user_articles[0];

    return (
        <article>
            <h1>{article.title}</h1>

            <p>{article.source.name}</p>

            <p>{article.published_at ?? "公開日不明"}</p>

            <p>{article.summary ?? "概要はありません。"}</p>

            <a href={article.url} target="_blank" rel="noreferrer">
                元記事をみる
            </a>

            <div>
            <p>{userArticle?.is_read ? "既読" : "未読"}</p>

            <p>
                {userArticle?.is_read_later
                ? "あとで見るに追加済み"
                : "あとで見るに未追加"}
            </p>
            </div>
        </article>
    );
}

export default ArticleDetailPage;
