import { get, post, put, del } from '@/api/client';
import type { ApiResponse, Block } from '@/types';

export const blocksApi = {
  getAll: () => get<Block[]>('blocks'),

  getById: (id: number) => get<Block>(`blocks/${id}`),

  create: (data: Partial<Block>) =>
    post<Block>('blocks', data),

  update: (id: number, data: Partial<Block>) =>
    put<Block>(`blocks/${id}`, data),

  delete: (id: number) =>
    del<void>(`blocks/${id}`),
};