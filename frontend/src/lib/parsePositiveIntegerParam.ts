export function parsePositiveIntegerParam(
  value: string | null,
): number | undefined {
  if (value === null || !/^[1-9]\d*$/.test(value)) return undefined;

  const parsedValue = Number(value);

  return Number.isSafeInteger(parsedValue) ? parsedValue : undefined;
}
