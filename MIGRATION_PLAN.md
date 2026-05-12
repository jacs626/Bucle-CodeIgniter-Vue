# Plan de Migración: MVP Bucle

## Descripción

Migración del proyecto **MVP Bucle** desde **NestJS + MongoDB + Next.js** hacia **CodeIgniter 4 + MySQL + Vue 3**.

## Estructura Actual

### Proyecto Original (NestJS + Next.js)
```
MVP Bucle/
├── backend/           # NestJS
│   └── src/modules/
│       ├── categories
│       ├── entities
│       ├── blocks
│       ├── history
│       ├── documents
│       ├── uploads
│       └── seed
├── frontend/          # Next.js
│   └── src/
│       ├── api/
│       ├── components/
│       ├── stores/
│       └── views/
```

### Proyecto Destino (CodeIgniter + Vue)
```
MVP Bucle - CodeIgniter&Vue/
├── backend/           # CodeIgniter 4
│   └── app/Modules/
│       ├── Categories/
│       ├── Entities/
│       ├── Blocks/
│       ├── History/
│       └── Documents/
└── frontend/          # Vue 3 + Vite
    └── src/
        ├── api/
        ├── stores/
        ├── composables/
        ├── views/
        └── components/
```

## Plan de Migración

### Fase 1: Configuración Base (Completado)

- [x] Estructura modular backend (CodeIgniter 4)
- [x] Estructura frontend (Vue 3 + Vite + Pinia)
- [x] CORS y configuración API
- [x] Trait ApiResponse estándar
- [x] Layout base + Sidebar

### Fase 2: Migrar Módulos Backend

#### 2.1 Categories (Completado ✓)
- [x] Migration MySQL
- [x] Entity
- [x] Model
- [x] Service
- [x] Controller
- [x] Transformer
- [x] Rutas API

#### 2.2 Entities (Completado ✓)
- [x] Migration: entities
- [x] Entity
- [x] Model
- [x] Service
- [x] Controller
- [x] Transformer
- [x] Rutas API

#### 2.3 Blocks (Completado ✓)
- [x] Migration: blocks (2026-05-12-000003_CreateBlocksTable.php)
- [x] Entity
- [x] Model
- [x] Service
- [x] Controller
- [x] Transformer
- [x] Rutas API
- Tipos de blocks: task, reminder, payment, step, note, workflow

#### 2.4 History (Completado ✓)
- [x] Migration: history (2026-05-12-000004_CreateHistoryTable.php)
- [x] Entity
- [x] Model
- [x] Service
- [x] Controller
- [x] Transformer
- [x] Rutas API

#### 2.5 Documents (Completado ✓)
- [x] Migration: documents (2026-05-12-000005_CreateDocumentsTable.php)
- [x] Entity
- [x] Model
- [x] Service
- [x] Controller
- [x] Transformer
- [x] Rutas API

### Fase 3: Migrar Módulos Frontend

#### 3.1 Categories (Completado ✓)
- [x] Types
- [x] API endpoints
- [x] Composable useCategories
- [x] Store Pinia
- [x] Components (CategoryList, CategoryForm)
- [x] CategoriesView.vue
- [x] Toast notifications

#### 3.2 Entities (Parcial)
- [x] Types
- [x] Store Pinia
- [ ] API endpoints
- [ ] Composable useEntities
- [ ] Components
- [ ] EntitiesView.vue

#### 3.3 Blocks (Completado ✓)
- [x] Types
- [x] API endpoints
- [x] Composable useBlocks
- [x] Store Pinia
- [x] BlocksView.vue

#### 3.4 History (Completado ✓)
- [x] Types
- [x] API endpoints
- [x] Composable useHistory
- [x] Store Pinia
- [x] HistoryView.vue

#### 3.5 Documents (Completado ✓)
- [x] Types
- [x] API endpoints
- [x] Composable useDocuments
- [x] Store Pinia
- [x] DocumentsView.vue

### Fase 4: Características Avanzadas (Parcial)

- [x] Seed de datos iniciales (BucleSeeder)
- [ ] Upload de archivos
- [ ] Autenticación (JWT)
- [ ] Websockets (notificaciones tiempo real)
- [ ] Jobs/Queue (tareas programadas)
- [ ] Testing

## Base de Datos MySQL

```sql
-- Categories
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  icon VARCHAR(100),
  created_at DATETIME,
  updated_at DATETIME,
  deleted_at DATETIME
);

-- Entities
CREATE TABLE entities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  type VARCHAR(100),
  metadata JSON,
  created_at DATETIME,
  updated_at DATETIME,
  deleted_at DATETIME,
  FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Blocks
CREATE TABLE blocks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  entity_id INT,
  type VARCHAR(50) NOT NULL,
  data JSON,
  schedule JSON,
  created_at DATETIME,
  updated_at DATETIME,
  deleted_at DATETIME,
  FOREIGN KEY (entity_id) REFERENCES entities(id)
);

-- History
CREATE TABLE history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  entity_id INT,
  block_id INT,
  date DATETIME,
  status VARCHAR(50),
  note JSON,
  created_at DATETIME,
  updated_at DATETIME,
  deleted_at DATETIME,
  FOREIGN KEY (entity_id) REFERENCES entities(id),
  FOREIGN KEY (block_id) REFERENCES blocks(id)
);

-- Documents
CREATE TABLE documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  entity_id INT,
  title VARCHAR(255) NOT NULL,
  url VARCHAR(500),
  type VARCHAR(50),
  created_at DATETIME,
  updated_at DATETIME,
  deleted_at DATETIME,
  FOREIGN KEY (entity_id) REFERENCES entities(id)
);
```

## Arquitectura de参考 (Proyecto Precios)

El proyecto **Precios** sirve como referencia para la arquitectura:

- **Backend**: CodeIgniter 4 con módulos en `app/Modules/{Name}/`
  - Controllers, Services, Models, Entities, Transformers, Validation
- **Frontend**: Vue 3 + Vite + Pinia + Vue Router
  - Separación clara: api/, stores/, composables/, views/, components/

## Comandos de Desarrollo

```bash
# Backend
cd backend
php spark migrate
php spark serve

# Frontend
cd frontend
npm run dev
```

## Notas

1. El proyecto usa MySQL en lugar de MongoDB
2. JSON fields para metadata flexible
3. Soft deletes en todas las tablas
4. Timestamps automáticos
5. API REST con respuestas consistentes
6. Frontend con Composition API y TypeScript