# Backend - CodeIgniter 4

## Visión General

Backend API REST construido con CodeIgniter 4 PHP.

## Estructura de Módulos

```
app/Modules/
├── Blocks/
│   ├── Controllers/    # CRUD endpoints
│   ├── Services/       # Lógica de negocio + Strategy
│   ├── Models/         # Acceso a datos
│   ├── Entities/       # Entidades de dominio
│   └── Transformers/   # Formateo de respuestas
├── Categories/         # CRUD categorías
├── Documents/          # CRUD documentos
├── Entities/           # CRUD entidades con metadata
└── History/            # Historial de ejecuciones

app/Blocks/             # Strategy Pattern
├── Context/
├── Interfaces/
└── Strategies/         # TaskStrategy, ReminderStrategy, PaymentStrategy, WorkflowStrategy
```

## API Endpoints

### Categories
- `GET /api/categories` - Listar todas
- `GET /api/categories/:id` - Ver una
- `POST /api/categories` - Crear
- `PUT /api/categories/:id` - Actualizar
- `DELETE /api/categories/:id` - Eliminar

### Entities
- `GET /api/entities` - Listar todas
- `GET /api/entities/:id` - Ver una
- `POST /api/entities` - Crear (soporta metadata JSON)
- `PUT /api/entities/:id` - Actualizar
- `DELETE /api/entities/:id` - Eliminar

### Blocks
- `GET /api/blocks` - Listar todos
- `GET /api/blocks/:id` - Ver uno
- `GET /api/blocks/entity/:entityId` - Bloques por entidad
- `POST /api/blocks` - Crear (soporta schedule JSON)
- `PUT /api/blocks/:id` - Actualizar
- `DELETE /api/blocks/:id` - Eliminar

### History
- `GET /api/history` - Listar historial
- `GET /api/history/:id` - Ver entrada
- `POST /api/history` - Crear entrada
- `DELETE /api/history/:id` - Eliminar

### Documents
- `GET /api/documents` - Listar documentos
- `GET /api/documents/:id` - Ver documento
- `POST /api/documents` - Crear (soporta upload)
- `PUT /api/documents/:id` - Actualizar
- `DELETE /api/documents/:id` - Eliminar

## Scripts

```bash
php spark serve       # Servidor desarrollo (puerto 8080)
php spark migrate     # Ejecutar migraciones
php spark db:seed     # Datos de prueba
php spark db:create   # Crear base de datos
```

## Formato de Respuesta

```json
{
  "status": "success",
  "message": "Mensaje descriptivo",
  "data": { ... }
}
```

## Modelo de Datos

### entities
- id, name, description, type, category_id, metadata (JSON), is_active, timestamps

### blocks
- id, entity_id, name, type, data (JSON), schedule (JSON), parent_block_id, order_index, is_active, timestamps

### history
- id, entity_id, block_id, date, status, note (JSON), block_name, block_type, entity_name, entity_type, executed_at, timestamps

### documents
- id, entity_id, block_id, title, url, type, file_type, file_size, is_published, timestamps

### categories
- id, name, icon, timestamps

## Estrategia de Bloques (Strategy Pattern)

Cada tipo de bloque implementa su propia lógica:
- `TaskStrategy` - Tareas simples
- `ReminderStrategy` - Recordatorios
- `PaymentStrategy` - Pagos con montos
- `WorkflowStrategy` - Flujos de trabajo con steps
- `NoteStrategy` - Notas

## Variables de Entorno

Configurar en `.env`:
```
database.default.hostname = localhost
database.default.database = bucle_db
database.default.username = root
database.default.password =
```