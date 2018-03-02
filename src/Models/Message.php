<?php


namespace Benwilkins\Yak\Models;

use Benwilkins\Yak\Events\MessageSent;
use Illuminate\Support\Facades\DB;


/**
 * Class Message
 * @package Benwilkins\Yak\Models
 */
class Message extends YakBaseModel
{
//    use UuidKey;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'author_id',
        'body'
    ];

    protected static function boot()
    {
        parent::boot();
        self::bootUuidForKey();

        static::created(function (Message $model) {
            $model->handleNewMessage();
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(self::userClass());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * @param $query
     * @param $conversationId
     * @return mixed
     */
    public function scopeOfConversation($query, $conversationId)
    {
        return $query->where('conversation_id', $conversationId);
    }

    /**
     * Tasks for when a new message is created.
     */
    public function handleNewMessage()
    {
        // Add any tasks to run when a new message is created.
        $this->updateConversationStateForRecipients();
        $this->conversation->touch();
        $this->sendEvents();
    }

    /**
     * Sets the conversation state for recipients
     */
    protected function updateConversationStateForRecipients()
    {
        foreach ($this->conversation->participants as $participant) {
            if ($participant->id !== $this->author_id) {
                ConversationState::updateOrCreate(
                    ['conversation_id' => $this->conversation_id, 'user_id' => $this->author_id],
                    ['read' => false]
                );
            }
        }
    }

    /**
     * Sends any Laravel events that should be sent when a new message is created.
     */
    protected function sendEvents()
    {
        event(new MessageSent($this, DB::getDefaultConnection()));
    }
}