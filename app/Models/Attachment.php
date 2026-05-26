<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'file_path',
        'file_name',
        'mime_type',
        'size',
        'attachable_type',
        'attachable_id',
    ];


    /**
     * The parent model (Article or Profile).
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
