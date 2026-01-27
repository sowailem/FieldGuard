<?php

namespace Sowailem\FieldGuard\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FieldGuardRuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'model_class' => $this->model_class,
            'field_name' => $this->field_name,
            'read_policy' => $this->read_policy,
            'write_policy' => $this->write_policy,
            'mask' => $this->mask,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
