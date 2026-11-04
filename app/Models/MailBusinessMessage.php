<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MailBusinessMessage extends Model
{
    use SoftDeletes;

    protected $table = 'mail_business_messages';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'to_recipients' => 'array',
            'cc_recipients' => 'array',
            'bcc_recipients' => 'array',
            'flags' => 'array',
            'is_seen' => 'boolean',
            'is_answered' => 'boolean',
            'is_flagged' => 'boolean',
            'is_draft' => 'boolean',
            'has_attachments' => 'boolean',
            'received_at' => 'datetime',
            'sent_at' => 'datetime',
            'internal_date' => 'datetime',
            'synced_at' => 'datetime',
            'remote_deleted_at' => 'datetime',
        ];
    }

    public function mailBusinessAccount(): BelongsTo
    {
        return $this->belongsTo(MailAccessAccount::class, 'mail_business_account_id', 'id');
    }

    public function mailAccessAccount(): BelongsTo
    {
        return $this->mailBusinessAccount();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MailBusinessMessageAttachment::class, 'mail_business_message_id', 'id');
    }
}
