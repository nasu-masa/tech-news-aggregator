type StatusBadgeProps = {
  active: boolean;
  activeLabel: string;
  inactiveLabel: string;
};

export function StatusBadge({
  active,
  activeLabel,
  inactiveLabel,
}: StatusBadgeProps) {
  return (
    <span
      className={`inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium ${
        active
          ? "border-green-200 bg-green-50 text-green-800"
          : "border-gray-200 bg-gray-50 text-gray-600"
      }`}
    >
      {active ? activeLabel : inactiveLabel}
    </span>
  );
}

type StatusButtonProps = StatusBadgeProps & {
  onClick: () => void;
  disabled?: boolean;
};

export function StatusButton({
  active,
  activeLabel,
  inactiveLabel,
  onClick,
  disabled = false,
}: StatusButtonProps) {
  return (
    <button
      type="button"
      aria-pressed={active}
      onClick={onClick}
      disabled={disabled}
      className={`rounded-md border px-3.5 py-2.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-700/40 focus-visible:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60 ${
        active
          ? "border-green-700 bg-green-50 text-green-800 hover:bg-green-100"
          : "border-gray-300 bg-white text-gray-700 hover:border-green-300 hover:bg-green-50"
      }`}
    >
      {active ? activeLabel : inactiveLabel}
    </button>
  );
}
