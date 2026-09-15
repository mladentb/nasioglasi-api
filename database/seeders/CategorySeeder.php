<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Vozila',
                'icon' => 'car',
                'children' => [
                    ['name' => 'Automobili', 'meta_fields' => $this->autoMeta()],
                    ['name' => 'Motocikli', 'meta_fields' => $this->motoMeta()],
                    ['name' => 'Kamioni i dostavna'],
                    ['name' => 'Bicikli'],
                    ['name' => 'Prikolice'],
                    ['name' => 'Delovi i oprema'],
                    ['name' => 'Nautika'],
                ],
            ],
            [
                'name' => 'Nekretnine',
                'icon' => 'home',
                'children' => [
                    ['name' => 'Stanovi - prodaja', 'meta_fields' => $this->realEstateMeta()],
                    ['name' => 'Stanovi - iznajmljivanje', 'meta_fields' => $this->realEstateMeta()],
                    ['name' => 'Kuće - prodaja', 'meta_fields' => $this->realEstateMeta()],
                    ['name' => 'Kuće - iznajmljivanje', 'meta_fields' => $this->realEstateMeta()],
                    ['name' => 'Poslovni prostori', 'meta_fields' => $this->realEstateMeta()],
                    ['name' => 'Zemljišta'],
                    ['name' => 'Garaže'],
                    ['name' => 'Vikendice'],
                ],
            ],
            [
                'name' => 'Elektronika',
                'icon' => 'smartphone',
                'children' => [
                    ['name' => 'Mobilni telefoni'],
                    ['name' => 'Laptopi i računari'],
                    ['name' => 'Tableti'],
                    ['name' => 'Televizori'],
                    ['name' => 'Audio oprema'],
                    ['name' => 'Fotoaparati i kamere'],
                    ['name' => 'Gaming konzole i igrice'],
                    ['name' => 'Komponente i delovi'],
                ],
            ],
            [
                'name' => 'Odeća i obuća',
                'icon' => 'shirt',
                'children' => [
                    ['name' => 'Ženska odeća'],
                    ['name' => 'Muška odeća'],
                    ['name' => 'Dečija odeća'],
                    ['name' => 'Obuća'],
                    ['name' => 'Torbe i aksesoari'],
                    ['name' => 'Satovi i nakit'],
                ],
            ],
            [
                'name' => 'Kuća i bašta',
                'icon' => 'sofa',
                'children' => [
                    ['name' => 'Nameštaj'],
                    ['name' => 'Kućni aparati'],
                    ['name' => 'Alati'],
                    ['name' => 'Baštanski program'],
                    ['name' => 'Dekoracija'],
                    ['name' => 'Rasveta'],
                ],
            ],
            [
                'name' => 'Posao',
                'icon' => 'briefcase',
                'children' => [
                    ['name' => 'Ponuda posla'],
                    ['name' => 'Tražim posao'],
                    ['name' => 'Honorarni rad'],
                    ['name' => 'Praksa i stažiranje'],
                ],
            ],
            [
                'name' => 'Usluge',
                'icon' => 'wrench',
                'children' => [
                    ['name' => 'Zanatske usluge'],
                    ['name' => 'IT usluge'],
                    ['name' => 'Prevoz i selidbe'],
                    ['name' => 'Edukacija i kursevi'],
                    ['name' => 'Zdravlje i lepota'],
                    ['name' => 'Pravne i finansijske'],
                    ['name' => 'Foto i video'],
                ],
            ],
            [
                'name' => 'Sport i rekreacija',
                'icon' => 'dumbbell',
                'children' => [
                    ['name' => 'Fitness oprema'],
                    ['name' => 'Zimski sportovi'],
                    ['name' => 'Kampovanje i planinarenje'],
                    ['name' => 'Ribolov i lov'],
                    ['name' => 'Biciklizam'],
                    ['name' => 'Ostali sportovi'],
                ],
            ],
            [
                'name' => 'Ljubimci',
                'icon' => 'dog',
                'children' => [
                    ['name' => 'Psi'],
                    ['name' => 'Mačke'],
                    ['name' => 'Ptice'],
                    ['name' => 'Akvaristika'],
                    ['name' => 'Ostale životinje'],
                    ['name' => 'Hrana i oprema'],
                ],
            ],
            [
                'name' => 'Poljoprivreda',
                'icon' => 'tractor',
                'children' => [
                    ['name' => 'Poljoprivredne mašine'],
                    ['name' => 'Životinje'],
                    ['name' => 'Sadnice i seme'],
                    ['name' => 'Voće i povrće'],
                ],
            ],
            [
                'name' => 'Knjige, hobby i muzika',
                'icon' => 'book-open',
                'children' => [
                    ['name' => 'Knjige'],
                    ['name' => 'Muzički instrumenti'],
                    ['name' => 'Kolekcionarstvo'],
                    ['name' => 'Igračke'],
                    ['name' => 'Filmovi i muzika'],
                ],
            ],
            [
                'name' => 'Bebina oprema',
                'icon' => 'baby',
                'children' => [
                    ['name' => 'Kolica i autosedišta'],
                    ['name' => 'Odeća (0-3 god)'],
                    ['name' => 'Igračke'],
                    ['name' => 'Nameštaj za decu'],
                ],
            ],
            [
                'name' => 'Ostalo',
                'icon' => 'package',
                'children' => [
                    ['name' => 'Razno'],
                    ['name' => 'Poklanjam'],
                    ['name' => 'Tražim'],
                    ['name' => 'Zamena'],
                ],
            ],
        ];

        foreach ($categories as $sortOrder => $cat) {
            $parent = Category::create([
                'name' => $cat['name'],
                'slug' => Str::slug($cat['name']),
                'icon' => $cat['icon'] ?? null,
                'sort_order' => $sortOrder,
            ]);

            foreach ($cat['children'] as $childOrder => $child) {
                Category::create([
                    'parent_id' => $parent->id,
                    'name' => $child['name'],
                    'slug' => Str::slug($cat['name'] . '-' . $child['name']),
                    'sort_order' => $childOrder,
                    'meta_fields' => $child['meta_fields'] ?? null,
                ]);
            }
        }
    }

    private function autoMeta(): array
    {
        return [
            ['name' => 'brand', 'label' => 'Marka', 'type' => 'select', 'options' => [
                'Alfa Romeo', 'Audi', 'BMW', 'Chevrolet', 'Citroen', 'Cupra', 'Dacia', 'Daewoo',
                'Daihatsu', 'DS', 'Fiat', 'Ford', 'Honda', 'Hyundai', 'Infiniti', 'Jaguar', 'Jeep',
                'Kia', 'Lancia', 'Land Rover', 'Lexus', 'Mazda', 'Mercedes-Benz', 'MG', 'Mini',
                'Mitsubishi', 'Nissan', 'Opel', 'Peugeot', 'Porsche', 'Renault', 'Rover', 'Saab',
                'Seat', 'Škoda', 'Smart', 'SsangYong', 'Subaru', 'Suzuki', 'Tesla', 'Toyota',
                'Volkswagen', 'Volvo', 'Ostalo',
            ]],
            ['name' => 'model', 'label' => 'Model', 'type' => 'text'],
            ['name' => 'year', 'label' => 'Godište', 'type' => 'number'],
            ['name' => 'mileage', 'label' => 'Kilometraža (km)', 'type' => 'number'],
            ['name' => 'body_type', 'label' => 'Karoserija', 'type' => 'select', 'options' => [
                'Limuzina', 'Hečbek', 'Karavan', 'Kupe', 'Kabriolet', 'SUV/Džip',
                'Monovolumen (MiniVan)', 'Pick Up', 'Malo auto',
            ]],
            ['name' => 'fuel', 'label' => 'Gorivo', 'type' => 'select', 'options' => [
                'Dizel', 'Benzin', 'Benzin + Gas (TNG)', 'Benzin + Metan (CNG)',
                'Električni pogon', 'Hibrid', 'Plug-in Hibrid',
            ]],
            ['name' => 'engine_cc', 'label' => 'Kubikaža (cm³)', 'type' => 'number'],
            ['name' => 'power_kw', 'label' => 'Snaga (kW)', 'type' => 'number'],
            ['name' => 'transmission', 'label' => 'Menjač', 'type' => 'select', 'options' => [
                'Manuelni', 'Automatski', 'Poluautomatski',
            ]],
            ['name' => 'drive', 'label' => 'Pogon', 'type' => 'select', 'options' => [
                'Prednji', 'Zadnji', '4x4 (stalni)', '4x4 (priključni)',
            ]],
            ['name' => 'doors', 'label' => 'Broj vrata', 'type' => 'select', 'options' => [
                '2/3', '4/5',
            ]],
            ['name' => 'seats', 'label' => 'Broj sedišta', 'type' => 'select', 'options' => [
                '2', '4', '5', '6', '7', '8+',
            ]],
            ['name' => 'color', 'label' => 'Boja', 'type' => 'select', 'options' => [
                'Bela', 'Crna', 'Siva', 'Srebrna', 'Plava', 'Crvena', 'Zelena',
                'Bordo', 'Teget', 'Žuta', 'Narandžasta', 'Braon/Bež', 'Zlatna', 'Ljubičasta',
            ]],
            ['name' => 'steering_side', 'label' => 'Strana volana', 'type' => 'select', 'options' => [
                'Levi volan', 'Desni volan',
            ]],
            ['name' => 'air_conditioning', 'label' => 'Klima', 'type' => 'select', 'options' => [
                'Manuelna klima', 'Automatska klima', 'Nema klimu',
            ]],
            ['name' => 'emission_class', 'label' => 'Emisiona klasa', 'type' => 'select', 'options' => [
                'Euro 1', 'Euro 2', 'Euro 3', 'Euro 4', 'Euro 5', 'Euro 6', 'Euro 6d',
            ]],
            ['name' => 'registered', 'label' => 'Registrovan', 'type' => 'select', 'options' => [
                'Da', 'Ne',
            ]],
            ['name' => 'registration_until', 'label' => 'Registrovan do', 'type' => 'text'],
            ['name' => 'origin', 'label' => 'Poreklo vozila', 'type' => 'select', 'options' => [
                'Domaće vozilo', 'Uvoz - Nemačka', 'Uvoz - Švajcarska', 'Uvoz - Italija',
                'Uvoz - Austrija', 'Uvoz - Francuska', 'Uvoz - Holandija', 'Uvoz - Ostalo',
            ]],
            ['name' => 'ownership', 'label' => 'Vlasništvo', 'type' => 'select', 'options' => [
                '1. vlasnik', '2. vlasnik', '3+ vlasnik',
            ]],
            ['name' => 'interior_material', 'label' => 'Materijal enterijera', 'type' => 'select', 'options' => [
                'Koža', 'Polukoža', 'Štof', 'Velur', 'Alkantara',
            ]],
            ['name' => 'interior_color', 'label' => 'Boja enterijera', 'type' => 'select', 'options' => [
                'Crna', 'Siva', 'Bež/Krem', 'Braon', 'Crvena', 'Bela',
            ]],
        ];
    }

    private function motoMeta(): array
    {
        return [
            ['name' => 'brand', 'label' => 'Marka', 'type' => 'select', 'options' => [
                'Aprilia', 'Benelli', 'BMW', 'Ducati', 'Harley-Davidson', 'Honda', 'Husqvarna',
                'Kawasaki', 'KTM', 'Moto Guzzi', 'Piaggio', 'Suzuki', 'Triumph', 'Vespa', 'Yamaha', 'Ostalo',
            ]],
            ['name' => 'model', 'label' => 'Model', 'type' => 'text'],
            ['name' => 'year', 'label' => 'Godište', 'type' => 'number'],
            ['name' => 'mileage', 'label' => 'Kilometraža (km)', 'type' => 'number'],
            ['name' => 'engine_cc', 'label' => 'Kubikaža (cm³)', 'type' => 'number'],
            ['name' => 'power_kw', 'label' => 'Snaga (kW)', 'type' => 'number'],
            ['name' => 'type', 'label' => 'Tip', 'type' => 'select', 'options' => [
                'Sport', 'Touring', 'Naked', 'Enduro', 'Chopper/Cruiser', 'Skuter', 'Cross/Motokros', 'ATV/Quad', 'Ostalo',
            ]],
            ['name' => 'color', 'label' => 'Boja', 'type' => 'select', 'options' => [
                'Crna', 'Bela', 'Crvena', 'Plava', 'Siva', 'Zelena', 'Narandžasta', 'Žuta', 'Ostalo',
            ]],
            ['name' => 'registered', 'label' => 'Registrovan', 'type' => 'select', 'options' => ['Da', 'Ne']],
        ];
    }

    private function realEstateMeta(): array
    {
        return [
            ['name' => 'area_m2', 'label' => 'Površina (m²)', 'type' => 'number'],
            ['name' => 'rooms', 'label' => 'Broj soba', 'type' => 'number'],
            ['name' => 'floor', 'label' => 'Sprat', 'type' => 'number'],
            ['name' => 'total_floors', 'label' => 'Ukupno spratova', 'type' => 'number'],
            ['name' => 'heating', 'label' => 'Grejanje', 'type' => 'select', 'options' => [
                'Centralno', 'Etažno', 'TA peć', 'Klima', 'Gas', 'Drva', 'Bez grijanja',
            ]],
            ['name' => 'furnished', 'label' => 'Namešten', 'type' => 'select', 'options' => [
                'Da', 'Polunamešten', 'Ne',
            ]],
            ['name' => 'parking', 'label' => 'Parking', 'type' => 'select', 'options' => [
                'Garaža', 'Parking mjesto', 'Ulični', 'Nema',
            ]],
            ['name' => 'elevator', 'label' => 'Lift', 'type' => 'select', 'options' => ['Da', 'Ne']],
            ['name' => 'balcony', 'label' => 'Balkon', 'type' => 'select', 'options' => ['Da', 'Ne']],
            ['name' => 'year_built', 'label' => 'Godina izgradnje', 'type' => 'number'],
        ];
    }
}
