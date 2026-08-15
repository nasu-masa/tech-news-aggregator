import { useEffect, useState } from "react";
import { getArticles } from "../api/articles";
import type { Article } from "../types/article";
import { Link } from "react-router-dom";

function ArticleListPage() {
  const [articles, setArticles] = useState<Article[]>([]);

  useEffect(() => {
    const fetchArticles = async () => {
      const response = await getArticles();

      setArticles(response.data);
    };

    fetchArticles();
  }, []);

  return (
    <div>
      <h1>記事一覧</h1>

      {articles.map((article) => (
        <article key={article.id}>
          <h2>
            <Link to={`/articles/${article.id}`}>
              {article.title}
            </Link>
          </h2>
          <p>{article.source.name}</p>
          <p>{article.published_at ?? "公開日不明"}</p>
        </article>
      ))}
    </div>
  );
}

export default ArticleListPage;