import apiClient from "./apiClient";

export type RegisterInput = {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
};

export const registerUser = async (input: RegisterInput): Promise<void> => {
  await apiClient.get("/sanctum/csrf-cookie");
  await apiClient.post("/register", input);
};

export type LoginInput = {
  email: string;
  password: string;
};

export const loginUser = async (input: LoginInput): Promise<void> => {
  await apiClient.get("/sanctum/csrf-cookie");
  await apiClient.post("/login", input);
};

export type User = {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
};

export const getCurrentUser = async (): Promise<User> => {
  const response = await apiClient.get<User>("/api/user");

  return response.data;
};

export const logout = async (): Promise<void> => {
  await apiClient.post("/logout");
};

export const resendVerificationEmail = async (): Promise<void> => {
  await apiClient.post("/email/verification-notification");
};

export type ForgotPasswordInput = {
  email: string;
};

export const forgotPassword = async (
  input: ForgotPasswordInput
): Promise<void> => {
  await apiClient.post("/forgot-password", input);
};

export type ResetPasswordInput = {
  token: string;
  email: string;
  password: string;
  password_confirmation: string;
};

export const resetPassword = async (
  input: ResetPasswordInput,
): Promise<void> => {
  await apiClient.post("/reset-password", input);
};
