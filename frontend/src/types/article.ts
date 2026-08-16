import type { Source } from "./source";

export type UserArticle = {
    id: number;
    user_id: number;
    article_id: number;
    is_favorite: boolean;
    is_read: boolean;
    is_read_later: boolean;
    memo: string | null;
    read_at: string | null;
};

export type Article = {
    id: number;
    source_id: number;
    title: string;
    summary: string | null;
    url: string;
    published_at: string | null;
    source: Source;
    user_articles: UserArticle[];
};
