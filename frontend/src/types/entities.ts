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
  entity_id: number | null;
  name: string;
  type: string;
  data: Record<string, unknown> | null;
  schedule: Record<string, unknown> | null;
  parent_block_id: number | null;
  order_index: number;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface History {
  id: number;
  entity_id: number | null;
  block_id: number | null;
  date: string | null;
  status: string | null;
  note: Record<string, unknown> | null;
  created_at: string;
  updated_at: string;
}

export interface Document {
  id: number;
  entity_id: number | null;
  title: string;
  url: string | null;
  type: string | null;
  created_at: string;
  updated_at: string;
}