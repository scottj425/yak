<?php

namespace Benwilkins\Yak\Events;

use Benwilkins\Yak\Enums\BroadcastChannels;
use Benwilkins\Yak\Contracts\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ConversationParticipantRemoved extends YakEvent implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public $conversation;
    public $participant;

    /**
     * Create a new event instance.
     *
     * @param Conversation $conversation
     * @param Model $participant
     * @param string|null $connectionName
     */
    public function __construct(Conversation $conversation, $participant, $connectionName = null)
    {
        $this->connectionName = $connectionName ?: DB::getDefaultConnection();
        $this->conversation = $conversation;
        $this->participant = $participant;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel(BroadcastChannels::CONVERSATION_PREFIX . $this->conversation->id);
    }

    public function broadcastWith()
    {
        return [
            'conversation' => $this->conversation,
            'participant' => $this->participant
        ];
    }
}
