import { get, post, put, del } from '@/api/client';
import type { ApiResponse, Entity } from '@/types';

export const entitiesApi = {
  getAll: () => get<Entity[]>('entities'),

  getById: (id: number) => get<Entity>(`entities/${id}`),

  create: (data: Partial<Entity>) =>
    post<Entity>('entities', data),

  update: (id: number, data: Partial<Entity>) =>
    put<Entity>(`entities/${id}`, data),

  delete: (id: number) =>
    del<void>(`entities/${id}`),
};