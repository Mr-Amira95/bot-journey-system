<?php

namespace App\Http\Controllers;

use App\Enums\CallEventType;
use App\Enums\CallParticipantStatus;
use App\Enums\CallStatus;
use App\Enums\CallType;
use App\Events\CallInitiated;
use App\Events\CallStatusChanged;
use App\Models\Call;
use App\Models\CallParticipant;
use App\Models\Conversation;
use App\Services\AgoraTokenService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CallController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $calls = Call::whereHas('participants', fn ($q) => $q->where('user_id', $userId))
            ->with(['conversation', 'startedBy', 'participants.user'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('calls.index', compact('calls'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'conversation_id' => ['required', 'exists:conversations,id'],
            'type'            => ['required', Rule::enum(CallType::class)],
        ]);

        $conversation = Conversation::findOrFail($data['conversation_id']);

        $isMember = $conversation->members()
            ->where('user_id', auth()->id())
            ->whereNull('left_at')
            ->exists();

        abort_unless($isMember, 403);

        // Create the call
        $call = Call::create([
            'conversation_id' => $conversation->id,
            'type'            => $data['type'],
            'status'          => CallStatus::Ringing,
            'started_by'      => auth()->id(),
        ]);

        // Add caller as participant (auto-joined)
        $call->participants()->create([
            'user_id'    => auth()->id(),
            'status'     => CallParticipantStatus::Joined,
            'joined_at'  => now(),
            'is_video_on'=> $data['type'] === 'video',
        ]);

        // Invite all other conversation members
        $otherMembers = $conversation->members()
            ->where('user_id', '!=', auth()->id())
            ->whereNull('left_at')
            ->pluck('user_id');

        foreach ($otherMembers as $recipientId) {
            $call->participants()->create([
                'user_id' => $recipientId,
                'status'  => CallParticipantStatus::Invited,
            ]);
            broadcast(new CallInitiated($call, $recipientId));
        }

        // Log event
        $call->events()->create([
            'user_id' => auth()->id(),
            'event'   => CallEventType::Joined,
        ]);

        return redirect()->route('calls.show', $call);
    }

    public function show(Call $call)
    {
        $userId = auth()->id();

        $participant = $call->participants()->where('user_id', $userId)->firstOrFail();

        // If call already ended, redirect with message
        if ($call->status === CallStatus::Ended) {
            return redirect()->route('calls.index')->with('info', 'This call has ended.');
        }

        // Mark caller as joined if coming in via accept flow
        if ($participant->status === CallParticipantStatus::Invited) {
            $participant->update([
                'status'     => CallParticipantStatus::Joined,
                'joined_at'  => now(),
                'is_video_on'=> $call->type === CallType::Video,
            ]);

            // Mark call as ongoing when first other person joins
            if ($call->status === CallStatus::Ringing) {
                $call->update(['status' => CallStatus::Ongoing, 'started_at' => now()]);
            }

            $call->events()->create([
                'user_id' => $userId,
                'event'   => CallEventType::Joined,
            ]);

            broadcast(new CallStatusChanged($call, 'participant_joined', ['user_id' => $userId]));
        }

        $call->load('conversation', 'startedBy', 'participants.user');

        $channelName  = 'call_' . $call->id;
        $agora        = new AgoraTokenService();
        $agoraToken   = $agora->buildTokenWithUid($channelName, $userId);
        $agoraAppId   = config('services.agora.app_id');

        return view('calls.show', compact('call', 'participant', 'channelName', 'agoraToken', 'agoraAppId'));
    }

    public function token(Call $call)
    {
        $participant = $call->participants()->where('user_id', auth()->id())->first();
        abort_unless($participant, 403);

        $channelName = 'call_' . $call->id;
        $agora       = new AgoraTokenService();
        $token       = $agora->buildTokenWithUid($channelName, auth()->id());

        return response()->json([
            'token'       => $token,
            'channel'     => $channelName,
            'uid'         => auth()->id(),
            'app_id'      => config('services.agora.app_id'),
        ]);
    }

    public function join(Call $call)
    {
        $participant = $call->participants()->where('user_id', auth()->id())->first();
        abort_unless($participant && $participant->status === CallParticipantStatus::Invited, 403);

        $participant->update(['status' => CallParticipantStatus::Joined, 'joined_at' => now()]);

        return redirect()->route('calls.show', $call);
    }

    public function reject(Call $call)
    {
        $participant = $call->participants()->where('user_id', auth()->id())->first();
        abort_unless($participant, 403);

        $participant->update(['status' => CallParticipantStatus::Rejected]);

        $call->events()->create([
            'user_id' => auth()->id(),
            'event'   => CallEventType::Left,
        ]);

        broadcast(new CallStatusChanged($call, 'participant_rejected', ['user_id' => auth()->id()]));

        // If all non-callers rejected, mark call as rejected
        $anyJoined = $call->participants()
            ->where('user_id', '!=', $call->started_by)
            ->whereIn('status', ['joined'])
            ->exists();

        if (! $anyJoined) {
            $pending = $call->participants()
                ->where('user_id', '!=', $call->started_by)
                ->whereIn('status', ['invited'])
                ->exists();

            if (! $pending) {
                $call->update(['status' => CallStatus::Rejected, 'ended_at' => now()]);
                broadcast(new CallStatusChanged($call, 'call_rejected'));
            }
        }

        return response()->json(['success' => true]);
    }

    public function updateParticipant(Request $request, Call $call)
    {
        $participant = $call->participants()->where('user_id', auth()->id())->firstOrFail();

        $data = $request->validate([
            'is_muted'          => ['sometimes', 'boolean'],
            'is_video_on'       => ['sometimes', 'boolean'],
            'is_screen_sharing' => ['sometimes', 'boolean'],
        ]);

        $participant->update($data);

        // Log event
        if (array_key_exists('is_muted', $data)) {
            $call->events()->create([
                'user_id' => auth()->id(),
                'event'   => $data['is_muted'] ? CallEventType::Muted : CallEventType::Unmuted,
            ]);
        }
        if (array_key_exists('is_video_on', $data)) {
            $call->events()->create([
                'user_id' => auth()->id(),
                'event'   => $data['is_video_on'] ? CallEventType::VideoOn : CallEventType::VideoOff,
            ]);
        }
        if (array_key_exists('is_screen_sharing', $data)) {
            $call->events()->create([
                'user_id' => auth()->id(),
                'event'   => $data['is_screen_sharing'] ? CallEventType::ScreenShareStarted : CallEventType::ScreenShareStopped,
            ]);
            // Track screen share record
            if ($data['is_screen_sharing']) {
                $call->screenShares()->create(['user_id' => auth()->id(), 'status' => 'active']);
            } else {
                $call->screenShares()
                    ->where('user_id', auth()->id())
                    ->where('status', 'active')
                    ->update(['status' => 'stopped', 'ended_at' => now()]);
            }
        }

        broadcast(new CallStatusChanged($call, 'participant_updated', [
            'user_id'           => auth()->id(),
            'is_muted'          => $participant->fresh()->is_muted,
            'is_video_on'       => $participant->fresh()->is_video_on,
            'is_screen_sharing' => $participant->fresh()->is_screen_sharing,
        ]));

        return response()->json(['success' => true]);
    }

    public function leave(Call $call)
    {
        $participant = $call->participants()->where('user_id', auth()->id())->firstOrFail();

        $participant->update([
            'status'  => CallParticipantStatus::Left,
            'left_at' => now(),
        ]);

        $call->events()->create([
            'user_id' => auth()->id(),
            'event'   => CallEventType::Left,
        ]);

        broadcast(new CallStatusChanged($call, 'participant_left', ['user_id' => auth()->id()]));

        // If no one is left in the call, end it
        $anyActive = $call->participants()
            ->where('status', CallParticipantStatus::Joined->value)
            ->where('user_id', '!=', auth()->id())
            ->exists();

        if (! $anyActive) {
            $call->update([
                'status'   => CallStatus::Ended,
                'ended_at' => now(),
                'ended_by' => auth()->id(),
            ]);
            broadcast(new CallStatusChanged($call, 'call_ended'));
        }

        return response()->json(['success' => true]);
    }

    public function end(Call $call)
    {
        // Only the call starter can force-end for everyone
        abort_unless($call->started_by === auth()->id(), 403);

        $call->update([
            'status'   => CallStatus::Ended,
            'ended_at' => now(),
            'ended_by' => auth()->id(),
        ]);

        $call->participants()
            ->where('status', CallParticipantStatus::Joined->value)
            ->update(['status' => CallParticipantStatus::Left->value, 'left_at' => now()]);

        $call->events()->create([
            'user_id' => auth()->id(),
            'event'   => CallEventType::Left,
        ]);

        broadcast(new CallStatusChanged($call, 'call_ended'));

        return response()->json(['success' => true]);
    }

    public function logEvent(Request $request, Call $call)
    {
        $participant = $call->participants()->where('user_id', auth()->id())->first();
        abort_unless($participant, 403);

        $data = $request->validate([
            'event'   => ['required', Rule::enum(CallEventType::class)],
            'payload' => ['nullable', 'array'],
        ]);

        $call->events()->create([
            'user_id' => auth()->id(),
            'event'   => $data['event'],
            'payload' => $data['payload'] ?? null,
        ]);

        return response()->json(['success' => true]);
    }
}
