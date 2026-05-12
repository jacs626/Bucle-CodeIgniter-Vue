export interface ApiResponse<T = unknown> {
  status: 'success' | 'error';
  message: string;
  data?: T;
  errors?: Record<string, string>;
}

export interface ApiCollectionResponse<T = unknown[]> extends ApiResponse<T> {
  data: T;
}

export interface PaginatedResponse<T = unknown> {
  data: T[];
  pagination?: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
  };
}