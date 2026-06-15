<?php

namespace Database\Seeders;

use App\Enums\TaskAssigneeRole;
use App\Enums\TaskLogAction;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAssignee;
use App\Models\TaskComment;
use App\Models\TaskDependency;
use App\Models\TaskLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $u = fn(string $email) => User::where('email', $email)->first();
        $p = fn(string $name)  => Project::where('name', $name)->first();

        $sarah  = $u('sarah.chen@botjourney.ai');
        $james  = $u('james.wilson@botjourney.ai');
        $emma   = $u('emma.rodriguez@botjourney.ai');
        $liam   = $u('liam.thompson@botjourney.ai');
        $olivia = $u('olivia.davis@botjourney.ai');
        $noah   = $u('noah.martinez@botjourney.ai');
        $ava    = $u('ava.johnson@botjourney.ai');
        $william = $u('william.brown@botjourney.ai');
        $mia    = $u('mia.taylor@botjourney.ai');
        $alex   = $u('alexander.wright@botjourney.ai');
        $amelia = $u('amelia.lewis@botjourney.ai');

        $chatbot    = $p('AI Chatbot Platform');
        $health     = $p('Healthcare Analytics Dashboard');
        $retail     = $p('Retail Inventory Management System');
        $logistics  = $p('Logistics Route Optimizer');
        $elearning  = $p('E-Learning Platform Redesign');
        $support    = $p('Customer Support Automation');

        $tasksData = [
            // === AI Chatbot Platform ===
            [
                'project'     => $chatbot,
                'title'       => 'Design conversation flow architecture',
                'description' => 'Define the complete conversation flow architecture for the multi-channel chatbot, including fallback strategies, escalation paths to human agents, and multi-turn context management.',
                'status'      => TaskStatus::Done,
                'priority'    => TaskPriority::High,
                'start_date'  => '2026-01-20',
                'due_date'    => '2026-02-10',
                'completed_at'=> '2026-02-08 15:30:00',
                'estimated_hours' => 32.00,
                'created_by'  => $sarah,
                'updated_by'  => $james,
                'assignees'   => [[$james, TaskAssigneeRole::Assignee->value], [$sarah, TaskAssigneeRole::Reviewer->value]],
                'comments'    => [
                    [$james, 'Completed the main flow diagrams. Using a state machine approach with 47 distinct conversation states.', null],
                    [$sarah, 'Excellent work. The fallback strategy covers all edge cases we discussed. Approved.', null],
                ],
            ],
            [
                'project'     => $chatbot,
                'title'       => 'Set up NLP pipeline with intent recognition',
                'description' => 'Configure the NLP pipeline using transformer-based models for intent classification and entity extraction. Must support 15 languages with >92% accuracy on the test set.',
                'status'      => TaskStatus::InProgress,
                'priority'    => TaskPriority::High,
                'start_date'  => '2026-02-15',
                'due_date'    => '2026-04-15',
                'completed_at'=> null,
                'estimated_hours' => 80.00,
                'created_by'  => $sarah,
                'updated_by'  => $ava,
                'assignees'   => [[$ava, TaskAssigneeRole::Assignee->value], [$alex, TaskAssigneeRole::Assignee->value], [$emma, TaskAssigneeRole::Reviewer->value]],
                'comments'    => [
                    [$ava, 'Training data for 10 languages is ready. Starting model fine-tuning this week.', null],
                    [$alex, 'Entity extraction accuracy is at 94% on English. Working on improving Arabic and Chinese.', null],
                    [$emma, 'Please document the model selection rationale before we finalize the architecture.', null],
                ],
            ],
            [
                'project'     => $chatbot,
                'title'       => 'Build REST API integrations for CRM',
                'description' => 'Implement REST API integrations with Salesforce, HubSpot, and Zendesk. Each integration must support bidirectional sync with webhook support and OAuth 2.0 authentication.',
                'status'      => TaskStatus::InProgress,
                'priority'    => TaskPriority::Medium,
                'start_date'  => '2026-03-01',
                'due_date'    => '2026-05-31',
                'completed_at'=> null,
                'estimated_hours' => 60.00,
                'created_by'  => $emma,
                'updated_by'  => $noah,
                'assignees'   => [[$noah, TaskAssigneeRole::Assignee->value], [$emma, TaskAssigneeRole::Reviewer->value]],
                'comments'    => [
                    [$noah, 'Salesforce integration complete and tested. Starting HubSpot next.', null],
                ],
            ],
            [
                'project'     => $chatbot,
                'title'       => 'Build admin dashboard frontend',
                'description' => 'Create the admin dashboard for managing chatbot configurations, viewing analytics, training intents, and monitoring live conversations. Use Vue.js with Tailwind CSS.',
                'status'      => TaskStatus::Todo,
                'priority'    => TaskPriority::Medium,
                'start_date'  => '2026-05-01',
                'due_date'    => '2026-07-15',
                'completed_at'=> null,
                'estimated_hours' => 120.00,
                'created_by'  => $james,
                'updated_by'  => $james,
                'assignees'   => [[$liam, TaskAssigneeRole::Assignee->value], [$olivia, TaskAssigneeRole::Reviewer->value]],
                'comments'    => [],
            ],
            [
                'project'     => $chatbot,
                'title'       => 'Write comprehensive API documentation',
                'description' => 'Create developer documentation for all public APIs including authentication, endpoints, request/response examples, rate limits, and SDK usage guides.',
                'status'      => TaskStatus::Todo,
                'priority'    => TaskPriority::Low,
                'start_date'  => '2026-07-01',
                'due_date'    => '2026-08-15',
                'completed_at'=> null,
                'estimated_hours' => 24.00,
                'created_by'  => $james,
                'updated_by'  => $james,
                'assignees'   => [[$emma, TaskAssigneeRole::Assignee->value]],
                'comments'    => [],
            ],
            [
                'project'     => $chatbot,
                'title'       => 'Performance load testing and optimisation',
                'description' => 'Conduct load testing to verify 10,000 concurrent session target. Profile bottlenecks and optimise database queries, caching, and WebSocket connections.',
                'status'      => TaskStatus::Todo,
                'priority'    => TaskPriority::High,
                'start_date'  => '2026-07-15',
                'due_date'    => '2026-08-25',
                'completed_at'=> null,
                'estimated_hours' => 40.00,
                'created_by'  => $sarah,
                'updated_by'  => $sarah,
                'assignees'   => [[$william, TaskAssigneeRole::Assignee->value], [$noah, TaskAssigneeRole::Assignee->value]],
                'comments'    => [],
            ],

            // === Healthcare Analytics Dashboard ===
            [
                'project'     => $health,
                'title'       => 'Define HIPAA-compliant data schema',
                'description' => 'Design the complete database schema ensuring HIPAA compliance. All PHI must be encrypted at rest and in transit. Include audit logging tables for all data access.',
                'status'      => TaskStatus::InProgress,
                'priority'    => TaskPriority::Urgent,
                'start_date'  => '2026-03-05',
                'due_date'    => '2026-03-31',
                'completed_at'=> null,
                'estimated_hours' => 40.00,
                'created_by'  => $james,
                'updated_by'  => $ava,
                'assignees'   => [[$ava, TaskAssigneeRole::Assignee->value], [$emma, TaskAssigneeRole::Reviewer->value]],
                'comments'    => [
                    [$ava, 'Initial schema draft is ready for review. Key concern: we need to separate PII into a dedicated encrypted schema.', null],
                    [$emma, 'The approach looks solid. Make sure to add created_by and accessed_by fields to the audit log.', null],
                    [$ava, 'Updated. Audit log now captures user ID, timestamp, IP, and action type for all PHI access.', null],
                ],
            ],
            [
                'project'     => $health,
                'title'       => 'Set up HIPAA-compliant AWS infrastructure',
                'description' => 'Provision HIPAA-eligible AWS services: RDS with encryption, S3 with server-side encryption, VPC with private subnets, CloudTrail for audit logging, and WAF for application security.',
                'status'      => TaskStatus::Todo,
                'priority'    => TaskPriority::Urgent,
                'start_date'  => '2026-04-01',
                'due_date'    => '2026-04-30',
                'completed_at'=> null,
                'estimated_hours' => 48.00,
                'created_by'  => $sarah,
                'updated_by'  => $sarah,
                'assignees'   => [[$william, TaskAssigneeRole::Assignee->value], [$noah, TaskAssigneeRole::Reviewer->value]],
                'comments'    => [],
            ],
            [
                'project'     => $health,
                'title'       => 'Design analytics dashboard wireframes',
                'description' => 'Create high-fidelity wireframes for the patient analytics dashboard. Must include: patient cohort views, KPI cards, trend charts, risk stratification visualisations, and alert management.',
                'status'      => TaskStatus::Review,
                'priority'    => TaskPriority::Medium,
                'start_date'  => '2026-03-10',
                'due_date'    => '2026-04-15',
                'completed_at'=> null,
                'estimated_hours' => 40.00,
                'created_by'  => $james,
                'updated_by'  => $olivia,
                'assignees'   => [[$olivia, TaskAssigneeRole::Assignee->value], [$james, TaskAssigneeRole::Reviewer->value]],
                'comments'    => [
                    [$olivia, 'Wireframes for main dashboard and cohort views are done and uploaded to Figma. Please review.', null],
                    [$james, 'Great work overall. One request: add a patient risk score widget prominently on the main view.', null],
                    [$olivia, 'Added the risk score widget with colour coding (green/yellow/red). Updated the Figma file.', null],
                ],
            ],
            [
                'project'     => $health,
                'title'       => 'Implement real-time patient data pipeline',
                'description' => 'Build streaming data pipeline using Apache Kafka and Flink for real-time ingestion of HL7 FHIR messages from hospital systems, with sub-second latency SLA.',
                'status'      => TaskStatus::Todo,
                'priority'    => TaskPriority::High,
                'start_date'  => '2026-05-01',
                'due_date'    => '2026-07-31',
                'completed_at'=> null,
                'estimated_hours' => 120.00,
                'created_by'  => $emma,
                'updated_by'  => $emma,
                'assignees'   => [[$noah, TaskAssigneeRole::Assignee->value], [$alex, TaskAssigneeRole::Assignee->value], [$ava, TaskAssigneeRole::Reviewer->value]],
                'comments'    => [],
            ],

            // === Retail Inventory Management ===
            [
                'project'     => $retail,
                'title'       => 'Integrate warehouse management system APIs',
                'description' => 'Integrate with 5 warehouse management systems (SAP WM, Oracle WMS, Manhattan, HighJump, Fishbowl) via REST and SOAP APIs for real-time inventory data synchronisation.',
                'status'      => TaskStatus::Done,
                'priority'    => TaskPriority::High,
                'start_date'  => '2025-11-05',
                'due_date'    => '2025-12-31',
                'completed_at'=> '2025-12-28 16:00:00',
                'estimated_hours' => 80.00,
                'created_by'  => $sarah,
                'updated_by'  => $noah,
                'assignees'   => [[$noah, TaskAssigneeRole::Assignee->value], [$emma, TaskAssigneeRole::Reviewer->value]],
                'comments'    => [
                    [$noah, 'All 5 WMS integrations complete. SAP SOAP was the most complex — needed custom WSDL parsing.', null],
                    [$emma, 'Code reviewed. Clean implementation. Logging all failed sync events to a dead-letter queue.', null],
                ],
            ],
            [
                'project'     => $retail,
                'title'       => 'Build ML demand forecasting model',
                'description' => 'Develop time-series demand forecasting model using Prophet and LightGBM ensemble. Must achieve <10% MAPE on 90-day forecast horizon across all product categories.',
                'status'      => TaskStatus::InProgress,
                'priority'    => TaskPriority::High,
                'start_date'  => '2026-01-10',
                'due_date'    => '2026-03-31',
                'completed_at'=> null,
                'estimated_hours' => 100.00,
                'created_by'  => $emma,
                'updated_by'  => $alex,
                'assignees'   => [[$alex, TaskAssigneeRole::Assignee->value], [$ava, TaskAssigneeRole::Reviewer->value]],
                'comments'    => [
                    [$alex, 'Current MAPE is 8.3% on electronics category. Working on improving perishables which is at 13.1%.', null],
                    [$ava, 'Try adding weather data as an exogenous variable for perishables — it often helps significantly.', null],
                    [$alex, 'Great suggestion! Weather features dropped perishables MAPE to 9.7%. Will continue tuning.', null],
                ],
            ],
            [
                'project'     => $retail,
                'title'       => 'Implement automated reordering logic',
                'description' => 'Build the automated purchase order generation system that triggers reorders based on demand forecasts, safety stock levels, supplier lead times, and budget constraints.',
                'status'      => TaskStatus::Todo,
                'priority'    => TaskPriority::Medium,
                'start_date'  => '2026-04-01',
                'due_date'    => '2026-05-15',
                'completed_at'=> null,
                'estimated_hours' => 56.00,
                'created_by'  => $emma,
                'updated_by'  => $emma,
                'assignees'   => [[$noah, TaskAssigneeRole::Assignee->value]],
                'comments'    => [],
            ],
            [
                'project'     => $retail,
                'title'       => 'Build inventory management UI',
                'description' => 'Develop the retail manager-facing web UI with real-time stock levels, low-stock alerts, reorder management, supplier performance dashboards, and bulk import/export.',
                'status'      => TaskStatus::Review,
                'priority'    => TaskPriority::Medium,
                'start_date'  => '2026-01-15',
                'due_date'    => '2026-04-30',
                'completed_at'=> null,
                'estimated_hours' => 80.00,
                'created_by'  => $james,
                'updated_by'  => $liam,
                'assignees'   => [[$liam, TaskAssigneeRole::Assignee->value], [$amelia, TaskAssigneeRole::Assignee->value], [$olivia, TaskAssigneeRole::Reviewer->value]],
                'comments'    => [
                    [$liam, 'Main views complete. The bulk import with CSV preview and validation is the last piece.', null],
                    [$olivia, 'UI looks great! The data table performance needs work — it lags with 10,000+ rows. Consider virtualisation.', null],
                    [$liam, 'Implemented virtual scrolling with TanStack Table. Now handles 100,000 rows smoothly.', null],
                ],
            ],

            // === Logistics Route Optimizer (Completed project) ===
            [
                'project'     => $logistics,
                'title'       => 'Collect and prepare historical routing data',
                'description' => 'Extract, clean, and label 3 years of historical delivery route data from SmartLogistics systems. Build feature engineering pipeline for ML model training.',
                'status'      => TaskStatus::Done,
                'priority'    => TaskPriority::High,
                'start_date'  => '2025-05-05',
                'due_date'    => '2025-06-30',
                'completed_at'=> '2025-06-28 14:00:00',
                'estimated_hours' => 60.00,
                'created_by'  => $ava,
                'updated_by'  => $alex,
                'assignees'   => [[$alex, TaskAssigneeRole::Assignee->value], [$ava, TaskAssigneeRole::Reviewer->value]],
                'comments'    => [],
            ],
            [
                'project'     => $logistics,
                'title'       => 'Train and validate route optimization model',
                'description' => 'Train reinforcement learning and graph neural network models for route optimization. Benchmark against industry standard (OR-Tools). Target: 20%+ improvement in efficiency.',
                'status'      => TaskStatus::Done,
                'priority'    => TaskPriority::High,
                'start_date'  => '2025-07-01',
                'due_date'    => '2025-10-31',
                'completed_at'=> '2025-10-25 11:00:00',
                'estimated_hours' => 200.00,
                'created_by'  => $ava,
                'updated_by'  => $ava,
                'assignees'   => [[$ava, TaskAssigneeRole::Assignee->value], [$alex, TaskAssigneeRole::Assignee->value], [$sarah, TaskAssigneeRole::Reviewer->value]],
                'comments'    => [
                    [$ava, 'Model achieves 23% improvement in route efficiency and 18% fuel cost reduction. Exceeds targets!', null],
                ],
            ],
            [
                'project'     => $logistics,
                'title'       => 'Deploy optimization engine to production',
                'description' => 'Deploy the route optimization engine to AWS with auto-scaling, 99.9% uptime SLA, and sub-200ms response time for single route calculations.',
                'status'      => TaskStatus::Done,
                'priority'    => TaskPriority::High,
                'start_date'  => '2025-11-01',
                'due_date'    => '2025-12-01',
                'completed_at'=> '2025-11-28 10:00:00',
                'estimated_hours' => 48.00,
                'created_by'  => $sarah,
                'updated_by'  => $william,
                'assignees'   => [[$william, TaskAssigneeRole::Assignee->value], [$noah, TaskAssigneeRole::Assignee->value]],
                'comments'    => [],
            ],

            // === E-Learning Platform Redesign ===
            [
                'project'     => $elearning,
                'title'       => 'Conduct user research and discovery interviews',
                'description' => 'Interview 20 learners and 5 course creators to understand pain points with current platform. Synthesise findings into a research report with actionable insights.',
                'status'      => TaskStatus::Done,
                'priority'    => TaskPriority::Medium,
                'start_date'  => '2026-02-05',
                'due_date'    => '2026-02-28',
                'completed_at'=> '2026-02-25 17:00:00',
                'estimated_hours' => 40.00,
                'created_by'  => $james,
                'updated_by'  => $olivia,
                'assignees'   => [[$olivia, TaskAssigneeRole::Assignee->value], [$james, TaskAssigneeRole::Reviewer->value]],
                'comments'    => [
                    [$olivia, 'Research complete. Top pain points: difficult course navigation (78%), poor mobile experience (65%), and lack of progress visibility (58%).', null],
                    [$james, 'Excellent findings. Let\'s prioritise mobile-first design and clear progress tracking in the new design.', null],
                ],
            ],
            [
                'project'     => $elearning,
                'title'       => 'Redesign information architecture and navigation',
                'description' => 'Restructure the entire information architecture based on user research findings. Create new sitemap, navigation taxonomy, and content hierarchy for improved discoverability.',
                'status'      => TaskStatus::InProgress,
                'priority'    => TaskPriority::Medium,
                'start_date'  => '2026-03-01',
                'due_date'    => '2026-04-30',
                'completed_at'=> null,
                'estimated_hours' => 48.00,
                'created_by'  => $james,
                'updated_by'  => $olivia,
                'assignees'   => [[$olivia, TaskAssigneeRole::Assignee->value], [$amelia, TaskAssigneeRole::Assignee->value]],
                'comments'    => [
                    [$olivia, 'New sitemap is drafted. Reducing navigation depth from 5 levels to 3. Much more intuitive.', null],
                ],
            ],
            [
                'project'     => $elearning,
                'title'       => 'Create interactive learning flow prototypes',
                'description' => 'Build high-fidelity interactive prototypes in Figma for the new course player, progress tracking, and learning path features. Must be usable for client validation sessions.',
                'status'      => TaskStatus::Review,
                'priority'    => TaskPriority::High,
                'start_date'  => '2026-04-01',
                'due_date'    => '2026-05-15',
                'completed_at'=> null,
                'estimated_hours' => 60.00,
                'created_by'  => $james,
                'updated_by'  => $amelia,
                'assignees'   => [[$amelia, TaskAssigneeRole::Assignee->value], [$olivia, TaskAssigneeRole::Reviewer->value]],
                'comments'    => [
                    [$amelia, 'Prototype covers course player, chapter navigation, quizzes, and progress dashboard. Ready for review.', null],
                    [$olivia, 'Prototype is impressive! Small note: the quiz result animation could be more celebratory for gamification.', null],
                ],
            ],
            [
                'project'     => $elearning,
                'title'       => 'Design gamification and achievement system',
                'description' => 'Design badges, leaderboards, streaks, and XP system to increase learner engagement. Create all visual assets, animation specs, and reward trigger logic documentation.',
                'status'      => TaskStatus::Todo,
                'priority'    => TaskPriority::Medium,
                'start_date'  => '2026-05-01',
                'due_date'    => '2026-07-15',
                'completed_at'=> null,
                'estimated_hours' => 56.00,
                'created_by'  => $james,
                'updated_by'  => $james,
                'assignees'   => [[$amelia, TaskAssigneeRole::Assignee->value], [$liam, TaskAssigneeRole::Watcher->value]],
                'comments'    => [],
            ],

            // === Customer Support Automation (On Hold) ===
            [
                'project'     => $support,
                'title'       => 'Gather requirements from NexusAI stakeholders',
                'description' => 'Conduct structured discovery sessions with NexusAI support team leads to document current ticket volume, categories, escalation rules, and SLA requirements.',
                'status'      => TaskStatus::Done,
                'priority'    => TaskPriority::Medium,
                'start_date'  => '2026-04-05',
                'due_date'    => '2026-04-20',
                'completed_at'=> '2026-04-18 15:00:00',
                'estimated_hours' => 20.00,
                'created_by'  => $mia,
                'updated_by'  => $mia,
                'assignees'   => [[$mia, TaskAssigneeRole::Assignee->value], [$james, TaskAssigneeRole::Reviewer->value]],
                'comments'    => [
                    [$mia, 'Requirements doc complete. NexusAI receives ~3,200 tickets/month. Top categories: billing (28%), technical (45%), account (17%), other (10%).', null],
                ],
            ],
            [
                'project'     => $support,
                'title'       => 'Evaluate and select NLP vendor for ticket classification',
                'description' => 'Evaluate AWS Comprehend, Google CCAI, and custom fine-tuned models for ticket intent classification. Benchmark accuracy, latency, and cost on sample ticket data.',
                'status'      => TaskStatus::Blocked,
                'priority'    => TaskPriority::High,
                'start_date'  => '2026-04-20',
                'due_date'    => '2026-05-15',
                'completed_at'=> null,
                'estimated_hours' => 24.00,
                'created_by'  => $ava,
                'updated_by'  => $ava,
                'assignees'   => [[$ava, TaskAssigneeRole::Assignee->value]],
                'comments'    => [
                    [$ava, 'Task on hold pending NexusAI budget approval for Q3. Will resume once project budget is confirmed.', null],
                ],
            ],
        ];

        $createdTasks = [];

        foreach ($tasksData as $data) {
            if (!$data['project'] || !$data['created_by'] || !$data['updated_by']) continue;

            $task = Task::create([
                'project_id'      => $data['project']->id,
                'title'           => $data['title'],
                'description'     => $data['description'],
                'status'          => $data['status'],
                'priority'        => $data['priority'],
                'start_date'      => $data['start_date'],
                'due_date'        => $data['due_date'],
                'completed_at'    => $data['completed_at'],
                'estimated_hours' => $data['estimated_hours'],
                'created_by'      => $data['created_by']->id,
                'updated_by'      => $data['updated_by']->id,
            ]);

            $createdTasks[] = $task;

            // Assignees
            foreach ($data['assignees'] as [$assigneeUser, $assigneeRole]) {
                if (!$assigneeUser) continue;
                TaskAssignee::firstOrCreate(
                    ['task_id' => $task->id, 'user_id' => $assigneeUser->id],
                    ['role' => $assigneeRole]
                );
            }

            // Task creation log
            TaskLog::create([
                'task_id'     => $task->id,
                'user_id'     => $data['created_by']->id,
                'action'      => TaskLogAction::Created->value,
                'description' => "Task created with status '{$data['status']->label()}' and priority '{$data['priority']->value}'.",
            ]);

            // Status change log if not todo
            if ($data['status'] !== TaskStatus::Todo) {
                TaskLog::create([
                    'task_id'   => $task->id,
                    'user_id'   => $data['updated_by']->id,
                    'action'    => TaskLogAction::StatusChanged->value,
                    'field'     => 'status',
                    'old_value' => TaskStatus::Todo->value,
                    'new_value' => $data['status']->value,
                    'description' => "Status changed from 'todo' to '{$data['status']->value}'.",
                ]);
            }

            // Assigned log
            if (!empty($data['assignees'])) {
                $firstAssignee = $data['assignees'][0][0];
                if ($firstAssignee) {
                    TaskLog::create([
                        'task_id'     => $task->id,
                        'user_id'     => $data['created_by']->id,
                        'action'      => TaskLogAction::Assigned->value,
                        'description' => "Task assigned to {$firstAssignee->name}.",
                    ]);
                }
            }

            // Comments
            $parentComment = null;
            foreach ($data['comments'] as $index => [$commenter, $text, $parentRef]) {
                if (!$commenter) continue;
                $comment = TaskComment::create([
                    'task_id'   => $task->id,
                    'user_id'   => $commenter->id,
                    'comment'   => $text,
                    'parent_id' => null,
                ]);

                if ($index === 0) {
                    $parentComment = $comment;
                }

                TaskLog::create([
                    'task_id'     => $task->id,
                    'user_id'     => $commenter->id,
                    'action'      => TaskLogAction::CommentAdded->value,
                    'description' => 'Comment added to task.',
                ]);
            }
        }

        // Add task dependencies
        $this->addDependencies($createdTasks, $chatbot, $health, $retail, $elearning);

        $this->command->info('Tasks seeded: ' . count($createdTasks));
    }

    private function addDependencies(
        array $tasks,
        ?object $chatbot,
        ?object $health,
        ?object $retail,
        ?object $elearning
    ): void {
        if (!$chatbot || !$health || !$retail || !$elearning) return;

        $admin = User::where('email', 'admin@admin.com')->first();
        if (!$admin) return;

        $getTask = fn(int $projectId, string $title) => collect($tasks)
            ->first(fn($t) => $t->project_id === $projectId && str_contains($t->title, $title));

        // Chatbot: API integrations depends on NLP pipeline
        $nlp = $getTask($chatbot->id, 'Set up NLP pipeline');
        $api = $getTask($chatbot->id, 'Build REST API integrations');
        if ($nlp && $api) {
            TaskDependency::firstOrCreate(
                ['task_id' => $api->id, 'depends_on_task_id' => $nlp->id],
                ['created_by' => $admin->id]
            );
        }

        // Chatbot: Load testing depends on API integrations
        $load = $getTask($chatbot->id, 'Performance load testing');
        if ($api && $load) {
            TaskDependency::firstOrCreate(
                ['task_id' => $load->id, 'depends_on_task_id' => $api->id],
                ['created_by' => $admin->id]
            );
        }

        // Healthcare: Infrastructure setup depends on schema definition
        $schema = $getTask($health->id, 'Define HIPAA-compliant data schema');
        $infra  = $getTask($health->id, 'Set up HIPAA-compliant AWS infrastructure');
        if ($schema && $infra) {
            TaskDependency::firstOrCreate(
                ['task_id' => $infra->id, 'depends_on_task_id' => $schema->id],
                ['created_by' => $admin->id]
            );
        }

        // Healthcare: Data pipeline depends on infrastructure
        $pipeline = $getTask($health->id, 'Implement real-time patient data pipeline');
        if ($infra && $pipeline) {
            TaskDependency::firstOrCreate(
                ['task_id' => $pipeline->id, 'depends_on_task_id' => $infra->id],
                ['created_by' => $admin->id]
            );
        }

        // Retail: Reordering logic depends on ML model
        $ml = $getTask($retail->id, 'Build ML demand forecasting model');
        $reorder = $getTask($retail->id, 'Implement automated reordering logic');
        if ($ml && $reorder) {
            TaskDependency::firstOrCreate(
                ['task_id' => $reorder->id, 'depends_on_task_id' => $ml->id],
                ['created_by' => $admin->id]
            );
        }

        // E-learning: IA redesign depends on user research
        $research = $getTask($elearning->id, 'Conduct user research');
        $ia = $getTask($elearning->id, 'Redesign information architecture');
        if ($research && $ia) {
            TaskDependency::firstOrCreate(
                ['task_id' => $ia->id, 'depends_on_task_id' => $research->id],
                ['created_by' => $admin->id]
            );
        }
    }
}
