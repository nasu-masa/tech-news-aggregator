export const articleStatusFilters = [
  "unread",
  "favorite",
  "read_later",
] as const;

export type ArticleStatusFilter = (typeof articleStatusFilters)[number];

export function parseArticleStatusFilter(
  value: string | null,
): ArticleStatusFilter | undefined {
  return articleStatusFilters.find((status) => status === value);
}
