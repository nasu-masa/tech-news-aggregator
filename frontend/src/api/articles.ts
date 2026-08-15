import apiClient from "../lib/apiClient";
import type { Article } from "../types/article";

type ArticleListResponse = {
    current_page: number;
    data: Article[];
    last_page: number;
    per_page: number;
    total: number;
};

export async function getArticles(): Promise<ArticleListResponse> {
    const response = await apiClient.get<ArticleListResponse>("/api/articles");

    return response.data;
}

export async function getArticle(id: number): Promise<Article> {
    const response = await apiClient.get<Article>(`/api/articles/${id}`);

    return response.data;
}
