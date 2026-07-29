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
};

export type LoginInput = {
  email: string;
  password: string;
};

export const login = async (input: LoginInput): Promise<void> => {
  await apiClient.get("/sanctum/csrf-cookie");
  await apiClient.post("/login", input);
};

export type User = {
  id: number;
  name: string;
  email: string;
};

export const getCurrentUser = async (): Promise<User> => {
  const response = await apiClient.get<User>("/api/user");

  return response.data;
};

export const logout = async (): Promise<void> => {
  await apiClient.post("/logout");
};
