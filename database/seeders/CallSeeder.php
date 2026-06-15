<?php

namespace Database\Seeders;

use App\Enums\CallEventType;
use App\Enums\CallParticipantStatus;
use App\Enums\CallScreenShareStatus;
use App\Enums\CallStatus;
use App\Enums\CallType;
use App\Enums\ConversationType;
use App\Enums\ConversationUserRole;
use App\Models\Call;
use App\Models\CallEvent;
use App\Models\CallParticipant;
use App\Models\CallScreenShare;
use App\Models\Conversation;
use App\Models\ConversationUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class CallSeeder extends Seeder
{
    public function run(): void
    {
        $u = fn(string $email) => User::where('email', $email)->first();

        $sarah   = $u('sarah.chen@botjourney.ai');
        $james   = $u('james.wilson@botjourney.ai');
        $emma    = $u('emma.rodriguez@botjourney.ai');
        $noah    = $u('noah.martinez@botjourney.ai');
        $ava     = $u('ava.johnson@botjourney.ai');
        $william = $u('william.brown@botjourney.ai');
        $liam    = $u('liam.thompson@botjourney.ai');
        $olivia  = $u('olivia.davis@botjourney.ai');
        $alex    = $u('alexander.wright@botjourney.ai');

        $callsData = [
            // 1. Engineering sync — video call
            [
                'title'        => 'Engineering Weekly Sync',
                'conv_type'    => ConversationType::Group,
                'type'         => CallType::Video,
                'status'       => CallStatus::Ended,
                'started_by'   => $sarah,
                'ended_by'     => $sarah,
                'started_at'   => now()->subDays(7)->setTime(10, 0),
                'ended_at'     => now()->subDays(7)->setTime(11, 15),
                'members'      => [$sarah, $emma, $noah, $william, $ava, $liam, $alex],
                'participants' => [
                    [$sarah,   CallParticipantStatus::Joined, true,  false],
                    [$emma,    CallParticipantStatus::Joined, false, true],
                    [$noah,    CallParticipantStatus::Joined, false, true],
                    [$william, CallParticipantStatus::Joined, false, true],
                    [$ava,     CallParticipantStatus::Joined, false, true],
                    [$liam,    CallParticipantStatus::Joined, false, true],
                    [$alex,    CallParticipantStatus::Joined, false, true],
                ],
                'screen_share' => [$william, now()->subDays(7)->setTime(10, 30), now()->subDays(7)->setTime(10, 45)],
            ],
            // 2. NexusAI demo prep — video call with screen share
            [
                'title'        => 'NexusAI Demo Preparation',
                'conv_type'    => ConversationType::Group,
                'type'         => CallType::Video,
                'status'       => CallStatus::Ended,
                'started_by'   => $james,
                'ended_by'     => $james,
                'started_at'   => now()->subDays(3)->setTime(14, 0),
                'ended_at'     => now()->subDays(3)->setTime(15, 30),
                'members'      => [$sarah, $james, $emma, $noah, $ava, $olivia, $liam],
                'participants' => [
                    [$sarah,  CallParticipantStatus::Joined, false, true],
                    [$james,  CallParticipantStatus::Joined, true,  true],
                    [$emma,   CallParticipantStatus::Joined, false, true],
                    [$noah,   CallParticipantStatus::Joined, true,  true],
                    [$ava,    CallParticipantStatus::Joined, false, true],
                    [$olivia, CallParticipantStatus::Joined, false, true],
                    [$liam,   CallParticipantStatus::Joined, true,  true],
                ],
                'screen_share' => [$james, now()->subDays(3)->setTime(14, 10), now()->subDays(3)->setTime(14, 50)],
            ],
            // 3. Direct call: Sarah ↔ James (quick audio check-in)
            [
                'title'        => 'Quick Project Check-in',
                'conv_type'    => ConversationType::Direct,
                'type'         => CallType::Audio,
                'status'       => CallStatus::Ended,
                'started_by'   => $sarah,
                'ended_by'     => $sarah,
                'started_at'   => now()->subDays(1)->setTime(9, 0),
                'ended_at'     => now()->subDays(1)->setTime(9, 15),
                'members'      => [$sarah, $james],
                'participants' => [
                    [$sarah, CallParticipantStatus::Joined, false, false],
                    [$james, CallParticipantStatus::Joined, false, false],
                ],
                'screen_share' => null,
            ],
            // 4. HealthFlow dashboard design review — video
            [
                'title'        => 'HealthFlow Dashboard Design Review',
                'conv_type'    => ConversationType::Group,
                'type'         => CallType::Video,
                'status'       => CallStatus::Ended,
                'started_by'   => $james,
                'ended_by'     => $james,
                'started_at'   => now()->subDays(5)->setTime(15, 0),
                'ended_at'     => now()->subDays(5)->setTime(16, 0),
                'members'      => [$james, $emma, $ava, $olivia, $william],
                'participants' => [
                    [$james,   CallParticipantStatus::Joined, true,  true],
                    [$emma,    CallParticipantStatus::Joined, false, true],
                    [$ava,     CallParticipantStatus::Joined, false, true],
                    [$olivia,  CallParticipantStatus::Joined, false, true],
                    [$william, CallParticipantStatus::Joined, true,  true],
                ],
                'screen_share' => [$olivia, now()->subDays(5)->setTime(15, 10), now()->subDays(5)->setTime(15, 45)],
            ],
            // 5. Missed call
            [
                'title'        => 'Retail Project Sync',
                'conv_type'    => ConversationType::Direct,
                'type'         => CallType::Audio,
                'status'       => CallStatus::Missed,
                'started_by'   => $emma,
                'ended_by'     => null,
                'started_at'   => now()->subHours(3)->setTime(11, 0),
                'ended_at'     => null,
                'members'      => [$emma, $noah],
                'participants' => [
                    [$emma, CallParticipantStatus::Joined,  false, false],
                    [$noah, CallParticipantStatus::Missed,  false, false],
                ],
                'screen_share' => null,
            ],
        ];

        foreach ($callsData as $data) {
            if (!$data['started_by']) continue;

            // Get or create a suitable conversation
            $convType = $data['conv_type'];
            $memberIds = collect($data['members'])->filter()->pluck('id')->sort()->values();

            // Find existing conversation matching these members and type
            $conversation = null;
            $candidates = Conversation::where('type', $convType)->get();
            foreach ($candidates as $conv) {
                $convMemberIds = $conv->members()->pluck('user_id')->sort()->values();
                if ($convMemberIds->count() === $memberIds->count() && $convMemberIds->diff($memberIds)->isEmpty()) {
                    $conversation = $conv;
                    break;
                }
            }

            // If no matching conversation found, create one
            if (!$conversation) {
                $conversation = Conversation::create([
                    'type'       => $convType,
                    'title'      => $convType === ConversationType::Group ? $data['title'] : null,
                    'created_by' => $data['started_by']->id,
                ]);
                foreach ($data['members'] as $member) {
                    if (!$member) continue;
                    ConversationUser::firstOrCreate(
                        ['conversation_id' => $conversation->id, 'user_id' => $member->id],
                        ['role' => ConversationUserRole::Member, 'joined_at' => now()->subDays(10)]
                    );
                }
            }

            $call = Call::create([
                'conversation_id' => $conversation->id,
                'type'            => $data['type'],
                'status'          => $data['status'],
                'started_at'      => $data['started_at'],
                'ended_at'        => $data['ended_at'],
                'started_by'      => $data['started_by']->id,
                'ended_by'        => $data['ended_by']?->id,
            ]);

            foreach ($data['participants'] as [$participant, $pStatus, $isMuted, $isVideoOn]) {
                if (!$participant) continue;

                $joinedAt = null;
                $leftAt   = null;

                if (in_array($pStatus, [CallParticipantStatus::Joined, CallParticipantStatus::Left])) {
                    $joinedAt = $data['started_at']?->copy()->addMinutes(rand(0, 2));
                    $leftAt   = $data['ended_at'];
                }

                CallParticipant::firstOrCreate(
                    ['call_id' => $call->id, 'user_id' => $participant->id],
                    [
                        'status'           => $pStatus,
                        'joined_at'        => $joinedAt,
                        'left_at'          => $leftAt,
                        'is_muted'         => $isMuted,
                        'is_video_on'      => $isVideoOn,
                        'is_screen_sharing'=> false,
                    ]
                );

                if ($pStatus === CallParticipantStatus::Joined) {
                    CallEvent::create([
                        'call_id'    => $call->id,
                        'user_id'    => $participant->id,
                        'event'      => CallEventType::Joined->value,
                        'payload'    => null,
                        'created_at' => $joinedAt ?? $data['started_at'],
                    ]);
                }
            }

            // Screen share
            if ($data['screen_share']) {
                [$sharer, $shareStart, $shareEnd] = $data['screen_share'];
                if ($sharer) {
                    CallScreenShare::create([
                        'call_id'    => $call->id,
                        'user_id'    => $sharer->id,
                        'started_at' => $shareStart,
                        'ended_at'   => $shareEnd,
                        'status'     => CallScreenShareStatus::Stopped->value,
                    ]);

                    CallEvent::create([
                        'call_id'    => $call->id,
                        'user_id'    => $sharer->id,
                        'event'      => CallEventType::ScreenShareStarted->value,
                        'payload'    => null,
                        'created_at' => $shareStart,
                    ]);

                    CallEvent::create([
                        'call_id'    => $call->id,
                        'user_id'    => $sharer->id,
                        'event'      => CallEventType::ScreenShareStopped->value,
                        'payload'    => null,
                        'created_at' => $shareEnd,
                    ]);
                }
            }

            // End event
            if ($data['ended_by'] && $data['ended_at']) {
                CallEvent::create([
                    'call_id'    => $call->id,
                    'user_id'    => $data['ended_by']->id,
                    'event'      => CallEventType::Left->value,
                    'payload'    => null,
                    'created_at' => $data['ended_at'],
                ]);
            }
        }

        $this->command->info('Calls seeded: ' . count($callsData));
    }
}
