export const toStr = (val: unknown): string => String(val ?? '')
export const idsMatch = (a: unknown, b: unknown): boolean => toStr(a) === toStr(b)
export const toNum = (val: unknown): number => Number(val ?? 0)