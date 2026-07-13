<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EcommerceSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = [];

        $topCategories = [
            'Dama' => [
                'description' => 'Moda femenina con categorias organizadas por tipo de prenda.',
                'children' => [
                    'Faldas' => 'Faldas casuales, formales y de temporada.',
                    'Camisas' => 'Camisas, blusas y tops para uso diario.',
                ],
            ],
            'Calzado' => 'Modelos urbanos y deportivos para uso diario.',
            'Ropa' => 'Prendas versatiles para un look actual.',
            'Accesorios' => 'Complementos funcionales con estilo.',
        ];

        foreach ($topCategories as $name => $data) {
            $description = is_array($data) ? $data['description'] : $data;

            $category = Category::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $description, 'parent_id' => null]
            );

            $categoryIds[$name] = $category->id;

            if (is_array($data) && isset($data['children'])) {
                foreach ($data['children'] as $childName => $childDescription) {
                    $child = Category::query()->updateOrCreate(
                        ['slug' => Str::slug($name.'-'.$childName)],
                        [
                            'name' => $childName,
                            'description' => $childDescription,
                            'parent_id' => $category->id,
                        ]
                    );

                    $categoryIds[$name.' / '.$childName] = $child->id;
                }
            }
        }

        $brands = [
            'Nike' => 'Rendimiento y estilo para uso diario.',
            'Adidas' => 'Diseno deportivo con enfoque urbano.',
            'Puma' => 'Moda casual y deportiva con energia.',
            'H&M' => 'Basicos modernos para outfits versatiles.',
            'Seiko' => 'Precision y elegancia en relojeria.',
            'New Era' => 'Gorras y accesorios de estilo urbano.',
            'Fjallraven' => 'Equipamiento durable para ciudad y viaje.',
        ];

        $brandIds = [];

        foreach ($brands as $name => $description) {
            $brand = Brand::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $description]
            );

            $brandIds[$name] = $brand->id;
        }

        $products = [
            [
                'sku' => 'SHOE-URBAN-001',
                'name' => 'Tenis Urban Wave',
                'brand' => 'Nike',
                'category' => 'Calzado',
                'price' => 79.99,
                'stock' => 25,
                'is_featured' => true,
                'show_in_main_banner' => true,
                'main_banner_order' => 1,
                'show_in_home_carousel' => true,
                'home_carousel_order' => 1,
                'description' => 'Diseno minimalista y comodo para uso diario.',
                'images' => [
                    'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
            [
                'sku' => 'HOOD-ESS-002',
                'name' => 'Hoodie Essential Black',
                'brand' => 'Adidas',
                'category' => 'Ropa',
                'price' => 49.90,
                'stock' => 40,
                'is_featured' => true,
                'show_in_main_banner' => true,
                'main_banner_order' => 2,
                'show_in_home_carousel' => true,
                'home_carousel_order' => 2,
                'description' => 'Sudadera premium con interior suave y corte moderno.',
                'images' => [
                    'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
            [
                'sku' => 'BAG-NOMAD-003',
                'name' => 'Mochila Nomad Pro',
                'brand' => 'Fjallraven',
                'category' => 'Accesorios',
                'price' => 64.50,
                'stock' => 18,
                'is_featured' => true,
                'show_in_main_banner' => true,
                'main_banner_order' => 3,
                'show_in_home_carousel' => true,
                'home_carousel_order' => 3,
                'description' => 'Espaciosa, resistente al agua y lista para ciudad o viaje.',
                'images' => [
                    'https://images.unsplash.com/photo-1491637639811-60e2756cc1c7?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1577733966973-d680bffd2e80?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
            [
                'sku' => 'WATCH-CHR-004',
                'name' => 'Reloj Chrono Silver',
                'brand' => 'Seiko',
                'category' => 'Accesorios',
                'price' => 120.00,
                'stock' => 12,
                'is_featured' => false,
                'show_in_main_banner' => false,
                'main_banner_order' => null,
                'show_in_home_carousel' => true,
                'home_carousel_order' => 4,
                'description' => 'Estilo elegante con acabado metalico y diseno atemporal.',
                'images' => [
                    'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
            [
                'sku' => 'SHIRT-LINEN-005',
                'name' => 'Camisa Linen Soft',
                'brand' => 'H&M',
                'category' => 'Ropa',
                'price' => 39.00,
                'stock' => 30,
                'is_featured' => true,
                'show_in_main_banner' => false,
                'main_banner_order' => null,
                'show_in_home_carousel' => true,
                'home_carousel_order' => 5,
                'description' => 'Ligera y transpirable, ideal para clima calido.',
                'images' => [
                    'https://images.unsplash.com/photo-1562157873-818bc0726f68?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
            [
                'sku' => 'BOOT-EDGE-006',
                'name' => 'Botines Street Edge',
                'brand' => 'Puma',
                'category' => 'Calzado',
                'price' => 95.25,
                'stock' => 16,
                'is_featured' => false,
                'show_in_main_banner' => false,
                'main_banner_order' => null,
                'show_in_home_carousel' => true,
                'home_carousel_order' => 6,
                'description' => 'Look urbano con gran traccion y confort.',
                'images' => [
                    'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
            [
                'sku' => 'CAP-METRO-007',
                'name' => 'Gorra Metro Club',
                'brand' => 'New Era',
                'category' => 'Accesorios',
                'price' => 24.00,
                'stock' => 50,
                'is_featured' => true,
                'show_in_main_banner' => false,
                'main_banner_order' => null,
                'show_in_home_carousel' => false,
                'home_carousel_order' => null,
                'description' => 'Diseno clasico para elevar cualquier outfit casual.',
                'images' => [
                    'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
            [
                'sku' => 'TSH-CORE-008',
                'name' => 'Playera Core White',
                'brand' => 'H&M',
                'category' => 'Ropa',
                'price' => 21.50,
                'stock' => 65,
                'is_featured' => false,
                'show_in_main_banner' => false,
                'main_banner_order' => null,
                'show_in_home_carousel' => false,
                'home_carousel_order' => null,
                'description' => 'Basico premium de algodon con corte limpio.',
                'images' => [
                    'https://images.unsplash.com/photo-1521577352947-9bb58764b69a?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
        ];

        foreach ($products as $item) {
            $product = Product::query()->updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'category_id' => $categoryIds[$item['category']],
                    'brand_id' => $brandIds[$item['brand']],
                    'name' => $item['name'],
                    'slug' => Str::slug($item['name']),
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'stock' => $item['stock'],
                    'is_featured' => $item['is_featured'],
                    'show_in_main_banner' => $item['show_in_main_banner'] ?? false,
                    'main_banner_order' => $item['show_in_main_banner'] ? ($item['main_banner_order'] ?? null) : null,
                    'show_in_home_carousel' => $item['show_in_home_carousel'] ?? false,
                    'home_carousel_order' => $item['show_in_home_carousel'] ? ($item['home_carousel_order'] ?? null) : null,
                ]
            );

            $product->images()->delete();

            foreach ($item['images'] as $index => $imageUrl) {
                $product->images()->create([
                    'url' => $imageUrl,
                    'alt_text' => $item['name'],
                    'sort_order' => $index + 1,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        $bulkCount = (int) env('SEED_BULK_PRODUCTS_COUNT', app()->environment('production') ? 0 : 1000);

        if ($bulkCount > 0) {
            $this->seedBulkProducts($categoryIds, $brandIds, $bulkCount);
        }
    }

    private function seedBulkProducts(array $categoryIds, array $brandIds, int $count): void
    {
        $categoryKeys = array_values(array_keys($categoryIds));
        $brandKeys = array_values(array_keys($brandIds));

        $adjectives = [
            'Urban', 'Classic', 'Active', 'Fresh', 'Prime', 'Nova', 'Smart', 'Bold', 'Mono', 'Elite',
            'Flex', 'Street', 'Pure', 'Core', 'Ultra', 'Light', 'Daily', 'Peak', 'Motion', 'Vibe',
        ];

        $nouns = [
            'Wave', 'Pulse', 'Aura', 'Mode', 'Shift', 'Flow', 'Line', 'Edge', 'Spark', 'Drive',
            'Vertex', 'Orbit', 'Layer', 'Stride', 'Glow', 'Link', 'Frame', 'Drift', 'Canvas', 'Loop',
        ];

        $descriptions = [
            'Diseñado para alto volumen de pruebas de catálogo.',
            'Producto generado para validar filtros, paginación y búsqueda.',
            'Ideal para medir rendimiento del catálogo con datos extensos.',
            'Artículo de prueba con distribución automática de inventario.',
            'Contenido de demo para stress test del storefront.',
        ];

        $images = [
            'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1526178615678-8c5f1a10b7ad?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=1200&q=80',
        ];

        for ($i = 1; $i <= $count; $i++) {
            $sku = sprintf('BULK-%04d', $i);
            $categoryName = $categoryKeys[($i - 1) % count($categoryKeys)];
            $brandName = $brandKeys[($i - 1) % count($brandKeys)];
            $adjective = $adjectives[$i % count($adjectives)];
            $noun = $nouns[$i % count($nouns)];
            $name = sprintf('%s %s %03d', $adjective, $noun, $i);
            $price = round(14 + (($i % 180) * 0.85), 2);
            $stock = 5 + ($i % 120);
            $featured = $i % 11 === 0;
            $mainBanner = $i % 47 === 0;
            $homeCarousel = $i % 7 === 0;

            $product = Product::query()->updateOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $categoryIds[$categoryName],
                    'brand_id' => $brandIds[$brandName],
                    'name' => $name,
                    'slug' => Str::slug($sku.'-'.$name),
                    'description' => $descriptions[$i % count($descriptions)],
                    'price' => $price,
                    'stock' => $stock,
                    'is_featured' => $featured,
                    'show_in_main_banner' => $mainBanner,
                    'main_banner_order' => $mainBanner ? (($i % 3) + 1) : null,
                    'show_in_home_carousel' => $homeCarousel,
                    'home_carousel_order' => $homeCarousel ? (($i % 6) + 1) : null,
                ]
            );

            $product->images()->delete();

            $imageUrl = $images[$i % count($images)];

            $product->images()->create([
                'url' => $imageUrl,
                'alt_text' => $name,
                'sort_order' => 1,
                'is_primary' => true,
            ]);
        }
    }
}
