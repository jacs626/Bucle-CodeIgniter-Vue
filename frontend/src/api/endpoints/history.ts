import { get, post, del } from '@/api/client';
import type { History } from '@/types';

export const historyApi = {
  getAll: () => get<History[]>('history'),

  getById: (id: number) => get<History>(`history/${id}`),

  create: (data: Partial<History>) =>
    post<History>('history', data),

  delete: (id: number) =>
    del<void>(`history/${id}`),
};