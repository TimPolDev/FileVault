<?php

declare(strict_types=1);

namespace FileVault\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FileVersionModel extends Model
{
    protected $table = 'file_versions';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'file_id',
        'version_number',
        'storage_path',
        'hash',
        'size',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(FileModel::class, 'file_id', 'id');
    }
}
