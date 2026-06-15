<?php

namespace Database\Seeders;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $u = fn(string $email) => User::where('email', $email)->first();

        $sarah  = $u('sarah.chen@botjourney.ai');
        $james  = $u('james.wilson@botjourney.ai');
        $emma   = $u('emma.rodriguez@botjourney.ai');
        $noah   = $u('noah.martinez@botjourney.ai');
        $ava    = $u('ava.johnson@botjourney.ai');
        $liam   = $u('liam.thompson@botjourney.ai');
        $olivia = $u('olivia.davis@botjourney.ai');
        $mia    = $u('mia.taylor@botjourney.ai');

        $projects = Project::all()->keyBy('name');
        $clients  = Client::with('user')->get();
        $tasks    = Task::all();

        $logs = [];

        // Project activity
        foreach ($projects as $name => $project) {
            $logs[] = [
                'user_id'     => $sarah->id ?? null,
                'action'      => ActivityAction::Created->value,
                'module'      => 'projects',
                'entity_id'   => $project->id,
                'description' => "Project '{$name}' was created.",
                'old_values'  => null,
                'new_values'  => json_encode(['name' => $name, 'status' => $project->status]),
                'ip_address'  => '10.0.1.10',
                'created_at'  => now()->subDays(rand(30, 120)),
            ];
        }

        // Status change logs
        $chatbot = $projects['AI Chatbot Platform'] ?? null;
        if ($chatbot) {
            $logs[] = [
                'user_id'     => $sarah->id ?? null,
                'action'      => ActivityAction::Updated->value,
                'module'      => 'projects',
                'entity_id'   => $chatbot->id,
                'description' => "Project status changed from 'planning' to 'active'.",
                'old_values'  => json_encode(['status' => 'planning']),
                'new_values'  => json_encode(['status' => 'active']),
                'ip_address'  => '10.0.1.10',
                'created_at'  => now()->subDays(85),
            ];
        }

        // Client views
        foreach ($clients->take(3) as $client) {
            $logs[] = [
                'user_id'     => $sarah->id ?? null,
                'action'      => ActivityAction::Viewed->value,
                'module'      => 'clients',
                'entity_id'   => $client->id,
                'description' => "Client '{$client->company_name}' profile viewed.",
                'old_values'  => null,
                'new_values'  => null,
                'ip_address'  => '10.0.1.10',
                'created_at'  => now()->subDays(rand(1, 10)),
            ];
        }

        // Task activity
        foreach ($tasks->take(10) as $task) {
            $actor = collect([$emma, $james, $noah, $ava, $liam, $olivia])->filter()->random();
            $logs[] = [
                'user_id'     => $actor->id,
                'action'      => ActivityAction::Created->value,
                'module'      => 'tasks',
                'entity_id'   => $task->id,
                'description' => "Task '{$task->title}' was created.",
                'old_values'  => null,
                'new_values'  => json_encode(['title' => $task->title, 'status' => $task->status]),
                'ip_address'  => '10.0.1.' . rand(10, 50),
                'created_at'  => $task->created_at ?? now()->subDays(rand(1, 60)),
            ];
        }

        foreach ($tasks->where('status', 'done') as $task) {
            $logs[] = [
                'user_id'     => $task->updated_by ?? ($emma->id ?? null),
                'action'      => ActivityAction::Updated->value,
                'module'      => 'tasks',
                'entity_id'   => $task->id,
                'description' => "Task '{$task->title}' marked as done.",
                'old_values'  => json_encode(['status' => 'in_progress']),
                'new_values'  => json_encode(['status' => 'done']),
                'ip_address'  => '10.0.1.' . rand(10, 50),
                'created_at'  => $task->completed_at ?? now()->subDays(rand(1, 30)),
            ];
        }

        // Employee viewed their profile
        if ($mia) {
            $logs[] = [
                'user_id'     => $mia->id,
                'action'      => ActivityAction::Viewed->value,
                'module'      => 'employees',
                'entity_id'   => $mia->employee?->id,
                'description' => 'Employee viewed their own profile.',
                'old_values'  => null,
                'new_values'  => null,
                'ip_address'  => '10.0.1.42',
                'created_at'  => now()->subHours(2),
            ];
        }

        // Expense created
        $logs[] = [
            'user_id'     => $noah->id ?? null,
            'action'      => ActivityAction::Created->value,
            'module'      => 'expenses',
            'entity_id'   => 1,
            'description' => 'Expense submitted for AWS HIPAA Compliance Module.',
            'old_values'  => null,
            'new_values'  => json_encode(['amount' => 850.00, 'status' => 'pending']),
            'ip_address'  => '10.0.1.22',
            'created_at'  => now()->subDays(14),
        ];

        // Expense approved
        $logs[] = [
            'user_id'     => $u('ethan.lee@botjourney.ai')?->id,
            'action'      => ActivityAction::Updated->value,
            'module'      => 'expenses',
            'entity_id'   => 1,
            'description' => 'Expense approved by Finance Manager.',
            'old_values'  => json_encode(['status' => 'pending']),
            'new_values'  => json_encode(['status' => 'approved']),
            'ip_address'  => '10.0.1.30',
            'created_at'  => now()->subDays(12),
        ];

        foreach ($logs as $log) {
            if (!$log['user_id']) continue;
            ActivityLog::create($log);
        }

        $this->command->info('Activity logs seeded: ' . count($logs));
    }
}
