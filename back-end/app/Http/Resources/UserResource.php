<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => collect($this->getRoleNames()->toArray())
                ->map(fn($r) => is_string($r) ? strtolower($r) : $r)
                ->unique()
                ->sort()
                ->values()
                ->toArray(),
            'permissions' => collect($this->getAllPermissions()->pluck('name')->toArray())
                ->map(fn($p) => is_string($p) ? strtolower($p) : $p)
                ->unique()
                ->sort()
                ->values()
                ->toArray(),
        ];
    }
}
