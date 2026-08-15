import apiClient from "../lib/apiClient";
import type { Article, UserArticle } from "../types/article";

type ArticleListResponse = {
    current_page: number;
    data: Article[];
    last_page: number;
    per_page: number;
    total: number;
};

export type UpdateArticleStatusData = {
    is_favorite?: boolean;
    is_read?: boolean;
    is_read_later?: boolean;
};

export async function getArticles(): Promise<ArticleListResponse> {
    const response = await apiClient.get<ArticleListResponse>("/api/articles");

    return response.data;
}

export async function getArticle(id: number): Promise<Article> {
    const response = await apiClient.get<Article>(`/api/articles/${id}`);

    return response.data;
}

export async function updateArticleStatus(
    id: number,
    data: UpdateArticleStatusData,
): Promise<UserArticle> {
    const response = await apiClient.patch<UserArticle>(
        `/api/articles/${id}/status`,
        data,
    );

    return response.data;
}
