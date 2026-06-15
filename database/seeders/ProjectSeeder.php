<?php

namespace Database\Seeders;

use App\Enums\ProjectMemberRole;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectStatusLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $u = fn(string $email) => User::where('email', $email)->first();
        $c = fn(string $email) => Client::whereHas('user', fn($q) => $q->where('email', $email))->first();

        $sarah    = $u('sarah.chen@botjourney.ai');
        $james    = $u('james.wilson@botjourney.ai');
        $emma     = $u('emma.rodriguez@botjourney.ai');
        $liam     = $u('liam.thompson@botjourney.ai');
        $olivia   = $u('olivia.davis@botjourney.ai');
        $noah     = $u('noah.martinez@botjourney.ai');
        $ava      = $u('ava.johnson@botjourney.ai');
        $william  = $u('william.brown@botjourney.ai');
        $mia      = $u('mia.taylor@botjourney.ai');
        $alex     = $u('alexander.wright@botjourney.ai');
        $amelia   = $u('amelia.lewis@botjourney.ai');

        $nexusai      = $c('marcus.reed@nexusai.com');
        $healthflow   = $c('priya.sharma@healthflow.com');
        $retailedge   = $c('derek.walsh@retailedge.com');
        $smartlog     = $c('yuki.tanaka@smartlogistics.com');
        $futurelearn  = $c('fatima.alhassan@futurelearn.edu');

        $projects = [
            [
                'client'       => $nexusai,
                'name'         => 'AI Chatbot Platform',
                'description'  => 'Enterprise-grade conversational AI platform with multi-channel support, intent recognition, and seamless CRM integration. The system handles 10,000+ concurrent sessions and supports 15+ languages.',
                'status'       => ProjectStatus::Active,
                'priority'     => ProjectPriority::High,
                'start_date'   => '2026-01-15',
                'due_date'     => '2026-08-31',
                'budget'       => 250000.00,
                'created_by'   => $sarah,
                'completed_at' => null,
                'members' => [
                    [$sarah,   ProjectMemberRole::Owner->value,   90],
                    [$james,   ProjectMemberRole::Manager->value, 88],
                    [$emma,    ProjectMemberRole::Member->value,  85],
                    [$noah,    ProjectMemberRole::Member->value,  85],
                    [$ava,     ProjectMemberRole::Member->value,  80],
                    [$olivia,  ProjectMemberRole::Member->value,  75],
                    [$liam,    ProjectMemberRole::Member->value,  70],
                ],
            ],
            [
                'client'       => $healthflow,
                'name'         => 'Healthcare Analytics Dashboard',
                'description'  => 'HIPAA-compliant real-time patient analytics platform with predictive health metrics, clinical decision support, and interactive data visualisations for hospital administrators.',
                'status'       => ProjectStatus::Planning,
                'priority'     => ProjectPriority::High,
                'start_date'   => '2026-03-01',
                'due_date'     => '2026-10-31',
                'budget'       => 180000.00,
                'created_by'   => $james,
                'completed_at' => null,
                'members' => [
                    [$james,   ProjectMemberRole::Owner->value,   100],
                    [$emma,    ProjectMemberRole::Manager->value, 95],
                    [$ava,     ProjectMemberRole::Member->value,  90],
                    [$alex,    ProjectMemberRole::Member->value,  88],
                    [$liam,    ProjectMemberRole::Member->value,  85],
                    [$william, ProjectMemberRole::Member->value,  80],
                ],
            ],
            [
                'client'       => $retailedge,
                'name'         => 'Retail Inventory Management System',
                'description'  => 'AI-powered inventory management system with demand forecasting, automated reordering, multi-warehouse support, and real-time supplier integration for 500+ retail locations.',
                'status'       => ProjectStatus::Active,
                'priority'     => ProjectPriority::Medium,
                'start_date'   => '2025-11-01',
                'due_date'     => '2026-05-31',
                'budget'       => 120000.00,
                'created_by'   => $sarah,
                'completed_at' => null,
                'members' => [
                    [$sarah,   ProjectMemberRole::Owner->value,   150],
                    [$noah,    ProjectMemberRole::Manager->value, 145],
                    [$liam,    ProjectMemberRole::Member->value,  140],
                    [$william, ProjectMemberRole::Member->value,  135],
                    [$amelia,  ProjectMemberRole::Member->value,  130],
                ],
            ],
            [
                'client'       => $smartlog,
                'name'         => 'Logistics Route Optimizer',
                'description'  => 'Machine learning-based route optimization engine that reduced delivery times by 23% and fuel costs by 18%. Processes 50,000+ daily route calculations across 30 countries.',
                'status'       => ProjectStatus::Completed,
                'priority'     => ProjectPriority::Medium,
                'start_date'   => '2025-05-01',
                'due_date'     => '2025-12-31',
                'budget'       => 90000.00,
                'created_by'   => $sarah,
                'completed_at' => '2025-12-15 17:00:00',
                'members' => [
                    [$sarah,   ProjectMemberRole::Owner->value,   380],
                    [$ava,     ProjectMemberRole::Manager->value, 375],
                    [$alex,    ProjectMemberRole::Member->value,  370],
                    [$noah,    ProjectMemberRole::Member->value,  365],
                    [$william, ProjectMemberRole::Member->value,  360],
                ],
            ],
            [
                'client'       => $futurelearn,
                'name'         => 'E-Learning Platform Redesign',
                'description'  => 'Complete redesign of the learning management system with AI-driven course personalisation, interactive assessments, gamification, and mobile-first responsive interface for 2M+ learners.',
                'status'       => ProjectStatus::Active,
                'priority'     => ProjectPriority::Medium,
                'start_date'   => '2026-02-01',
                'due_date'     => '2026-09-30',
                'budget'       => 150000.00,
                'created_by'   => $james,
                'completed_at' => null,
                'members' => [
                    [$james,   ProjectMemberRole::Owner->value,   130],
                    [$olivia,  ProjectMemberRole::Manager->value, 125],
                    [$liam,    ProjectMemberRole::Member->value,  120],
                    [$amelia,  ProjectMemberRole::Member->value,  115],
                    [$ava,     ProjectMemberRole::Member->value,  110],
                ],
            ],
            [
                'client'       => $nexusai,
                'name'         => 'Customer Support Automation',
                'description'  => 'Automated tier-1 customer support system using NLP and sentiment analysis to autonomously handle 70% of incoming tickets, reducing average resolution time from 4 hours to 15 minutes.',
                'status'       => ProjectStatus::OnHold,
                'priority'     => ProjectPriority::Low,
                'start_date'   => '2026-04-01',
                'due_date'     => '2026-12-31',
                'budget'       => 75000.00,
                'created_by'   => $sarah,
                'completed_at' => null,
                'members' => [
                    [$sarah,   ProjectMemberRole::Owner->value,  70],
                    [$mia,     ProjectMemberRole::Manager->value, 68],
                    [$ava,     ProjectMemberRole::Member->value,  65],
                    [$noah,    ProjectMemberRole::Member->value,  65],
                ],
            ],
        ];

        foreach ($projects as $data) {
            if (!$data['client'] || !$data['created_by']) continue;

            $project = Project::firstOrCreate(
                ['name' => $data['name'], 'client_id' => $data['client']->id],
                [
                    'client_id'    => $data['client']->id,
                    'description'  => $data['description'],
                    'status'       => $data['status'],
                    'priority'     => $data['priority'],
                    'start_date'   => $data['start_date'],
                    'due_date'     => $data['due_date'],
                    'budget'       => $data['budget'],
                    'created_by'   => $data['created_by']->id,
                    'completed_at' => $data['completed_at'],
                ]
            );

            foreach ($data['members'] as [$memberUser, $memberRole, $daysAgo]) {
                if (!$memberUser) continue;
                ProjectMember::firstOrCreate(
                    ['project_id' => $project->id, 'user_id' => $memberUser->id],
                    [
                        'role_in_project' => $memberRole,
                        'joined_at'       => now()->subDays($daysAgo),
                    ]
                );
            }

            ProjectStatusLog::create([
                'project_id' => $project->id,
                'log_key'    => 'project_created',
                'changed_by' => $data['created_by']->id,
                'log_at'     => now()->subDays(end($data['members'])[2] ?? 30),
            ]);

            if ($data['status'] !== ProjectStatus::Planning) {
                ProjectStatusLog::create([
                    'project_id' => $project->id,
                    'log_key'    => 'status_changed_to_' . $data['status']->value,
                    'changed_by' => $data['created_by']->id,
                    'log_at'     => now()->subDays(30),
                ]);
            }
        }

        $this->command->info('Projects seeded: ' . count($projects));
    }
}
