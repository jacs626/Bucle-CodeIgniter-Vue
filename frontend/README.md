# Frontend - Vue 3 + Tailwind CSS

## Visión General

Frontend SPA construido con Vue 3, TypeScript, Pinia y Tailwind CSS.

## Estructura

```
src/
├── api/              # API client y endpoints
├── components/       # Componentes Vue
│   ├── blocks/       # Componentes de bloques
│   ├── categories/    # Componentes de categorías
│   ├── common/       # Componentes compartidos
│   └── layout/       # Layout principal
├── composables/      # Composables de Vue
├── layouts/          # Layouts de página
├── router/           # Configuración de rutas
├── stores/           # Stores de Pinia
├── types/            # Tipos TypeScript
├── utils/            # Utilidades
└── views/            # Vistas de página
```

## Scripts

```bash
npm run dev      # Desarrollo
npm run build    # Producción
npm run preview  # Preview producción
npm run lint     # Linting
```

## Rutas

- `/` - BucleView (workflow principal)
- `/entities` - EntitiesView
- `/blocks` - BlocksView
- `/calendar` - CalendarView
- `/notifications` - NotificationsView
- `/history` - HistoryView
- `/documents` - DocumentsView
- `/categories` - CategoriesView

## Stores (Pinia)

- `useEntitiesStore` - Entidades con metadata
- `useBlocksStore` - Bloques con schedule
- `useCategoriesStore` - Categorías
- `useHistoryStore` - Historial de ejecuciones
- `useDocumentsStore` - Documentos

## Variables de Entorno

```
VITE_API_URL=http://localhost:8080
```

## Dependencias Principales

- Vue 3.4+
- Pinia 2.1+
- Vue Router 4+
- Tailwind CSS 3+
- Axios