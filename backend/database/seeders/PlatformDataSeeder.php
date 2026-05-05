<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wave;
use App\Models\Circle;
use App\Models\GatedRoom;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\WaveVisibility;
use App\Enums\WaveStatus;
use App\Enums\GatedRoomStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PlatformDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create specialized creators
        $creators = [
            [
                'name' => 'Sarah Johnson',
                'username' => 'sarah_creates',
                'email' => 'sarah@zumi.app',
                'bio' => 'Visual Storyteller | Motion Designer | Explorer 🚀',
                'role' => UserRole::Pro,
                'interests' => ['Motion Design', 'Visual Arts', 'Tech'],
            ],
            [
                'name' => 'Marcus Chen',
                'username' => 'marcus_vibe',
                'email' => 'marcus@zumi.app',
                'bio' => 'Urban vibes and city lights. 🏙️ Cinematic waves only.',
                'role' => UserRole::Pro,
                'interests' => ['Cinematography', 'Photography', 'Travel'],
            ],
            [
                'name' => 'Elena Rodriguez',
                'username' => 'elena_dev',
                'email' => 'elena@zumi.app',
                'bio' => 'Full-stack developer building the future of SocialFi. 💻',
                'role' => UserRole::Studio,
                'interests' => ['Web3', 'SocialFi', 'Coding'],
            ],
            [
                'name' => 'Aiden Storm',
                'username' => 'aiden_storm',
                'email' => 'aiden@zumi.app',
                'bio' => 'Gaming, Gear, and Gadgets. 🎮 Let\'s drop some waves.',
                'role' => UserRole::Pro,
                'interests' => ['Gaming', 'Tech', 'Gear'],
            ],
        ];

        $createdCreators = [];
        foreach ($creators as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password' => Hash::make('password'),
                    'status' => UserStatus::Active,
                    'onboarding_completed' => true,
                    'verified_at' => now(),
                    'drops_balance' => rand(5000, 50000),
                ])
            );
            $createdCreators[] = $user;
        }

        // Add Jane Creator from DevAuthSeeder to the mix
        $jane = User::where('email', 'creator@zumi.app')->first();
        if ($jane) {
            $createdCreators[] = $jane;
        }

        // 2. Create some regular users
        $users = User::factory(10)->create([
            'role' => UserRole::User,
            'onboarding_completed' => true,
            'drops_balance' => 1000,
        ]);

        // 3. Establish follow relationships
        foreach ($users as $u) {
            // Everyone follows at least 2 creators
            $u->following()->sync(
                collect($createdCreators)->random(rand(2, 4))->pluck('id')
            );
        }

        foreach ($createdCreators as $creator) {
            // Creators follow other creators too
            $creator->following()->sync(
                collect($createdCreators)->filter(fn($c) => $c->id !== $creator->id)->random(rand(1, 3))->pluck('id')
            );
        }

        // 4. Create Waves (Videos) for creators
        foreach ($createdCreators as $creator) {
            for ($i = 1; $i <= 3; $i++) {
                Wave::create([
                    'user_id' => $creator->id,
                    'title' => "Wave #{$i} from @{$creator->username}",
                    'description' => "Check out my latest creation! This is wave number {$i}. Hope you enjoy the vibes. #Zumi #Wave",
                    'visibility' => WaveVisibility::Public,
                    'likes_count' => rand(100, 5000),
                    'comments_count' => rand(10, 200),
                    'shares_count' => rand(5, 50),
                    'views_count' => rand(1000, 20000),
                    'thumbnail_url' => "https://picsum.photos/seed/" . Str::random(10) . "/1080/1920",
                    'status' => WaveStatus::Ready,
                ]);
            }
        }

        // 5. Create Circles
        foreach ($createdCreators as $creator) {
            $name = "{$creator->name}'s Circle";
            $slug = Str::slug($name);
            Circle::updateOrCreate(
                ['slug' => $slug],
                [
                    'owner_id' => $creator->id,
                    'name' => $name,
                    'description' => "Welcome to my private community where we share exclusive waves and drops.",
                    'type' => 'gated',
                    'status' => 'active',
                    'members_count' => rand(50, 500),
                ]
            );
        }

        // 6. Create Gated Rooms (Live)
        foreach (collect($createdCreators)->take(2) as $creator) {
            $title = "Tech Alpha Session with @{$creator->username}";
            GatedRoom::updateOrCreate(
                [
                    'user_id' => $creator->id,
                    'title' => $title
                ],
                [
                    'description' => "Join me live for an exclusive deep dive into the latest tech trends and drops.",
                    'entry_fee_drops' => 500,
                    'status' => GatedRoomStatus::Live,
                    'started_at' => now(),
                ]
            );
        }

        $this->command->info('Platform data seeded successfully with realistic creators, waves, and gated rooms.');
    }
}
