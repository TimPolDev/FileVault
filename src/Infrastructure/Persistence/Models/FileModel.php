<?php

declare(strict_types=1);

namespace FileVault\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class FileModel extends Model
{
    protected $table = 'files';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'size',
        'mime_type',
        'storage_path',
        'hash',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(FileVersionModel::class, 'file_id', 'id');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(ShareModel::class, 'file_id', 'id');
    }
}
