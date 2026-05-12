<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Modules\Categories\Models\CategoryModel;
use App\Modules\Entities\Models\EntityModel;
use App\Modules\Blocks\Models\BlockModel;
use App\Modules\History\Models\HistoryModel;
use App\Modules\Documents\Models\DocumentModel;

class BucleSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCategories();
        $this->seedEntities();
        $this->seedBlocks();
        $this->seedHistory();
        $this->seedDocuments();
    }

    protected function seedCategories(): void
    {
        $categories = [
            ['name' => 'Viajes', 'icon' => '✈️'],
            ['name' => 'Medicamentos', 'icon' => '💊'],
            ['name' => 'Finanzas', 'icon' => '💰'],
            ['name' => 'Hogar', 'icon' => '🏠'],
            ['name' => 'Negocios', 'icon' => '🏪'],
        ];

        $categoryModel = new CategoryModel();
        
        foreach ($categories as $category) {
            $categoryModel->insert($category);
        }
    }

    protected function seedEntities(): void
    {
        $entities = [
            [
                'name' => 'Viaje a Brasil',
                'description' => 'Vacaciones familiares a Río de Janeiro',
                'type' => 'trip',
                'category_id' => 1,
                'metadata' => json_encode(['destination' => 'Río de Janeiro', 'date' => '2026-06-15']),
            ],
            [
                'name' => 'Lanzamiento Producto',
                'description' => 'Workflow de lanzamiento',
                'type' => 'project',
                'category_id' => 5,
                'metadata' => json_encode([]),
            ],
            [
                'name' => 'Rutina de Ibuprofeno',
                'description' => 'Horario de medicamentos',
                'type' => 'medication',
                'category_id' => 2,
                'metadata' => json_encode(['dosage' => '400mg', 'frequency' => 'Cada 8 horas']),
            ],
            [
                'name' => 'Presupuesto del hogar',
                'description' => 'Gastos mensuales',
                'type' => 'finance',
                'category_id' => 3,
                'metadata' => json_encode(['currency' => 'CLP', 'limit' => 1500000]),
            ],
        ];

        $entityModel = new EntityModel();

        foreach ($entities as $entity) {
            $entityModel->insert($entity);
        }
    }

    protected function seedBlocks(): void
    {
        $blocks = [
            [
                'entity_id' => 1,
                'name' => 'Reservar vuelos',
                'type' => 'task',
                'data' => json_encode(['description' => 'Buscar mejores precios']),
                'schedule' => null,
                'parent_block_id' => null,
                'order_index' => 0,
                'is_active' => true,
            ],
            [
                'entity_id' => 1,
                'name' => 'Revisar pasaporte',
                'type' => 'reminder',
                'data' => json_encode([]),
                'schedule' => json_encode(['type' => 'fixed', 'date' => '2026-05-01']),
                'parent_block_id' => null,
                'order_index' => 1,
                'is_active' => true,
            ],
            [
                'entity_id' => 1,
                'name' => 'Empaquetar equipaje',
                'type' => 'workflow',
                'data' => json_encode([]),
                'schedule' => null,
                'parent_block_id' => null,
                'order_index' => 2,
                'is_active' => true,
            ],
            [
                'entity_id' => 2,
                'name' => 'Hacer maleta',
                'type' => 'step',
                'data' => json_encode([]),
                'schedule' => null,
                'parent_block_id' => null,
                'order_index' => 0,
                'is_active' => true,
            ],
            [
                'entity_id' => 2,
                'name' => 'Revisar documentos',
                'type' => 'step',
                'data' => json_encode([]),
                'schedule' => null,
                'parent_block_id' => null,
                'order_index' => 1,
                'is_active' => true,
            ],
            [
                'entity_id' => 3,
                'name' => 'Dosis matutina',
                'type' => 'reminder',
                'data' => json_encode([]),
                'schedule' => json_encode(['type' => 'fixed', 'time' => '08:00']),
                'parent_block_id' => null,
                'order_index' => 0,
                'is_active' => true,
            ],
            [
                'entity_id' => 4,
                'name' => 'Cuenta de luz',
                'type' => 'payment',
                'data' => json_encode(['amount' => 45000]),
                'schedule' => null,
                'parent_block_id' => null,
                'order_index' => 0,
                'is_active' => true,
            ],
        ];

        $blockModel = new BlockModel();

        foreach ($blocks as $block) {
            $blockModel->insert($block);
        }
    }

    protected function seedHistory(): void
    {
        $histories = [
            [
                'entity_id' => 1,
                'block_id' => 1,
                'date' => '2026-05-10 10:00:00',
                'status' => 'completed',
                'note' => json_encode(['message' => 'Vuelos reservados']),
            ],
            [
                'entity_id' => 1,
                'block_id' => 2,
                'date' => '2026-05-01 09:00:00',
                'status' => 'pending',
                'note' => json_encode(['message' => 'Recordatorio enviado']),
            ],
            [
                'entity_id' => 3,
                'block_id' => 6,
                'date' => '2026-05-12 08:00:00',
                'status' => 'completed',
                'note' => json_encode(['message' => 'Dosis tomada']),
            ],
        ];

        $historyModel = new HistoryModel();

        foreach ($histories as $history) {
            $historyModel->insert($history);
        }
    }

    protected function seedDocuments(): void
    {
        $documents = [
            [
                'entity_id' => 1,
                'title' => 'Pasaporte',
                'url' => '/uploads/passports/passport_john.pdf',
                'type' => 'pdf',
            ],
            [
                'entity_id' => 1,
                'title' => 'Boletos de vuelo',
                'url' => '/uploads/tickets/flight_rio_123.pdf',
                'type' => 'pdf',
            ],
            [
                'entity_id' => 2,
                'title' => 'Plan de lanzamiento',
                'url' => '/uploads/projects/launch_plan.pdf',
                'type' => 'pdf',
            ],
            [
                'entity_id' => 4,
                'title' => 'Estado de cuenta mayo',
                'url' => '/uploads/finances/may_statement.pdf',
                'type' => 'pdf',
            ],
        ];

        $documentModel = new DocumentModel();

        foreach ($documents as $document) {
            $documentModel->insert($document);
        }
    }
}