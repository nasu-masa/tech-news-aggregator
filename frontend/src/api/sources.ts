import apiClient from "../lib/apiClient";
import type { Source } from "../types/source";

export async function getSources(): Promise<Source[]> {
    const response = await apiClient.get<Source[]>("/api/sources");

    return response.data;
}

export async function registerSource(feedUrl: string): Promise<Source> {
    const response = await apiClient.post<Source>("/api/sources", { feed_url: feedUrl });

    return response.data;
}

export async function subscribeSource(sourceId: number): Promise<void> {
    await apiClient.post(`/api/sources/${sourceId}/subscribe`);
}

export async function unsubscribeSource(sourceId: number): Promise<void> {
    await apiClient.delete(`/api/sources/${sourceId}/subscribe`);
}
