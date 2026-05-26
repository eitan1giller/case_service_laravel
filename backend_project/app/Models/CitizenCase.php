<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CitizenCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'applicant_name',
        'applicant_national_id',
        'contact_email',
        'contact_phone',
        'subject',
        'description',
        'metadata',
        'idempotency_key',
    ];
}
