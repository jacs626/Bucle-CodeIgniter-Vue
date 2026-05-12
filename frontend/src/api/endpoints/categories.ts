import { get, post, put, del } from '@/api/client';
import type { ApiResponse, Category } from '@/types';

export const categoriesApi = {
  getAll: () => get<Category[]>('categories'),

  getById: (id: number) => get<Category>(`categories/${id}`),

  create: (data: Partial<Category>) =>
    post<Category>('categories', data),

  update: (id: number, data: Partial<Category>) =>
    put<Category>(`categories/${id}`, data),

  delete: (id: number) =>
    del<void>(`categories/${id}`),
};