<?php

namespace Database\Seeders;

use App\Enums\ExpenseStatus;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $u = fn(string $email) => User::where('email', $email)->first();
        $c = fn(string $title) => ExpenseCategory::where('title', $title)->first();
        $p = fn(string $name)  => Project::where('name', $name)->first();

        $sarah  = $u('sarah.chen@botjourney.ai');
        $james  = $u('james.wilson@botjourney.ai');
        $emma   = $u('emma.rodriguez@botjourney.ai');
        $noah   = $u('noah.martinez@botjourney.ai');
        $ava    = $u('ava.johnson@botjourney.ai');
        $ethan  = $u('ethan.lee@botjourney.ai');
        $liam   = $u('liam.thompson@botjourney.ai');
        $will   = $u('william.brown@botjourney.ai');
        $alex   = $u('alexander.wright@botjourney.ai');

        $catOps    = $c('Operational Costs');
        $catProj   = $c('Project Resources');
        $catTools  = $c('Software & Tools');
        $catTravel = $c('Business Travel');
        $catHourly = $c('Hourly Staff Payments');
        $catMarketing = $c('Marketing & Advertising');
        $catOther  = $c('Miscellaneous');

        $chatbotProject  = $p('AI Chatbot Platform');
        $healthProject   = $p('Healthcare Analytics Dashboard');
        $retailProject   = $p('Retail Inventory Management System');
        $logisticsProject = $p('Logistics Route Optimizer');
        $elearningProject = $p('E-Learning Platform Redesign');

        $expenses = [
            // Software & Tools
            [
                'category'     => $catTools,
                'project'      => $chatbotProject,
                'paid_by'      => $sarah,
                'title'        => 'OpenAI API — Q2 2026',
                'description'  => 'GPT-4 API usage for chatbot NLP processing. Includes fine-tuning calls.',
                'amount'       => 3200.00,
                'expense_date' => '2026-04-01',
                'status'       => ExpenseStatus::Paid,
            ],
            [
                'category'     => $catTools,
                'project'      => null,
                'paid_by'      => $ethan,
                'title'        => 'GitHub Enterprise License — Annual',
                'description'  => 'Annual GitHub Enterprise plan for 20 developers.',
                'amount'       => 4800.00,
                'expense_date' => '2026-01-10',
                'status'       => ExpenseStatus::Paid,
            ],
            [
                'category'     => $catTools,
                'project'      => null,
                'paid_by'      => $ethan,
                'title'        => 'Figma Organization Plan',
                'description'  => 'Annual Figma organization license for design team.',
                'amount'       => 1440.00,
                'expense_date' => '2026-01-15',
                'status'       => ExpenseStatus::Paid,
            ],
            [
                'category'     => $catTools,
                'project'      => $healthProject,
                'paid_by'      => $noah,
                'title'        => 'AWS HIPAA Compliance Module',
                'description'  => 'AWS Business Associate Agreement and HIPAA-compliant services setup for HealthFlow project.',
                'amount'       => 850.00,
                'expense_date' => '2026-03-15',
                'status'       => ExpenseStatus::Approved,
            ],
            [
                'category'     => $catTools,
                'project'      => null,
                'paid_by'      => $will,
                'title'        => 'DataDog Monitoring — Q2 2026',
                'description'  => 'Infrastructure monitoring and APM for production services.',
                'amount'       => 2100.00,
                'expense_date' => '2026-04-01',
                'status'       => ExpenseStatus::Paid,
            ],
            // Travel
            [
                'category'     => $catTravel,
                'project'      => $chatbotProject,
                'paid_by'      => $sarah,
                'title'        => 'NexusAI Kickoff Meeting — San Francisco',
                'description'  => 'Round-trip flights and hotel for project kickoff meeting at NexusAI headquarters. 3 team members attended.',
                'amount'       => 4250.00,
                'expense_date' => '2026-01-20',
                'status'       => ExpenseStatus::Paid,
            ],
            [
                'category'     => $catTravel,
                'project'      => $logisticsProject,
                'paid_by'      => $ava,
                'title'        => 'SmartLogistics — Tokyo Client Visit',
                'description'  => 'Business travel to Tokyo for final project delivery and handover presentation.',
                'amount'       => 7800.00,
                'expense_date' => '2025-12-10',
                'status'       => ExpenseStatus::Paid,
            ],
            [
                'category'     => $catTravel,
                'project'      => $elearningProject,
                'paid_by'      => $james,
                'title'        => 'FutureLearn — London Discovery Workshop',
                'description'  => 'Travel and accommodation for 2-day discovery workshop with FutureLearn product team.',
                'amount'       => 3600.00,
                'expense_date' => '2026-02-10',
                'status'       => ExpenseStatus::Paid,
            ],
            // Project resources
            [
                'category'     => $catProj,
                'project'      => $chatbotProject,
                'paid_by'      => $emma,
                'title'        => 'Third-party NLP Dataset License',
                'description'  => 'Commercial license for multilingual training dataset (15 languages).',
                'amount'       => 5500.00,
                'expense_date' => '2026-02-01',
                'status'       => ExpenseStatus::Paid,
            ],
            [
                'category'     => $catProj,
                'project'      => $retailProject,
                'paid_by'      => $emma,
                'title'        => 'Retail POS Integration SDK',
                'description'  => 'Commercial SDK for integrating with 8 major POS systems used by RetailEdge.',
                'amount'       => 2400.00,
                'expense_date' => '2025-11-20',
                'status'       => ExpenseStatus::Paid,
            ],
            [
                'category'     => $catProj,
                'project'      => $healthProject,
                'paid_by'      => $ava,
                'title'        => 'Medical Terminology Database',
                'description'  => 'Subscription to standardized medical terminology database (ICD-10, SNOMED CT).',
                'amount'       => 1800.00,
                'expense_date' => '2026-03-20',
                'status'       => ExpenseStatus::Approved,
            ],
            // Hourly staff
            [
                'category'     => $catHourly,
                'project'      => $chatbotProject,
                'paid_by'      => $ethan,
                'title'        => 'Liam Thompson — April 2026 Hours',
                'description'  => '160 hours of frontend development @ $65/hr',
                'amount'       => 10400.00,
                'expense_date' => '2026-05-01',
                'status'       => ExpenseStatus::Paid,
            ],
            [
                'category'     => $catHourly,
                'project'      => $retailProject,
                'paid_by'      => $ethan,
                'title'        => 'William Brown — April 2026 Hours',
                'description'  => '120 hours of DevOps work @ $75/hr',
                'amount'       => 9000.00,
                'expense_date' => '2026-05-01',
                'status'       => ExpenseStatus::Paid,
            ],
            [
                'category'     => $catHourly,
                'project'      => $logisticsProject,
                'paid_by'      => $ethan,
                'title'        => 'Alexander Wright — December 2025 Hours',
                'description'  => '180 hours of AI research @ $90/hr',
                'amount'       => 16200.00,
                'expense_date' => '2026-01-05',
                'status'       => ExpenseStatus::Paid,
            ],
            // Marketing
            [
                'category'     => $catMarketing,
                'project'      => null,
                'paid_by'      => $u('isabella.garcia@botjourney.ai'),
                'title'        => 'LinkedIn Ads — Q2 2026 Campaign',
                'description'  => 'LinkedIn sponsored content campaign targeting enterprise tech decision-makers.',
                'amount'       => 5000.00,
                'expense_date' => '2026-04-01',
                'status'       => ExpenseStatus::Approved,
            ],
            [
                'category'     => $catMarketing,
                'project'      => null,
                'paid_by'      => $u('isabella.garcia@botjourney.ai'),
                'title'        => 'Google Ads — May 2026',
                'description'  => 'PPC campaign for BotJourney AI services targeting B2B market.',
                'amount'       => 3500.00,
                'expense_date' => '2026-05-01',
                'status'       => ExpenseStatus::Pending,
            ],
            // Operational
            [
                'category'     => $catOps,
                'project'      => null,
                'paid_by'      => $ethan,
                'title'        => 'Office Rent — May 2026',
                'description'  => 'Monthly co-working space rental for BotJourney team.',
                'amount'       => 8500.00,
                'expense_date' => '2026-05-01',
                'status'       => ExpenseStatus::Paid,
            ],
            [
                'category'     => $catOps,
                'project'      => null,
                'paid_by'      => $ethan,
                'title'        => 'Electricity & Internet — May 2026',
                'description'  => 'Monthly utility bills for the office.',
                'amount'       => 650.00,
                'expense_date' => '2026-05-05',
                'status'       => ExpenseStatus::Paid,
            ],
            // Other
            [
                'category'     => $catOther,
                'project'      => $chatbotProject,
                'paid_by'      => $james,
                'title'        => 'Team Dinner — Project Milestone Celebration',
                'description'  => 'Team dinner to celebrate successful completion of chatbot MVP.',
                'amount'       => 420.00,
                'expense_date' => '2026-03-31',
                'status'       => ExpenseStatus::Paid,
            ],
            [
                'category'     => $catOther,
                'project'      => null,
                'paid_by'      => $u('charlotte.clark@botjourney.ai'),
                'title'        => 'Company Swag — Branded Merchandise',
                'description'  => 'BotJourney branded t-shirts, mugs, and notebooks for the team.',
                'amount'       => 780.00,
                'expense_date' => '2026-04-20',
                'status'       => ExpenseStatus::Approved,
            ],
        ];

        foreach ($expenses as $data) {
            if (!$data['category'] || !$data['paid_by']) continue;

            Expense::create([
                'category_id'  => $data['category']->id,
                'project_id'   => $data['project']?->id,
                'paid_by'      => $data['paid_by']->id,
                'title'        => $data['title'],
                'description'  => $data['description'],
                'amount'       => $data['amount'],
                'expense_date' => $data['expense_date'],
                'status'       => $data['status'],
            ]);
        }

        $this->command->info('Expenses seeded: ' . count($expenses));
    }
}
