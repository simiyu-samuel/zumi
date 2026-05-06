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
        $videos = [
            'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
            'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
            'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4',
        ];

        $hashtagSets = [
            '#Zumi #Wave #Creative #DigitalArt',
            '#SkillDrops #Tutorial #LearnWithMe #ZumiFlow',
            '#WaveChallenge #Trending #MotionDesign #Cinematic',
        ];

        $allWaves = [];
        foreach ($createdCreators as $creator) {
            for ($i = 0; $i < 3; $i++) {
                $wave = Wave::create([
                    'user_id' => $creator->id,
                    'title' => "Wave #".($i+1)." from @{$creator->username}",
                    'description' => "Check out my latest creation! This is wave number ".($i+1).". Hope you enjoy the vibes. " . $hashtagSets[$i],
                    'visibility' => WaveVisibility::Public,
                    'likes_count' => rand(100, 5000),
                    'comments_count' => rand(10, 200),
                    'shares_count' => rand(5, 50),
                    'views_count' => rand(1000, 20000),
                    'thumbnail_url' => $videos[$i],
                    'status' => WaveStatus::Ready,
                ]);
                $allWaves[] = $wave;
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

        // 6. Create Gated Rooms (Live + Scheduled)
        $roomTitles = [
            'Tech Alpha Session',
            'Creative Deep Dive',
            'Wave Mastery Workshop',
        ];
        foreach (collect($createdCreators)->take(2) as $idx => $creator) {
            GatedRoom::updateOrCreate(
                [
                    'user_id' => $creator->id,
                    'title' => "{$roomTitles[$idx]} with @{$creator->username}",
                ],
                [
                    'description' => "Join me live for an exclusive deep dive into the latest tech trends and drops.",
                    'entry_fee_drops' => ($idx + 1) * 250,
                    'status' => GatedRoomStatus::Live,
                    'started_at' => now(),
                ]
            );
        }

        // Also create a scheduled room
        $scheduledCreator = $createdCreators[2] ?? $createdCreators[0];
        GatedRoom::updateOrCreate(
            [
                'user_id' => $scheduledCreator->id,
                'title' => "Upcoming: Motion Design Secrets with @{$scheduledCreator->username}",
            ],
            [
                'description' => "Learn my top techniques for cinematic motion design. Limited spots!",
                'entry_fee_drops' => 150,
                'status' => GatedRoomStatus::Scheduled,
                'scheduled_at' => now()->addDays(2),
            ]
        );

        // 7. Create Challenges
        $challengeData = [
            [
                'title' => 'The Digital Deep Sea',
                'description' => 'Show us your best interpretation of bioluminescent life using any digital medium. Highest engagement wins!',
                'prize_pool' => 5000,
                'ends_at' => now()->addDays(5),
            ],
            [
                'title' => 'Urban Flow Challenge',
                'description' => 'Capture the rhythm of city life through a cinematic wave. Creativity, composition, and vibe matter most.',
                'prize_pool' => 3000,
                'ends_at' => now()->addDays(7),
            ],
            [
                'title' => 'Retro Future Vision',
                'description' => 'Blend retro aesthetics with futuristic concepts. Think 80s neon meets 2050 tech.',
                'prize_pool' => 8000,
                'ends_at' => now()->addDays(10),
            ],
        ];

        foreach ($challengeData as $idx => $cd) {
            $creator = $createdCreators[$idx % count($createdCreators)];
            \App\Models\Challenge::updateOrCreate(
                ['title' => $cd['title']],
                array_merge($cd, [
                    'user_id' => $creator->id,
                    'type' => \App\Enums\ChallengeType::Wave,
                    'status' => \App\Enums\ChallengeStatus::Active,
                ])
            );
        }

        // 8. Create Skill Drops
        $skillDropData = [
            [
                'title' => 'Mastering Node-Based Art',
                'description' => 'Learn to create stunning procedural art using node-based workflows. From basics to advanced techniques.',
                'price_drops' => 850,
                'sales_count' => 124,
            ],
            [
                'title' => 'Cinematic Color Grading 101',
                'description' => 'Transform your waves with professional color grading. Industry-standard techniques revealed.',
                'price_drops' => 500,
                'sales_count' => 89,
            ],
            [
                'title' => 'Sound Design for Creators',
                'description' => 'Craft immersive audio landscapes that elevate your content. No musical background required.',
                'price_drops' => 1200,
                'sales_count' => 67,
            ],
            [
                'title' => 'Motion Typography Essentials',
                'description' => 'Make your text dance! Learn kinetic typography techniques that captivate viewers.',
                'price_drops' => 650,
                'sales_count' => 203,
            ],
            [
                'title' => 'Building Your Creator Brand',
                'description' => 'From zero to recognized creator. Strategy, consistency, and growth hacking for the Zumi ecosystem.',
                'price_drops' => 300,
                'sales_count' => 312,
            ],
        ];

        foreach ($skillDropData as $idx => $sd) {
            $creator = $createdCreators[$idx % count($createdCreators)];
            \App\Models\SkillDrop::updateOrCreate(
                ['title' => $sd['title']],
                array_merge($sd, [
                    'user_id' => $creator->id,
                    'slug' => Str::slug($sd['title']),
                    'rating_avg' => rand(3, 5),
                ])
            );
        }

        // 9. Create Comments on waves
        $commentTexts = [
            'This is absolutely fire! 🔥 The transitions are so smooth.',
            'Love the vibe on this one! Dropping some Drops your way 💎',
            'How do you even create stuff like this? Tutorial when?',
            'This should be in the #WaveChallenge for sure 🏆',
            'The color grading on this is next level. What tools do you use?',
            'Been following you since day one, never disappoints!',
            'This is why I love Zumi. Content like this. Pure art. 🎨',
            'Can we collab? DM me! I have some ideas 🤝',
        ];

        $allUsers = array_merge($createdCreators, $users->all());
        foreach (collect($allWaves)->take(8) as $waveIdx => $wave) {
            $numComments = rand(2, 4);
            for ($c = 0; $c < $numComments; $c++) {
                $commenter = $allUsers[array_rand($allUsers)];
                \App\Models\Comment::create([
                    'wave_id' => $wave->id,
                    'user_id' => $commenter->id,
                    'content' => $commentTexts[($waveIdx + $c) % count($commentTexts)],
                    'likes_count' => rand(0, 50),
                ]);
            }
        }

        // 10. Create Notifications for dev user
        $devUser = User::where('email', 'user@zumi.app')->first();
        if ($devUser) {
            $notificationData = [
                [
                    'type' => 'drops_received',
                    'data' => ['type' => 'drops_received', 'message' => 'Sarah Johnson sent you 500 Drops!', 'sender_name' => 'Sarah Johnson', 'amount' => 500],
                ],
                [
                    'type' => 'new_follower',
                    'data' => ['type' => 'new_follower', 'message' => 'Marcus Chen started following you', 'sender_name' => 'Marcus Chen'],
                ],
                [
                    'type' => 'new_comment',
                    'data' => ['type' => 'new_comment', 'message' => 'Elena Rodriguez commented on your wave', 'commenter_name' => 'Elena Rodriguez', 'commenter_username' => 'elena_dev', 'comment_excerpt' => 'This is absolutely fire! 🔥'],
                ],
                [
                    'type' => 'wave_liked',
                    'data' => ['type' => 'wave_liked', 'message' => 'Aiden Storm and 12 others liked your wave'],
                ],
                [
                    'type' => 'mention',
                    'data' => ['type' => 'mention', 'message' => 'Sarah Johnson mentioned you in a wave', 'creator_name' => 'Sarah Johnson'],
                ],
            ];

            foreach ($notificationData as $nd) {
                $devUser->notifications()->create([
                    'id' => Str::uuid(),
                    'type' => 'App\\Notifications\\' . Str::studly($nd['type']),
                    'data' => $nd['data'],
                    'created_at' => now()->subHours(rand(1, 48)),
                ]);
            }
        }

        $this->command->info('Platform data seeded successfully with creators, waves, circles, rooms, challenges, skill drops, comments, and notifications.');
    }
}
