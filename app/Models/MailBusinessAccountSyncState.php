<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailBusinessAccountSyncState extends Model
{
    protected $table = 'mail_business_account_sync_states';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }

    public function mailAccessAccount(): BelongsTo
    {
        return $this->belongsTo(MailAccessAccount::class, 'mail_access_account_id', 'id');
    }
}
