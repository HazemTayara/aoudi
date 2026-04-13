export function formatNumber(num: number | null | undefined): string {
  if (num === null || num === undefined) return '0';
  const n = Number(num);
  if (isNaN(n)) return '0';
  const formatted = n.toFixed(2);
  if (formatted.endsWith('.00')) {
    return formatted.slice(0, -3).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }
  return formatted.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

export function formatDate(date: string | null | undefined): string {
  if (!date) return '---';
  const d = new Date(date);
  return d.toLocaleDateString('en-CA'); // YYYY-MM-DD
}

export function formatDateTime(date: string | null | undefined): string {
  if (!date) return '---';
  const d = new Date(date);
  return `${d.toLocaleDateString('en-CA')} ${d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })}`;
}
