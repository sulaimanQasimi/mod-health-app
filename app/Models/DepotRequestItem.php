<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepotRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'depot_request_id',
        'medicine_id',
        'tool_id',
        'unit_id',
        'quantity',
        'batch_number',
        'sort_order',
        'depot_transaction_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'sort_order' => 'integer',
    ];

    public function depotRequest()
    {
        return $this->belongsTo(DepotRequest::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function tool()
    {
        return $this->belongsTo(Tool::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function depotTransaction()
    {
        return $this->belongsTo(DepotTransaction::class);
    }

    public function itemType(): ?string
    {
        if ($this->medicine_id) {
            return DepotTransaction::ITEM_MEDICINE;
        }

        if ($this->tool_id) {
            return DepotTransaction::ITEM_TOOL;
        }

        return null;
    }

    public function itemName(): string
    {
        if ($this->medicine_id) {
            return $this->medicine?->name ?? '-';
        }

        if ($this->tool_id) {
            return $this->tool?->displayName() ?? $this->tool?->name ?? '-';
        }

        return '-';
    }
}
