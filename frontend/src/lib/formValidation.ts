import axios from "axios";
import type { FieldValues, Path, UseFormSetError } from "react-hook-form";

type SetValidationErrors<T extends FieldValues> = {
  message: string;
  errors: Partial<Record<Path<T>, string[]>>;
};

function setValidationErrors<T extends FieldValues>(
  error: unknown,
  setError: UseFormSetError<T>,
  allowedFields: readonly Path<T>[],
): boolean {
  if (
    !axios.isAxiosError<SetValidationErrors<T>>(error) ||
    error.response?.status !== 422
  ) {
    return false;
  }

  let handled = false;

  for (const [field, messages] of Object.entries(
    error.response.data.errors,
  ) as [string, string[]][]) {
    const formField = field as Path<T>;
    const message = messages?.[0];

    if (!message || !allowedFields.includes(formField)) {
      continue;
    }

    setError(formField, {
      type: "server",
      message,
    });

    handled = true;
  }
  return handled;
}
export default setValidationErrors;
