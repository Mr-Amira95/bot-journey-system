<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Whiteboard;
use App\Models\WhiteboardShare;
use Illuminate\Database\Seeder;

class WhiteboardSeeder extends Seeder
{
    public function run(): void
    {
        $u = fn(string $email) => User::where('email', $email)->first();

        $sarah  = $u('sarah.chen@botjourney.ai');
        $james  = $u('james.wilson@botjourney.ai');
        $emma   = $u('emma.rodriguez@botjourney.ai');
        $ava    = $u('ava.johnson@botjourney.ai');
        $olivia = $u('olivia.davis@botjourney.ai');
        $noah   = $u('noah.martinez@botjourney.ai');
        $alex   = $u('alexander.wright@botjourney.ai');

        $whiteboards = [
            [
                'user'   => $sarah,
                'title'  => 'AI Chatbot Platform — System Architecture',
                'path'   => 'whiteboards/chatbot-system-architecture.json',
                'shares' => [
                    [$james,  $sarah],
                    [$emma,   $sarah],
                    [$noah,   $sarah],
                    [$ava,    $sarah],
                ],
            ],
            [
                'user'   => $james,
                'title'  => 'Product Roadmap Q3–Q4 2026',
                'path'   => 'whiteboards/product-roadmap-q3-q4-2026.json',
                'shares' => [
                    [$sarah,  $james],
                    [$olivia, $james],
                ],
            ],
            [
                'user'   => $ava,
                'title'  => 'Healthcare Analytics — Data Flow Diagram',
                'path'   => 'whiteboards/healthcare-data-flow.json',
                'shares' => [
                    [$emma,  $ava],
                    [$noah,  $ava],
                    [$alex,  $ava],
                    [$james, $ava],
                ],
            ],
            [
                'user'   => $olivia,
                'title'  => 'E-Learning Redesign — User Journey Maps',
                'path'   => 'whiteboards/elearning-user-journeys.json',
                'shares' => [
                    [$james, $olivia],
                ],
            ],
            [
                'user'   => $alex,
                'title'  => 'Route Optimisation — Algorithm Comparison',
                'path'   => 'whiteboards/logistics-algorithm-comparison.json',
                'shares' => [
                    [$ava,   $alex],
                    [$sarah, $alex],
                ],
            ],
            [
                'user'   => $sarah,
                'title'  => 'BotJourney Team OKRs — Q3 2026',
                'path'   => 'whiteboards/team-okrs-q3-2026.json',
                'shares' => [
                    [$james, $sarah],
                    [$emma,  $sarah],
                    [$ava,   $sarah],
                    [$noah,  $sarah],
                ],
            ],
            [
                'user'   => $emma,
                'title'  => 'API Integration Architecture — CRM Connectors',
                'path'   => 'whiteboards/crm-api-architecture.json',
                'shares' => [
                    [$noah,  $emma],
                    [$james, $emma],
                ],
            ],
        ];

        foreach ($whiteboards as $data) {
            if (!$data['user']) continue;

            $whiteboard = Whiteboard::create([
                'user_id'   => $data['user']->id,
                'title'     => $data['title'],
                'file_path' => $data['path'],
            ]);

            foreach ($data['shares'] as [$sharedWith, $sharedBy]) {
                if (!$sharedWith || !$sharedBy) continue;
                WhiteboardShare::firstOrCreate(
                    [
                        'whiteboard_id'      => $whiteboard->id,
                        'shared_with_user_id'=> $sharedWith->id,
                    ],
                    [
                        'shared_by_user_id' => $sharedBy->id,
                        'created_at'        => now()->subDays(rand(1, 20)),
                    ]
                );
            }
        }

        $this->command->info('Whiteboards seeded: ' . count($whiteboards));
    }
}
