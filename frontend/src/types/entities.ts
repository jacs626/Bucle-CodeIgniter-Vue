export interface Category {
  id: number;
  name: string;
  icon: string | null;
  created_at: string;
  updated_at: string;
}

export interface CategoryFormData {
  name: string;
  icon: string;
}

export interface Entity {
  id: number;
  name: string;
  description: string | null;
  type: string;
  category_id: number | null;
  metadata: Record<string, unknown> | null;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface Block {
  id: number;
  entity_id: number | null;
  name: string;
  type: string;
  data: Record<string, unknown> | null;
  schedule: {
    type: 'fixed' | 'interval' | 'weekly';
    time?: string;
    intervalHours?: number;
    daysOfWeek?: string[];
    date?: string;
    startDate?: string;
  } | null;
  parent_block_id: number | null;
  order_index: number;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  status?: string;
  title?: string;
}

export interface History {
  id: number;
  entity_id: number | null;
  block_id: number | null;
  date: string | null;
  status: string | null;
  note: Record<string, unknown> | null;
  action: string | null;
  entity_type: string | null;
  created_at: string;
  updated_at: string;
}

export interface HistoryFormData {
  entity_id?: number | null;
  block_id?: number | null;
  date?: string | null;
  status?: string | null;
  note?: Record<string, unknown> | null;
  action?: string | null;
  entity_type?: string | null;
}

export interface Document {
  id: number;
  entity_id: number | null;
  title: string;
  url: string | null;
  type: string | null;
  file_type: string | null;
  file_size: number | null;
  is_published: boolean;
  created_at: string;
  updated_at: string;
}

export interface DocumentFormData {
  entity_id?: number | null;
  title: string;
  url?: string | null;
  type?: string | null;
  file_type?: string | null;
  file_size?: number | null;
  is_published?: boolean;
}

export interface BlockFormData {
  entity_id?: number | null;
  name: string;
  type: string;
  data?: Record<string, unknown>;
  schedule?: {
    type: 'fixed' | 'interval' | 'weekly';
    time?: string;
    intervalHours?: number;
    daysOfWeek?: string[];
    date?: string;
    startDate?: string;
  };
  parent_block_id?: number | null;
  order_index?: number;
  is_active?: boolean;
}

export interface EntityFormData {
  name: string;
  description?: string;
  type: string;
  category_id: number | null;
  metadata?: Record<string, unknown>;
  is_active?: boolean;
}