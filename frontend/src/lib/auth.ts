import apiClient from "./apiClient";

export type RegisterInput = {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
};

export const register = async (input: RegisterInput): Promise<void> => {
    await apiClient.get("/sanctum/csrf-cookie");
    await apiClient.post("/register", input);
}