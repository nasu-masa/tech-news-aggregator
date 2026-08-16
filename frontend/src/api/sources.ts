import apiClient from "../lib/apiClient";
import type { Source } from "../types/source";

export async function getSources(): Promise<Source[]> {
    const response = await apiClient.get<Source[]>("/api/sources");

    return response.data;
}
