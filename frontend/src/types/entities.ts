export interface Category {
  id: number;
  name: string;
  icon: string | null;
  created_at: string;
  updated_at: string;
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
  name: string;
  type: string;
  content: Record<string, unknown> | null;
  entity_id: number | null;
  order: number;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface History {
  id: number;
  entity_type: string;
  entity_id: number;
  action: string;
  changes: Record<string, unknown> | null;
  user_id: number | null;
  created_at: string;
  updated_at: string;
}

export interface Document {
  id: number;
  title: string;
  content: string | null;
  file_path: string | null;
  file_type: string | null;
  file_size: number;
  entity_id: number | null;
  is_published: boolean;
  created_at: string;
  updated_at: string;
}