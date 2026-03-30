<?php

declare(strict_types=1);

namespace FileVault\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ShareModel extends Model
{
    protected $table = 'shares';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'file_id',
        'token',
        'permission',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(FileModel::class, 'file_id', 'id');
    }
}
