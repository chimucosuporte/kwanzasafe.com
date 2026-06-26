<?php

namespace App\Http\Resources;

use App\Models\Recourse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recurso (appeal) de uma transação, para a app mobile.
 *
 * @mixin Recourse
 */
class RecourseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'status'       => $this->status,
            'status_label' => Recourse::STATUS_LABELS[$this->status] ?? $this->status,
            'reason'       => $this->reason,
            'resolution'   => $this->resolution,
            'is_active'    => $this->isActive(),
            'can_cancel'   => $this->isActive(),
            'expires_at'   => $this->expires_at?->toIso8601String(),
            'resolved_at'  => $this->resolved_at?->toIso8601String(),
            'created_at'   => $this->created_at?->toIso8601String(),
        ];
    }
}
