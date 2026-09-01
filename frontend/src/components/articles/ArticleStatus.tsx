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
          ? "border-emerald-200 bg-emerald-50 text-emerald-800"
          : "border-stone-200 bg-stone-50 text-stone-600"
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
      className={`rounded-md border px-3.5 py-2.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40 focus-visible:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60 ${
        active
          ? "border-emerald-700 bg-emerald-50 text-emerald-800 hover:bg-emerald-100"
          : "border-stone-300 bg-white text-stone-700 hover:border-emerald-300 hover:bg-emerald-50"
      }`}
    >
      {active ? activeLabel : inactiveLabel}
    </button>
  );
}
