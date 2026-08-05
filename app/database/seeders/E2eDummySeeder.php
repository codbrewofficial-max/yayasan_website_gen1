<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignLink;
use App\Models\Donation;
use App\Models\GtmConfig;
use App\Models\Lead;
use App\Models\LinkClick;
use App\Models\PageVisit;
use App\Models\Program;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Dummy data untuk End-to-End Test manual.
 *
 * Jalankan SETELAH DatabaseSeeder (dengan migrate:fresh --seed):
 *   php artisan db:seed --class=E2eDummySeeder
 *
 * Menambahkan: donasi dengan berbagai status/metode/UTM, leads, page visits,
 * link click, GTM config aktif, dan tenant kedua untuk uji insight platform.
 */
class E2eDummySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('subdomain', 'kerkomit')->firstOrFail();
        $author = User::where('tenant_id', $tenant->id)->firstOrFail();

        $campaign = Campaign::where('tenant_id', $tenant->id)
            ->where('slug', 'beasiswa-batch-2026')
            ->firstOrFail();

        $this->command?->info('E2E dummy: seeding kerkomit...');

        $link = $this->seedCampaignLink($tenant, $author, $campaign);
        $this->seedDonations($tenant, $campaign, $link);
        $this->seedLeads($tenant);
        $this->seedPageVisits($tenant);
        $this->seedGtmConfig($tenant, $author);
        $this->seedSecondTenant();

        $this->command?->info('E2E dummy selesai.');
    }

    protected function seedCampaignLink(Tenant $tenant, User $author, Campaign $campaign): CampaignLink
    {
        $link = CampaignLink::firstOrCreate(
            ['short_code' => 'E2EBEA'],
            [
                'tenant_id' => $tenant->id,
                'campaign_id' => $campaign->id,
                'label' => 'Google Ads E2E',
                'utm_source' => 'google',
                'utm_medium' => 'cpc',
                'utm_campaign' => $campaign->slug,
                'target_url' => route('public.campaign', $campaign->slug) . '?utm_source=google&utm_medium=cpc&utm_campaign=' . $campaign->slug,
                'created_by' => $author->id,
            ],
        );

        $link->clicks_count = 14;
        $link->last_clicked_at = now()->subDays(2);
        $link->save();

        foreach ([now()->subDays(20), now()->subDays(9), now()->subDays(2)] as $i => $at) {
            LinkClick::create([
                'tenant_id' => $tenant->id,
                'campaign_link_id' => $link->id,
                'referrer' => 'https://google.com',
                'device_type' => $i % 2 === 0 ? 'mobile' : 'desktop',
                'clicked_at' => $at,
            ]);
        }

        return $link;
    }

    protected function seedDonations(Tenant $tenant, Campaign $campaign, CampaignLink $link): void
    {
        $paid = [
            ['order' => 'E2E-DONA-0001', 'amount' => 100000, 'name' => 'Andi Pratama', 'email' => 'andi.p@example.com', 'phone' => '081234560001', 'method' => 'qris', 'utm' => 'instagram', 'months' => 5, 'anon' => false],
            ['order' => 'E2E-DONA-0002', 'amount' => 250000, 'name' => 'Budi Santoso', 'email' => 'budi.s@example.com', 'phone' => '081234560002', 'method' => 'bank_transfer', 'utm' => 'google', 'months' => 4, 'anon' => false],
            ['order' => 'E2E-DONA-0003', 'amount' => 150000, 'name' => 'Citra Lestari', 'email' => 'citra.l@example.com', 'phone' => '081234560003', 'method' => 'virtual_account', 'utm' => 'whatsapp', 'months' => 3, 'anon' => false],
            ['order' => 'E2E-DONA-0004', 'amount' => 50000, 'name' => 'Dewi Anggraini', 'email' => 'dewi.a@example.com', 'phone' => '081234560004', 'method' => 'ewallet', 'utm' => 'email', 'months' => 2, 'anon' => true],
            ['order' => 'E2E-DONA-0005', 'amount' => 1000000, 'name' => 'Eko Saputra', 'email' => 'eko.s@example.com', 'phone' => '081234560005', 'method' => 'bank_transfer', 'utm' => 'google', 'months' => 1, 'anon' => false],
            ['order' => 'E2E-DONA-0006', 'amount' => 75000, 'name' => 'Fitri Handayani', 'email' => 'fitri.h@example.com', 'phone' => '081234560006', 'method' => 'qris', 'utm' => 'instagram', 'months' => 0, 'anon' => false],
        ];

        foreach ($paid as $i => $data) {
            $createdAt = now()->subMonths($data['months'])->subDays($i);
            Donation::create([
                'tenant_id' => $tenant->id,
                'campaign_id' => $campaign->id,
                'campaign_link_id' => in_array($data['utm'], ['google', 'instagram'], true) ? $link->id : null,
                'order_id' => $data['order'],
                'donor_name' => $data['name'],
                'donor_email' => $data['email'],
                'donor_phone' => $data['phone'],
                'is_anonymous' => $data['anon'],
                'amount' => $data['amount'],
                'message' => 'Semoga berkah untuk anak yatim.',
                'payment_method' => $data['method'],
                'payment_status' => Donation::STATUS_PAID,
                'payment_gateway_ref' => 'DUMMY-' . strtoupper(Str::random(10)),
                'donation_type' => Donation::TYPE_ONE_TIME,
                'utm_source' => $data['utm'],
                'utm_medium' => 'social',
                'utm_campaign' => $campaign->slug,
                'paid_at' => $createdAt->copy()->addMinutes(3),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $otherStatuses = [
            ['order' => 'E2E-DONA-0101', 'status' => Donation::STATUS_PENDING, 'amount' => 200000, 'name' => 'Gilang Ramadhan', 'method' => 'virtual_account', 'utm' => 'whatsapp', 'days' => 1],
            ['order' => 'E2E-DONA-0102', 'status' => Donation::STATUS_FAILED, 'amount' => 120000, 'name' => 'Hendra Wijaya', 'method' => 'ewallet', 'utm' => 'email', 'days' => 6],
            ['order' => 'E2E-DONA-0103', 'status' => Donation::STATUS_EXPIRED, 'amount' => 80000, 'name' => 'Intan Permata', 'method' => 'bank_transfer', 'utm' => 'google', 'days' => 12],
            ['order' => 'E2E-DONA-0104', 'status' => Donation::STATUS_REFUNDED, 'amount' => 300000, 'name' => 'Joko Susilo', 'method' => 'credit_card', 'utm' => 'instagram', 'days' => 18],
        ];

        foreach ($otherStatuses as $data) {
            $createdAt = now()->subDays($data['days']);
            Donation::create([
                'tenant_id' => $tenant->id,
                'campaign_id' => $campaign->id,
                'order_id' => $data['order'],
                'donor_name' => $data['name'],
                'donor_email' => Str::slug($data['name']) . '@example.com',
                'donor_phone' => '081234569999',
                'amount' => $data['amount'],
                'payment_method' => $data['method'],
                'payment_status' => $data['status'],
                'donation_type' => Donation::TYPE_ONE_TIME,
                'utm_source' => $data['utm'],
                'utm_medium' => 'social',
                'utm_campaign' => $campaign->slug,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    protected function seedLeads(Tenant $tenant): void
    {
        $leads = [
            ['name' => 'Rina Marlina', 'email' => 'rina.m@example.com', 'phone' => '081298760001', 'subject' => 'Informasi beasiswa', 'message' => 'Bagaimana cara mendaftarkan anak saya untuk beasiswa?', 'type' => Lead::TYPE_WHATSAPP, 'status' => Lead::STATUS_NEW],
            ['name' => 'Sinta Dewi', 'email' => 'sinta.d@example.com', 'phone' => '081298760002', 'subject' => 'Kerja sama CSR', 'message' => 'Perusahaan kami ingin menjalin kerja sama donasi CSR.', 'type' => Lead::TYPE_EMAIL, 'status' => Lead::STATUS_NEW],
            ['name' => 'Tono Hartono', 'email' => 'tono.h@example.com', 'phone' => '081298760003', 'subject' => 'Menjadi relawan', 'message' => 'Saya ingin ikut menjadi relawan program pendidikan.', 'type' => Lead::TYPE_EMAIL, 'status' => Lead::STATUS_PROCESSING],
            ['name' => 'Umi Kalsum', 'email' => 'umi.k@example.com', 'phone' => '081298760004', 'subject' => 'Donasi rutin', 'message' => 'Apakah tersedia program donasi rutin bulanan?', 'type' => Lead::TYPE_WHATSAPP, 'status' => Lead::STATUS_CLOSED],
        ];

        foreach ($leads as $data) {
            Lead::create([
                'tenant_id' => $tenant->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'subject' => $data['subject'],
                'message' => $data['message'],
                'lead_type' => $data['type'],
                'status' => $data['status'],
            ]);
        }
    }

    protected function seedPageVisits(Tenant $tenant): void
    {
        $urls = [
            ['/campaign/beasiswa-batch-2026', 'google', 'desktop'],
            ['/campaign/beasiswa-batch-2026', 'instagram', 'mobile'],
            ['/campaign/beasiswa-batch-2026', 'whatsapp', 'mobile'],
            ['/programs', 'google', 'desktop'],
            ['/program/beasiswa-anak-yatim', 'direct', 'desktop'],
            ['/donasi/beasiswa-batch-2026', 'instagram', 'mobile'],
            ['/articles', 'google', 'tablet'],
            ['/article/serah-terima-beasiswa-2026', 'direct', 'desktop'],
            ['/kontak', 'direct', 'mobile'],
            ['/pengurus', 'google', 'desktop'],
        ];

        foreach ($urls as $i => $data) {
            PageVisit::create([
                'tenant_id' => $tenant->id,
                'page_url' => $data[0],
                'source' => $data[1],
                'device_type' => $data[2],
                'visited_at' => now()->subDays(rand(0, 20))->subHours(rand(0, 12)),
            ]);
        }
    }

    protected function seedGtmConfig(Tenant $tenant, User $author): void
    {
        GtmConfig::updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'gtm_id' => 'GTM-TEST123',
                'ga4_measurement_id' => 'G-TEST123456',
                'status' => GtmConfig::STATUS_ACTIVE,
                'updated_by' => $author->id,
            ],
        );
    }

    protected function seedSecondTenant(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['subdomain' => 'yayasan-nusantara'],
            [
                'name' => 'Yayasan Nusantara Sejahtera',
                'category' => 'pendidikan',
                'status' => 'active',
                'contact_email' => 'admin@yayasan-nusantara.test',
                'contact_phone' => '081299990001',
                'address' => 'Jakarta, Indonesia',
            ],
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@yayasan-nusantara.test'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Admin Nusantara',
                'phone' => '081299990002',
                'password' => Hash::make('password'),
            ],
        );

        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
        if (! $admin->hasRole('admin_yayasan')) {
            $admin->assignRole('admin_yayasan');
        }

        $program = Program::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'bimbel-gratis'],
            [
                'title' => 'Program Bimbel Gratis',
                'category' => 'Pendidikan',
                'status' => 'ongoing',
                'location' => 'Jakarta',
                'content' => '<p>Bimbel gratis untuk siswa kurang mampu di Jakarta.</p>',
                'meta_description' => 'Bimbel gratis untuk siswa kurang mampu.',
                'author_id' => $admin->id,
                'published_at' => now()->subMonths(2),
            ],
        );

        $campaign = Campaign::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'bimbel-gratis-2026'],
            [
                'program_id' => $program->id,
                'title' => 'Bimbel Gratis 2026',
                'story' => '<p>Dukung 50 siswa mengikuti bimbel gratis selama setahun.</p>',
                'target_amount' => 50000000,
                'collected_amount' => 12000000,
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->addMonths(6)->toDateString(),
                'status' => Campaign::STATUS_ACTIVE,
                'author_id' => $admin->id,
            ],
        );

        $donations = [
            ['order' => 'NUS-DONA-0001', 'amount' => 500000, 'name' => 'Nadia Rahma', 'months' => 2],
            ['order' => 'NUS-DONA-0002', 'amount' => 7000000, 'name' => 'Omar Syah', 'months' => 1],
        ];

        foreach ($donations as $data) {
            $createdAt = now()->subMonths($data['months'])->subDays(5);
            Donation::create([
                'tenant_id' => $tenant->id,
                'campaign_id' => $campaign->id,
                'order_id' => $data['order'],
                'donor_name' => $data['name'],
                'donor_email' => Str::slug($data['name']) . '@example.com',
                'donor_phone' => '081299990003',
                'amount' => $data['amount'],
                'payment_method' => 'bank_transfer',
                'payment_status' => Donation::STATUS_PAID,
                'donation_type' => Donation::TYPE_ONE_TIME,
                'utm_source' => 'google',
                'utm_medium' => 'organic',
                'utm_campaign' => $campaign->slug,
                'paid_at' => $createdAt->copy()->addMinutes(5),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
