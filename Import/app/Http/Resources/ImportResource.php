<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $progress = 0;
        if (!empty($this->total_iterations) && !empty($this->confirmed_iterations)) {
            $progress = (int)floor($this->confirmed_iterations / $this->total_iterations * 100);
        }
        
        return [
            'id' => $this->id,
            'file_name' => $this->file_name,
            'progress' => $progress,
            'completed' => (bool)!is_null($this->completed_at)
        ];
    }
}
