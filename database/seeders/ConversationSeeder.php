<?php

namespace Database\Seeders;

use App\Enums\ConversationType;
use App\Enums\ConversationUserRole;
use App\Enums\MessageType;
use App\Models\Conversation;
use App\Models\ConversationUser;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConversationSeeder extends Seeder
{
    public function run(): void
    {
        $u = fn(string $email) => User::where('email', $email)->first();

        $admin   = $u('admin@admin.com');
        $sarah   = $u('sarah.chen@botjourney.ai');
        $james   = $u('james.wilson@botjourney.ai');
        $emma    = $u('emma.rodriguez@botjourney.ai');
        $liam    = $u('liam.thompson@botjourney.ai');
        $olivia  = $u('olivia.davis@botjourney.ai');
        $noah    = $u('noah.martinez@botjourney.ai');
        $ava     = $u('ava.johnson@botjourney.ai');
        $william = $u('william.brown@botjourney.ai');
        $isabella = $u('isabella.garcia@botjourney.ai');
        $lucas   = $u('lucas.anderson@botjourney.ai');
        $mia     = $u('mia.taylor@botjourney.ai');
        $ethan   = $u('ethan.lee@botjourney.ai');
        $charlotte = $u('charlotte.clark@botjourney.ai');
        $alex    = $u('alexander.wright@botjourney.ai');
        $amelia  = $u('amelia.lewis@botjourney.ai');

        // --- Direct Conversations ---

        $this->createConversation(
            type: ConversationType::Direct,
            title: null,
            creator: $sarah,
            members: [
                [$sarah, ConversationUserRole::Admin],
                [$james, ConversationUserRole::Member],
            ],
            messages: [
                [$sarah, 'Hey James, how is the AI Chatbot project timeline looking? Are we still on track for the Q3 delivery?'],
                [$james, 'Hi Sarah! Overall yes, we\'re on track. The NLP pipeline is progressing well — Ava says intent recognition is hitting 94% accuracy on English. The CRM integrations are slightly behind, Noah is catching up.'],
                [$sarah, 'Good to hear. What\'s the risk with the CRM integrations?'],
                [$james, 'Zendesk has some quirky API rate limits we didn\'t anticipate. Noah is implementing an exponential backoff queue. Should be resolved by end of next week.'],
                [$sarah, 'Understood. Keep me posted. Also, NexusAI\'s Marcus sent a message asking for a demo around mid-July — can we accommodate that?'],
                [$james, 'Mid-July should work. The core platform will be demo-ready by then. I\'ll coordinate with the team and send Marcus a calendar invite.'],
                [$sarah, 'Perfect. Let\'s also make sure Olivia\'s UI mockups are ready to show. First impressions matter with Marcus.'],
                [$james, 'Agreed. I\'ll loop Olivia in.'],
            ]
        );

        $this->createConversation(
            type: ConversationType::Direct,
            title: null,
            creator: $emma,
            members: [
                [$emma, ConversationUserRole::Admin],
                [$noah, ConversationUserRole::Member],
            ],
            messages: [
                [$emma, 'Noah, how are the Zendesk integration issues going?'],
                [$noah, 'Making progress. The rate limiting was hitting us at 100 req/min but their enterprise tier allows 700. We just needed the right API key settings from NexusAI.'],
                [$emma, 'Nice! When can we expect it fully working?'],
                [$noah, 'I should have the webhook sync fully working by Thursday. Salesforce and HubSpot are already 100% done and tested.'],
                [$emma, 'That\'s great. Once Zendesk is done, I need you to write unit tests covering all three integrations. At least 85% coverage.'],
                [$noah, 'Already started on the test suite. Salesforce has 91% coverage. I\'ll bring HubSpot and Zendesk up to the same level.'],
                [$emma, 'Awesome, you\'re ahead of me! One more thing — make sure the error logs include enough context to debug any sync failures without needing to reproduce the issue.'],
                [$noah, 'Will do. I\'m using structured JSON logging with correlation IDs so we can trace any failure end-to-end.'],
            ]
        );

        $this->createConversation(
            type: ConversationType::Direct,
            title: null,
            creator: $james,
            members: [
                [$james, ConversationUserRole::Admin],
                [$olivia, ConversationUserRole::Member],
            ],
            messages: [
                [$james, 'Olivia, the HealthFlow wireframes look great! The patient risk score widget was exactly what the client needed.'],
                [$olivia, 'Thanks James! I was worried the colour coding might seem too clinical, but the traffic light system (green/yellow/red) seems intuitive enough.'],
                [$james, 'It\'s perfect. Priya from HealthFlow actually specifically mentioned she loved the risk stratification view. Can you prepare a Figma prototype we can share with them for async review?'],
                [$olivia, 'Absolutely! I\'ll have an interactive Figma link ready by tomorrow afternoon. Should I include the mobile views as well?'],
                [$james, 'Yes please, mobile is important for their clinical staff who use tablets during rounds.'],
                [$olivia, 'Got it. I\'ll make sure the tablet views look polished. Should the prototype be view-only or can they leave comments?'],
                [$james, 'Allow comments — they have good product instincts and we want their input before we finalize the design.'],
                [$olivia, 'Done. I\'ll send you the link tomorrow by 3pm.'],
            ]
        );

        $this->createConversation(
            type: ConversationType::Direct,
            title: null,
            creator: $sarah,
            members: [
                [$sarah, ConversationUserRole::Admin],
                [$ava, ConversationUserRole::Member],
            ],
            messages: [
                [$ava, 'Sarah, I have an update on the logistics model. We hit 23% efficiency improvement — exceeding our 20% target!'],
                [$sarah, 'That\'s fantastic, Ava! The client is going to be thrilled. What was the key improvement?'],
                [$ava, 'Adding weather data as an exogenous variable was the biggest win for perishables. Also, Alexander\'s work on the graph neural network for urban route clustering made a huge difference.'],
                [$sarah, 'Excellent teamwork. Make sure both of you are highlighted in the client delivery report.'],
                [$ava, 'Will do. For the chatbot project, I wanted to flag that we might want to explore RLHF for improving response quality beyond just accuracy. Thoughts?'],
                [$sarah, 'RLHF is a great idea but might be scope creep for Phase 1. Let\'s document it as a Phase 2 enhancement and mention it to NexusAI — they might be interested in the roadmap.'],
                [$ava, 'Good call. I\'ll add it to the technical backlog with a rough effort estimate so we have something concrete to share.'],
                [$sarah, 'Perfect. And great work on the logistics project — genuinely impressive results.'],
            ]
        );

        $this->createConversation(
            type: ConversationType::Direct,
            title: null,
            creator: $mia,
            members: [
                [$mia, ConversationUserRole::Admin],
                [$lucas, ConversationUserRole::Member],
            ],
            messages: [
                [$lucas, 'Mia, how is the NexusAI onboarding going? Are they happy with the chatbot progress?'],
                [$mia, 'Marcus seems satisfied overall, but he\'s asked for a monthly status report going forward. I\'ve drafted a template — can you review it?'],
                [$lucas, 'Of course. Send it over. I\'d suggest keeping the executive summary to half a page max — Marcus is busy and appreciates brevity.'],
                [$mia, 'Good point. I\'ll trim it. Also, he mentioned they\'re potentially interested in a second project — the customer support automation we put on hold.'],
                [$lucas, 'Interesting timing. Sarah put it on hold due to resource constraints, but if NexusAI is keen to fund it separately, we might be able to unblock it.'],
                [$mia, 'Should I mention this to Sarah directly?'],
                [$lucas, 'Yes, set up a quick call between you, me, and Sarah this week. This could be a significant upsell opportunity.'],
            ]
        );

        // --- Group Conversations ---

        $this->createConversation(
            type: ConversationType::Group,
            title: 'Engineering Team',
            creator: $sarah,
            members: [
                [$sarah,   ConversationUserRole::Admin],
                [$emma,    ConversationUserRole::Admin],
                [$liam,    ConversationUserRole::Member],
                [$noah,    ConversationUserRole::Member],
                [$ava,     ConversationUserRole::Member],
                [$william, ConversationUserRole::Member],
                [$alex,    ConversationUserRole::Member],
            ],
            messages: [
                [$sarah,   'Team — reminder that our weekly engineering sync is tomorrow at 10am. Please have your status updates ready.'],
                [$emma,    'Will do. I\'ll prepare a summary of the CRM integration status and the NLP pipeline progress.'],
                [$william, 'I have a DataDog alert to discuss — we\'re seeing elevated p99 latency on the chatbot inference endpoint. Worth reviewing together.'],
                [$noah,    'Good catch William. I noticed it too. I suspect it\'s the model loading time — we might need to implement model caching.'],
                [$ava,     'Agreed. I can bring the profiling results to the sync. Model cold start is ~800ms which is too high.'],
                [$liam,    'For context from the frontend — users are experiencing slight delays on the first message of a session. Caching the model would fix it from UX side too.'],
                [$emma,    'Let\'s make this a priority discussion item tomorrow. William, can you prepare the DataDog graphs?'],
                [$william, 'Already on it. I\'ll have a dashboard link ready.'],
                [$alex,    'I can also look at quantizing the model to reduce load time. Might be a quick win.'],
                [$sarah,   'Great initiative Alex. Bring the trade-off analysis tomorrow — accuracy vs latency.'],
            ]
        );

        $this->createConversation(
            type: ConversationType::Group,
            title: 'AI Chatbot — Project Team',
            creator: $sarah,
            members: [
                [$sarah,   ConversationUserRole::Admin],
                [$james,   ConversationUserRole::Admin],
                [$emma,    ConversationUserRole::Member],
                [$noah,    ConversationUserRole::Member],
                [$ava,     ConversationUserRole::Member],
                [$olivia,  ConversationUserRole::Member],
                [$liam,    ConversationUserRole::Member],
            ],
            messages: [
                [$james,  'Good morning team! Quick update: NexusAI confirmed the mid-July demo date. We have 6 weeks to get the platform demo-ready.'],
                [$emma,   'That\'s tight but doable. What are the must-have features for the demo?'],
                [$james,  'Intent recognition, multi-turn conversations, Salesforce integration, and the admin dashboard overview. Zendesk can wait until after the demo.'],
                [$noah,   'Salesforce integration is production-ready. I can start helping with admin dashboard if Liam needs backend support.'],
                [$liam,   'That would be great Noah! The real-time conversation monitoring view needs WebSocket support. Can you help set up the endpoint?'],
                [$noah,   'Sure, I\'ll create a dedicated WebSocket channel for live conversation monitoring. Will ping you when it\'s ready to integrate.'],
                [$ava,    'Intent recognition is at 94% for English. For the demo, should I focus on Spanish and French as the secondary languages? Those cover most of NexusAI\'s market.'],
                [$james,  'Yes, focus on Spanish and French for now. We\'ll expand to the other 13 languages in Phase 2.'],
                [$olivia, 'The admin dashboard mockups are final. Sharing the Figma link now: [link]. Liam, let me know if you have questions.'],
                [$liam,   'These look awesome Olivia! Really clean. Starting implementation this week.'],
                [$sarah,  'Excellent work everyone. Let\'s do a full dry-run demo two weeks before the actual client demo. I\'ll schedule it.'],
            ]
        );

        $this->createConversation(
            type: ConversationType::Group,
            title: '#general',
            creator: $admin,
            members: [
                [$admin,     ConversationUserRole::Admin],
                [$sarah,     ConversationUserRole::Admin],
                [$james,     ConversationUserRole::Member],
                [$emma,      ConversationUserRole::Member],
                [$liam,      ConversationUserRole::Member],
                [$olivia,    ConversationUserRole::Member],
                [$noah,      ConversationUserRole::Member],
                [$ava,       ConversationUserRole::Member],
                [$william,   ConversationUserRole::Member],
                [$isabella,  ConversationUserRole::Member],
                [$lucas,     ConversationUserRole::Member],
                [$mia,       ConversationUserRole::Member],
                [$ethan,     ConversationUserRole::Member],
                [$charlotte, ConversationUserRole::Member],
                [$alex,      ConversationUserRole::Member],
                [$amelia,    ConversationUserRole::Member],
            ],
            messages: [
                [$charlotte, '🎉 Welcome to the BotJourney general channel! This is our company-wide space for announcements and casual chat.'],
                [$sarah,     'Exciting news: the Logistics Route Optimizer for SmartLogistics has been officially delivered! The results exceeded all targets — 23% efficiency improvement. Huge congrats to Ava, Alexander, Noah, and William!'],
                [$ava,       'Thank you! It was a challenging project but really rewarding. Alexander\'s graph neural network work was the breakthrough.'],
                [$alex,      'Team effort! The data pipeline Noah and William set up made the model training so much faster.'],
                [$william,   'Great to hear! That deployment at 2am was worth it.'],
                [$isabella,  'Just published our Q2 company blog post about the project. Check it out on LinkedIn! The AI community is loving it.'],
                [$lucas,     'Great write-up Isabella! Already got two inbound inquiries from companies interested in similar projects.'],
                [$mia,       'SmartLogistics sent a lovely thank-you note. Yuki said the route optimizer saved them over $2M in fuel costs in the first quarter.'],
                [$james,     'That\'s an incredible ROI. This is exactly the kind of impact we aim for. Let\'s document this as a case study.'],
                [$ethan,     'Finance update: Q2 revenue target hit! 📊 Great work everyone. Q3 projections look strong with the NexusAI and FutureLearn projects ramping up.'],
                [$charlotte, 'Reminder: Q2 performance reviews start next Monday. Please complete your self-assessments in the HR portal by Friday.'],
                [$sarah,     'Also a reminder: we\'re hosting a team lunch on Friday to celebrate the SmartLogistics delivery. Venue TBD — Charlotte will share details.'],
            ]
        );

        $this->command->info('Conversations seeded: 8 conversations with messages');
    }

    private function createConversation(
        ConversationType $type,
        ?string $title,
        User $creator,
        array $members,
        array $messages
    ): Conversation {
        $conversation = Conversation::create([
            'type'       => $type,
            'title'      => $title,
            'created_by' => $creator->id,
        ]);

        foreach ($members as [$user, $role]) {
            if (!$user) continue;
            ConversationUser::firstOrCreate(
                ['conversation_id' => $conversation->id, 'user_id' => $user->id],
                [
                    'role'      => $role,
                    'joined_at' => now()->subDays(rand(5, 30)),
                ]
            );
        }

        $previousMessage = null;
        foreach ($messages as $index => [$sender, $body]) {
            if (!$sender) continue;
            $msg = Message::create([
                'conversation_id' => $conversation->id,
                'user_id'         => $sender->id,
                'type'            => MessageType::Text,
                'body'            => $body,
                'reply_to'        => null,
                'is_read'         => true,
                'read_at'         => now()->subMinutes(rand(1, 1440)),
            ]);
            $previousMessage = $msg;
        }

        return $conversation;
    }
}
