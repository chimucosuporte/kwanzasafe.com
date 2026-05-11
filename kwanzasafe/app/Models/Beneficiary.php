<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{
    use HasFactory;

    /**
     * Colunas conforme migration 2026_04_19_055608_create_beneficiaries_table:
     *   user_id, bank_name, iban, holder_name, timestamps
     */
    protected $fillable = [
        'user_id',
        'bank_name',
        'iban',
        'holder_name',
    ];

    /**
     * Beneficiário pertence a um utilizador.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}