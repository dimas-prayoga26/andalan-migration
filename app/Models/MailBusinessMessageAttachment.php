<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailBusinessMessageAttachment extends Model
{
    protected $table = 'mail_business_message_attachments';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_inline' => 'boolean',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(MailBusinessMessage::class, 'mail_business_message_id', 'id');
    }
}
