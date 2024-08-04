<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'uploaded_file';
    protected $fillable = ['user_id', 'file_type', 'result'];

    protected $casts = [
        'data' => 'array',
        'data.recipient' => 'array',
        'data.issuer' => 'array',
        'data.issuer.identityProof' => 'array',
        'data.issuer.identityProof' => 'json',
        'signature' => 'array',
        'signature.targetHash' => 'string',
        'result' => 'string'
    ];

    const allowedType = [
        'application/json'
    ];
}
