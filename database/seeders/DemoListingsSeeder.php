<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoListingsSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo users
        $users = $this->createUsers();

        // Get categories
        $categories = Category::whereNotNull('parent_id')->get()->keyBy('slug');

        // Seed listings per category
        $this->seedAutomobili($users, $categories);
        $this->seedNekretnine($users, $categories);
        $this->seedElektronika($users, $categories);
        $this->seedOdeca($users, $categories);
        $this->seedKucaIBasta($users, $categories);
        $this->seedPosao($users, $categories);
        $this->seedSportIRekreacija($users, $categories);
        $this->seedLjubimci($users, $categories);
        $this->seedOstalo($users, $categories);

        $this->command->info('Demo listings seeded: ' . Listing::count() . ' listings');
    }

    private function createUsers(): array
    {
        $usersData = [
            ['name' => 'Marko Petrović', 'email' => 'marko@demo.rs', 'phone' => '+381611111111', 'city' => 'Beograd', 'user_type' => 'individual'],
            ['name' => 'Ana Jovanović', 'email' => 'ana@demo.rs', 'phone' => '+381622222222', 'city' => 'Novi Sad', 'user_type' => 'individual'],
            ['name' => 'Nikola Đorđević', 'email' => 'nikola@demo.rs', 'phone' => '+381633333333', 'city' => 'Niš', 'user_type' => 'individual'],
            ['name' => 'Jelena Nikolić', 'email' => 'jelena@demo.rs', 'phone' => '+381644444444', 'city' => 'Kragujevac', 'user_type' => 'individual'],
            ['name' => 'Stefan Ilić', 'email' => 'stefan@demo.rs', 'phone' => '+381655555555', 'city' => 'Subotica', 'user_type' => 'individual'],
            ['name' => 'Auto Kuća Petrović', 'email' => 'autokuca@demo.rs', 'phone' => '+381666666666', 'city' => 'Beograd', 'user_type' => 'company', 'company_name' => 'Auto Kuća Petrović DOO', 'pib' => '123456789'],
            ['name' => 'Nekretnine Centar', 'email' => 'nekretnine@demo.rs', 'phone' => '+381677777777', 'city' => 'Novi Sad', 'user_type' => 'company', 'company_name' => 'Nekretnine Centar DOO', 'pib' => '987654321'],
            ['name' => 'TechShop Srbija', 'email' => 'techshop@demo.rs', 'phone' => '+381688888888', 'city' => 'Beograd', 'user_type' => 'company', 'company_name' => 'TechShop Srbija DOO', 'pib' => '111222333'],
        ];

        $users = [];
        foreach ($usersData as $data) {
            $users[] = User::create(array_merge($data, [
                'password' => Hash::make(env('SEED_DEMO_PASSWORD', Str::random(32))),
                'country' => 'RS',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]));
        }
        return $users;
    }

    private function createListing(User $user, Category $category, array $data): Listing
    {
        $listing = Listing::create(array_merge([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'country' => 'RS',
            'status' => 'active',
            'expires_at' => now()->addDays(rand(5, 30)),
            'views_count' => rand(10, 2000),
        ], $data));

        // Download and attach placeholder image
        $this->attachDemoImage($listing, $data['title']);

        return $listing;
    }

    private function attachDemoImage(Listing $listing, string $title): void
    {
        $keywords = $this->getImageKeyword($listing->category_id);
        $width = 800;
        $height = 600;

        // Use picsum.photos for random images with seed for consistency
        $seed = md5($title);
        $urls = [
            "https://picsum.photos/seed/{$seed}a/{$width}/{$height}",
            "https://picsum.photos/seed/{$seed}b/{$width}/{$height}",
            "https://picsum.photos/seed/{$seed}c/{$width}/{$height}",
        ];

        $imageCount = rand(1, 3);
        for ($i = 0; $i < $imageCount; $i++) {
            try {
                $imageData = @file_get_contents($urls[$i]);
                if (! $imageData) continue;

                $dir = "listings/{$listing->id}";
                $filename = uniqid() . '.jpg';
                $path = "{$dir}/{$filename}";
                $thumbPath = "{$dir}/thumb_{$filename}";

                Storage::disk('public')->put($path, $imageData);
                Storage::disk('public')->put($thumbPath, $imageData); // Same for demo

                ListingImage::create([
                    'listing_id' => $listing->id,
                    'path' => $path,
                    'thumbnail_path' => $thumbPath,
                    'sort_order' => $i,
                ]);
            } catch (\Exception $e) {
                // Skip if image download fails
                continue;
            }
        }
    }

    private function getImageKeyword(int $categoryId): string
    {
        $cat = Category::find($categoryId);
        $parentSlug = $cat?->parent?->slug ?? $cat?->slug ?? 'item';
        return match ($parentSlug) {
            'vozila' => 'car',
            'nekretnine' => 'house',
            'elektronika' => 'technology',
            'odeca-i-obuca' => 'fashion',
            'kuca-i-basta' => 'furniture',
            'sport-i-rekreacija' => 'sport',
            'ljubimci' => 'pet',
            default => 'product',
        };
    }

    private function seedAutomobili(array $users, $categories): void
    {
        $cat = $categories['vozila-automobili'] ?? null;
        if (! $cat) return;

        $listings = [
            ['title' => 'Volkswagen Golf 7 1.6 TDI Comfortline', 'description' => "Prodajem Golf 7, 1.6 TDI, 2015. godište, 160.000km.\nUrađen veliki servis, nove gume, klima ispravna.\nVozilo u odličnom stanju, redovno održavano u ovlašćenom servisu.\nSva dokumentacija uredna, registrovan do maja 2027.", 'price' => 11500, 'currency' => 'EUR', 'city' => 'Beograd', 'meta' => ['brand' => 'Volkswagen', 'model' => 'Golf 7', 'year' => 2015, 'mileage' => 160000, 'body_type' => 'Hečbek', 'fuel' => 'Dizel', 'engine_cc' => 1598, 'power_kw' => 81, 'transmission' => 'Manuelni', 'drive' => 'Prednji', 'doors' => '4/5', 'color' => 'Siva', 'air_conditioning' => 'Manuelna klima', 'emission_class' => 'Euro 5', 'registered' => 'Da', 'origin' => 'Uvoz - Nemačka', 'ownership' => '2. vlasnik']],
            ['title' => 'BMW 320d F30 M Paket', 'description' => "BMW 320d, F30, M paket, 2014. godište.\nFull oprema: koža, navigacija, grejanje sedišta, LED farovi.\nMehanički potpuno ispravan, motor i menjač bez primedbi.\nCena nije fiksna.", 'price' => 14900, 'currency' => 'EUR', 'city' => 'Novi Sad', 'meta' => ['brand' => 'BMW', 'model' => '320d F30', 'year' => 2014, 'mileage' => 195000, 'body_type' => 'Limuzina', 'fuel' => 'Dizel', 'engine_cc' => 1995, 'power_kw' => 135, 'transmission' => 'Automatski', 'drive' => 'Zadnji', 'doors' => '4/5', 'color' => 'Crna', 'air_conditioning' => 'Automatska klima', 'emission_class' => 'Euro 5', 'registered' => 'Da', 'origin' => 'Uvoz - Nemačka', 'ownership' => '2. vlasnik', 'interior_material' => 'Koža', 'interior_color' => 'Crna']],
            ['title' => 'Škoda Octavia 2.0 TDI Style', 'description' => "Škoda Octavia karavan, 2018. godište, 2.0 TDI, 150ks.\nPrvi vlasnik, kupljena nova u Srbiji.\nGaražirana, servisna knjiga kompletna.\nSva oprema: navigacija, panorama krov, LED.", 'price' => 16500, 'currency' => 'EUR', 'city' => 'Beograd', 'meta' => ['brand' => 'Škoda', 'model' => 'Octavia', 'year' => 2018, 'mileage' => 110000, 'body_type' => 'Karavan', 'fuel' => 'Dizel', 'engine_cc' => 1968, 'power_kw' => 110, 'transmission' => 'Manuelni', 'drive' => 'Prednji', 'doors' => '4/5', 'color' => 'Bela', 'registered' => 'Da', 'origin' => 'Domaće vozilo', 'ownership' => '1. vlasnik']],
            ['title' => 'Fiat 500 1.2 Lounge - Ženski auto', 'description' => "Prelepi Fiat 500, 1.2 benzin, 2016. godište.\nSamo 65.000km, garažiran, bez ogrebotina.\nPanorama krov, klima, bluetooth.\nIdealan gradski auto.", 'price' => 7800, 'currency' => 'EUR', 'city' => 'Kragujevac', 'meta' => ['brand' => 'Fiat', 'model' => '500', 'year' => 2016, 'mileage' => 65000, 'body_type' => 'Malo auto', 'fuel' => 'Benzin', 'engine_cc' => 1242, 'power_kw' => 51, 'transmission' => 'Manuelni', 'drive' => 'Prednji', 'doors' => '2/3', 'color' => 'Crvena', 'registered' => 'Da', 'origin' => 'Uvoz - Italija', 'ownership' => '1. vlasnik']],
            ['title' => 'Renault Clio 4 1.5 dCi GPS', 'description' => "Renault Clio 4, 2017. godište, 1.5 dCi, 90ks.\nNavigacija, tempomat, parking senzori.\nPotrošnja 4.5l na 100km. Ekonomičan i pouzdan.", 'price' => 7500, 'currency' => 'EUR', 'city' => 'Niš', 'meta' => ['brand' => 'Renault', 'model' => 'Clio 4', 'year' => 2017, 'mileage' => 130000, 'body_type' => 'Hečbek', 'fuel' => 'Dizel', 'engine_cc' => 1461, 'power_kw' => 66, 'transmission' => 'Manuelni', 'drive' => 'Prednji', 'doors' => '4/5', 'color' => 'Plava', 'registered' => 'Da', 'origin' => 'Uvoz - Francuska']],
            ['title' => 'Toyota RAV4 2.5 Hybrid AWD', 'description' => "Toyota RAV4 Hybrid, 2021. godište, automatik, 4x4.\nKupljen nov u Toyota Srbija, garancija do 2026.\nSamo 35.000km, kao nov.\nAdaptivni tempomat, 360 kamera, kožni enterijer.", 'price' => 34900, 'currency' => 'EUR', 'city' => 'Beograd', 'is_premium' => true, 'meta' => ['brand' => 'Toyota', 'model' => 'RAV4', 'year' => 2021, 'mileage' => 35000, 'body_type' => 'SUV/Džip', 'fuel' => 'Hibrid', 'engine_cc' => 2487, 'power_kw' => 160, 'transmission' => 'Automatski', 'drive' => '4x4 (stalni)', 'doors' => '4/5', 'color' => 'Bela', 'registered' => 'Da', 'origin' => 'Domaće vozilo', 'ownership' => '1. vlasnik', 'interior_material' => 'Koža', 'interior_color' => 'Crna']],
            ['title' => 'Opel Astra K 1.6 CDTI Dynamic', 'description' => "Opel Astra K, 2016, 1.6 cdti 136ks.\nFull LED Matrix farovi, Navi 900 IntelliLink.\nVozilo bez ulaganja, sve radi perfektno.", 'price' => 9800, 'currency' => 'EUR', 'city' => 'Subotica', 'meta' => ['brand' => 'Opel', 'model' => 'Astra K', 'year' => 2016, 'mileage' => 175000, 'body_type' => 'Hečbek', 'fuel' => 'Dizel', 'engine_cc' => 1598, 'power_kw' => 100, 'transmission' => 'Manuelni', 'drive' => 'Prednji', 'doors' => '4/5', 'color' => 'Srebrna', 'registered' => 'Da', 'origin' => 'Uvoz - Nemačka']],
            ['title' => 'Mercedes C220d W205 AMG Line', 'description' => "Mercedes C klasa, W205, C220d, 2017.\nAMG Line paket, 9G-Tronic automatik.\nAmbient osvetljenje, Burmester audio, panorama.\nUvoz Švajcarska, servisiran u ovlašćenom.", 'price' => 23500, 'currency' => 'EUR', 'city' => 'Beograd', 'is_premium' => true, 'meta' => ['brand' => 'Mercedes-Benz', 'model' => 'C220d W205', 'year' => 2017, 'mileage' => 145000, 'body_type' => 'Limuzina', 'fuel' => 'Dizel', 'engine_cc' => 2143, 'power_kw' => 125, 'transmission' => 'Automatski', 'drive' => 'Zadnji', 'doors' => '4/5', 'color' => 'Teget', 'air_conditioning' => 'Automatska klima', 'emission_class' => 'Euro 6', 'registered' => 'Da', 'origin' => 'Uvoz - Švajcarska', 'interior_material' => 'Koža', 'interior_color' => 'Crna']],
        ];

        foreach ($listings as $i => $data) {
            $data['price_type'] = 'negotiable';
            $this->createListing($users[$i % count($users)], $cat, $data);
        }
    }

    private function seedNekretnine(array $users, $categories): void
    {
        $items = [
            ['cat' => 'nekretnine-stanovi-prodaja', 'title' => 'Trosoban stan 72m² Novi Beograd, Blok 63', 'description' => "Prodaje se trosoban stan u Bloku 63, Novi Beograd.\n72m², 5. sprat, lift, terasa.\nCentralno grejanje, parkiran. Renoviran 2022.\nBlizina škole, vrtića, marketa.", 'price' => 125000, 'currency' => 'EUR', 'city' => 'Beograd', 'meta' => ['area_m2' => 72, 'rooms' => 3, 'floor' => 5, 'total_floors' => 10, 'heating' => 'Centralno', 'furnished' => 'Namešten', 'parking' => 'Garaža', 'elevator' => 'Da', 'balcony' => 'Da', 'year_built' => 1985]],
            ['cat' => 'nekretnine-stanovi-prodaja', 'title' => 'Dvosoban stan 55m² Vračar, Crveni Krst', 'description' => "Svetao dvosoban stan na Vračaru, ulica Krunska.\n55m², 3. sprat, bez lifta.\nVisoki plafoni, parket, renoviran.\nOdlična lokacija, blizina centra.", 'price' => 115000, 'currency' => 'EUR', 'city' => 'Beograd', 'is_premium' => true, 'meta' => ['area_m2' => 55, 'rooms' => 2, 'floor' => 3, 'heating' => 'Etažno', 'furnished' => 'Polunamešten', 'elevator' => 'Ne', 'balcony' => 'Da', 'year_built' => 1935]],
            ['cat' => 'nekretnine-stanovi-iznajmljivanje', 'title' => 'Izdajem garsonjeru 28m² Dorćol', 'description' => "Izdajem namještenu garsonjeru na Dorćolu.\n28m², prizemlje, zaseban ulaz.\nInternet, klima, veš mašina.\nCena 350€ + depozit.", 'price' => 350, 'currency' => 'EUR', 'city' => 'Beograd', 'meta' => ['area_m2' => 28, 'rooms' => 1, 'floor' => 0, 'heating' => 'Klima', 'furnished' => 'Namešten']],
            ['cat' => 'nekretnine-kuce-prodaja', 'title' => 'Kuća sa placem 500m² Zemun, Gardoš', 'description' => "Prodajem kuću na Gardošu sa lepim placem.\nKuća 120m², plac 500m².\n4 sobe, 2 kupatila, garaža za 2 auta.\nMirna ulica, blizina Dunava.", 'price' => 195000, 'currency' => 'EUR', 'city' => 'Beograd', 'meta' => ['area_m2' => 120, 'rooms' => 4, 'heating' => 'Gas', 'parking' => 'Garaža', 'year_built' => 2005]],
            ['cat' => 'nekretnine-stanovi-prodaja', 'title' => 'Jednosoban stan 38m² Liman, Novi Sad', 'description' => "Nov jednosoban stan na Limanu 3.\nUseljivost odmah, vlasnik 1/1.\nKlima, interfon, video nadzor.", 'price' => 72000, 'currency' => 'EUR', 'city' => 'Novi Sad', 'meta' => ['area_m2' => 38, 'rooms' => 1, 'floor' => 2, 'total_floors' => 6, 'heating' => 'Centralno', 'elevator' => 'Da', 'year_built' => 2023]],
        ];

        foreach ($items as $i => $data) {
            $catSlug = $data['cat'];
            unset($data['cat']);
            $cat = $categories[$catSlug] ?? null;
            if (! $cat) continue;
            $data['price_type'] = 'fixed';
            $this->createListing($users[$i % count($users)], $cat, $data);
        }
    }

    private function seedElektronika(array $users, $categories): void
    {
        $items = [
            ['cat' => 'elektronika-mobilni-telefoni', 'title' => 'iPhone 15 Pro Max 256GB Natural Titanium', 'description' => "Prodajem iPhone 15 Pro Max, 256GB, Natural Titanium boja.\nKupljen u iStyle, garancija do marta 2025.\nBaterija 96%, bez ogrebotina.\nIde sa originalnom kutijom i punjačem.", 'price' => 95000, 'city' => 'Beograd'],
            ['cat' => 'elektronika-mobilni-telefoni', 'title' => 'Samsung Galaxy S24 Ultra 512GB', 'description' => "Samsung S24 Ultra, 512GB, Titanium Gray.\nKao nov, korišćen 3 meseca sa zaštitnim staklom.\nS Pen, kamera 200MP.", 'price' => 110000, 'city' => 'Novi Sad', 'is_premium' => true],
            ['cat' => 'elektronika-laptopi-i-racunari', 'title' => 'MacBook Air M2 15" 16GB/512GB', 'description' => "Apple MacBook Air M2, 15 inča, 16GB RAM, 512GB SSD.\nMidnight boja, kupljen jun 2024.\n38 ciklusa baterije, praktično nov.\nSa originalnim punjačem i kutijom.", 'price' => 135000, 'city' => 'Beograd'],
            ['cat' => 'elektronika-laptopi-i-racunari', 'title' => 'Gaming PC RTX 4070 / Ryzen 7 5800X', 'description' => "Gaming računar, sklopljen 2024:\n- Ryzen 7 5800X\n- RTX 4070 12GB\n- 32GB DDR4 3600MHz\n- 1TB NVMe SSD\n- 750W napajanje\n- RGB kućište\nSve radi perfektno, bez problema.", 'price' => 120000, 'city' => 'Niš'],
            ['cat' => 'elektronika-televizori', 'title' => 'Samsung 55" QLED 4K Smart TV', 'description' => "Samsung QE55Q80C, 55 inča, QLED 4K.\nKupljen 2024, garancija do 2026.\nSmart TV, WiFi, Bluetooth, HDR10+.\nSa originalnim daljinskim.", 'price' => 65000, 'city' => 'Beograd'],
            ['cat' => 'elektronika-gaming-konzole-i-igrice', 'title' => 'PlayStation 5 Slim + 2 džojstika + 5 igrica', 'description' => "PS5 Slim sa čitačem diskova.\n2 DualSense džojstika (beli i crni).\nIgrice: GTA V, FIFA 25, Spider-Man 2, God of War Ragnarök, The Last of Us Part II.", 'price' => 55000, 'city' => 'Kragujevac'],
        ];

        foreach ($items as $i => $data) {
            $catSlug = $data['cat'];
            unset($data['cat']);
            $cat = $categories[$catSlug] ?? null;
            if (! $cat) continue;
            $data['price_type'] = $data['price_type'] ?? 'fixed';
            $data['currency'] = $data['currency'] ?? 'RSD';
            $data['condition'] = 'used';
            $this->createListing($users[$i % count($users)], $cat, $data);
        }
    }

    private function seedOdeca(array $users, $categories): void
    {
        $items = [
            ['cat' => 'odeca-i-obuca-muska-odeca', 'title' => 'Nike Air Jordan 1 High OG 43', 'description' => "Originalne Nike Air Jordan 1, broj 43.\nNošene par puta, stanje 9/10.\nSa kutijom.", 'price' => 12000, 'city' => 'Beograd'],
            ['cat' => 'odeca-i-obuca-zenska-odeca', 'title' => 'Zara zimska jakna vel. M', 'description' => "Zara perjana zimska jakna, crna, veličina M.\nKupljena ove sezone, nošena par puta.\nTopla i kvalitetna.", 'price' => 5500, 'city' => 'Novi Sad'],
            ['cat' => 'odeca-i-obuca-satovi-i-nakit', 'title' => 'Casio G-Shock GA-2100 CasiOak', 'description' => "Casio G-Shock GA-2100, crni, CasiOak model.\nKao nov, sa kutijom i garancijom.\nVodootporan 200m.", 'price' => 9500, 'city' => 'Beograd'],
        ];

        foreach ($items as $i => $data) {
            $catSlug = $data['cat'];
            unset($data['cat']);
            $cat = $categories[$catSlug] ?? null;
            if (! $cat) continue;
            $data['price_type'] = 'fixed';
            $data['currency'] = 'RSD';
            $data['condition'] = 'used';
            $this->createListing($users[$i % count($users)], $cat, $data);
        }
    }

    private function seedKucaIBasta(array $users, $categories): void
    {
        $items = [
            ['cat' => 'kuca-i-basta-namestaj', 'title' => 'Kožna garnitura 3+2+1 bež boja', 'description' => "Prodajem kožnu garnituru, trosed + dvosed + fotelja.\nPrava koža, bež boja, odlično stanje.\nBez oštećenja, iz nepušačkog domaćinstva.", 'price' => 45000, 'city' => 'Beograd'],
            ['cat' => 'kuca-i-basta-kucni-aparati', 'title' => 'Bosch veš mašina 8kg A+++', 'description' => "Bosch veš mašina, 8kg, energetska klasa A+++.\nKupljena 2023, radi besprekorno.\nSvi programi funkcionišu.", 'price' => 25000, 'city' => 'Niš'],
            ['cat' => 'kuca-i-basta-alati', 'title' => 'Makita aku bušilica 18V set', 'description' => "Makita DDF484 aku bušilica/šrafilica.\n18V, 2 baterije 5.0Ah, punjač, kofer.\nKorišćena na jednom projektu.", 'price' => 18000, 'city' => 'Beograd'],
        ];

        foreach ($items as $i => $data) {
            $catSlug = $data['cat'];
            unset($data['cat']);
            $cat = $categories[$catSlug] ?? null;
            if (! $cat) continue;
            $data['price_type'] = 'fixed';
            $data['currency'] = 'RSD';
            $data['condition'] = 'used';
            $this->createListing($users[$i % count($users)], $cat, $data);
        }
    }

    private function seedPosao(array $users, $categories): void
    {
        $items = [
            ['cat' => 'posao-ponuda-posla', 'title' => 'PHP/Laravel developer - remote posao', 'description' => "Tražimo iskusnog PHP/Laravel developera za rad na SaaS projektu.\n\nZahtevi:\n- 3+ godine iskustva sa Laravel\n- PostgreSQL, Redis\n- REST API dizajn\n- Git\n\nNudimo:\n- Full remote\n- Fleksibilno radno vreme\n- Konkurentna plata", 'price' => null, 'price_type' => 'contact', 'city' => 'Beograd'],
            ['cat' => 'posao-ponuda-posla', 'title' => 'Konobar/ica - kafić u centru Novog Sada', 'description' => "Potreban konobar/ica za rad u kafiću u centru Novog Sada.\n\nUslovi:\n- Radno iskustvo min. 1 godina\n- Komunikativnost\n- Rad u smenama\n\nPlata po dogovoru + napojnice.", 'price' => null, 'price_type' => 'contact', 'city' => 'Novi Sad'],
        ];

        foreach ($items as $i => $data) {
            $catSlug = $data['cat'];
            unset($data['cat']);
            $cat = $categories[$catSlug] ?? null;
            if (! $cat) continue;
            $data['currency'] = 'RSD';
            $data['price_type'] = $data['price_type'] ?? 'contact';
            $this->createListing($users[$i % count($users)], $cat, $data);
        }
    }

    private function seedSportIRekreacija(array $users, $categories): void
    {
        $items = [
            ['cat' => 'sport-i-rekreacija-fitness-oprema', 'title' => 'Bench klupa + set tegova 100kg', 'description' => "Prodajem bench klupu sa stalcima i set tegova.\n- Klupa podesiva (ravna/kosa)\n- Šipka 180cm + EZ šipka\n- Tegovi: 2x20kg, 2x10kg, 2x5kg, 4x2.5kg\nOdlično stanje.", 'price' => 25000, 'city' => 'Beograd'],
            ['cat' => 'sport-i-rekreacija-zimski-sportovi', 'title' => 'Atomic skije 170cm + vezovi + štapovi', 'description' => "Atomic Redster S9i skije, 170cm.\nSa Atomic vezovima i štapovima.\nKorišćene dve sezone, dobro očuvane.", 'price' => 35000, 'city' => 'Novi Sad'],
        ];

        foreach ($items as $i => $data) {
            $catSlug = $data['cat'];
            unset($data['cat']);
            $cat = $categories[$catSlug] ?? null;
            if (! $cat) continue;
            $data['price_type'] = 'fixed';
            $data['currency'] = 'RSD';
            $data['condition'] = 'used';
            $this->createListing($users[$i % count($users)], $cat, $data);
        }
    }

    private function seedLjubimci(array $users, $categories): void
    {
        $items = [
            ['cat' => 'ljubimci-psi', 'title' => 'Štenad zlatnog retrivera - spremni za nove domove', 'description' => "Prodajem štenad zlatnog retrivera, stara 8 nedelja.\nVakcinisana, očišćena od parazita.\nOba roditelja sa rodovnikom.\n3 muška i 2 ženska šteneta.", 'price' => 500, 'currency' => 'EUR', 'city' => 'Subotica'],
            ['cat' => 'ljubimci-macke', 'title' => 'Britanska kratkodlaka mačka - odrasla', 'description' => "Poklanjam britansku kratkodlaku mačku, 3 godine.\nSterilisana, vakcinisana, čipovana.\nMirne naravi, navikla na stan.\nRazlog: selidba u inostranstvo.", 'price' => null, 'price_type' => 'free', 'city' => 'Beograd'],
        ];

        foreach ($items as $i => $data) {
            $catSlug = $data['cat'];
            unset($data['cat']);
            $cat = $categories[$catSlug] ?? null;
            if (! $cat) continue;
            $data['price_type'] = $data['price_type'] ?? 'fixed';
            $data['currency'] = $data['currency'] ?? 'RSD';
            $this->createListing($users[$i % count($users)], $cat, $data);
        }
    }

    private function seedOstalo(array $users, $categories): void
    {
        $items = [
            ['cat' => 'ostalo-poklanjam', 'title' => 'Poklanjam dečiji krevetac sa dušekom', 'description' => "Poklanjam dečiji krevetac 120x60cm sa dušekom.\nKorišćen za jedno dete, očuvan.\nSamo lično preuzimanje, Beograd - Banovo Brdo.", 'price' => null, 'price_type' => 'free', 'city' => 'Beograd'],
            ['cat' => 'ostalo-razno', 'title' => 'Drva za ogrev - bukva, iscepana', 'description' => "Prodajem drva za ogrev, bukovina, iscepana.\nCena po metru: 7500 din.\nDostava na teritoriji Beograda.", 'price' => 7500, 'price_type' => 'fixed', 'city' => 'Beograd'],
        ];

        foreach ($items as $i => $data) {
            $catSlug = $data['cat'];
            unset($data['cat']);
            $cat = $categories[$catSlug] ?? null;
            if (! $cat) continue;
            $data['currency'] = $data['currency'] ?? 'RSD';
            $data['price_type'] = $data['price_type'] ?? 'fixed';
            $this->createListing($users[$i % count($users)], $cat, $data);
        }
    }
}
