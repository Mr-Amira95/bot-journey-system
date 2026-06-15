<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'email'           => 'marcus.reed@nexusai.com',
                'company_name'    => 'NexusAI Corp',
                'company_website' => 'https://nexusai.com',
                'industry'        => 'Artificial Intelligence',
                'notes'           => 'Enterprise AI platform company. Key strategic account with multiple ongoing projects. Primary contact: Marcus Reed (CTO).',
            ],
            [
                'email'           => 'priya.sharma@healthflow.com',
                'company_name'    => 'HealthFlow Solutions',
                'company_website' => 'https://healthflow.io',
                'industry'        => 'Healthcare Technology',
                'notes'           => 'Digital health analytics company focused on patient data insights and clinical decision support. HIPAA compliance is a key requirement.',
            ],
            [
                'email'           => 'derek.walsh@retailedge.com',
                'company_name'    => 'RetailEdge Inc',
                'company_website' => 'https://retailedge.com',
                'industry'        => 'Retail Technology',
                'notes'           => 'Omnichannel retail management solution provider. Operating in 12 countries with 500+ retail clients.',
            ],
            [
                'email'           => 'yuki.tanaka@smartlogistics.com',
                'company_name'    => 'SmartLogistics Ltd',
                'company_website' => 'https://smartlogistics.jp',
                'industry'        => 'Logistics & Supply Chain',
                'notes'           => 'AI-driven route optimization for global logistics networks. Project successfully delivered in December 2025.',
            ],
            [
                'email'           => 'fatima.alhassan@futurelearn.edu',
                'company_name'    => 'FutureLearn Education',
                'company_website' => 'https://futurelearn.edu',
                'industry'        => 'EdTech',
                'notes'           => 'Online learning platform with 2M+ learners worldwide. Focus on AI-powered personalized education experiences.',
            ],
        ];

        foreach ($clients as $data) {
            $user = User::where('email', $data['email'])->first();
            if (!$user) continue;

            Client::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name'    => $data['company_name'],
                    'company_website' => $data['company_website'],
                    'industry'        => $data['industry'],
                    'notes'           => $data['notes'],
                ]
            );
        }

        $this->command->info('Clients seeded: ' . count($clients));
    }
}
