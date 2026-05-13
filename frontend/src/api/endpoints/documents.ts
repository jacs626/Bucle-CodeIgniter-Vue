import apiClient from '@/api/client';
import { get, post, put, del } from '@/api/client';
import type { Document } from '@/types';

export const documentsApi = {
  getAll: () => get<Document[]>('documents'),

  getById: (id: number) => get<Document>(`documents/${id}`),

  create: (data: Partial<Document>) =>
    post<Document>('documents', data),

  update: (id: number, data: Partial<Document>) =>
    put<Document>(`documents/${id}`, data),

  delete: (id: number) =>
    del<void>(`documents/${id}`),

  upload: async (formData: FormData) => {
    return apiClient.post<Document>('documents', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
  },
};