<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public $resource;
    public $message;
    public $status;
    public $totalElement;


    public function __construct($resource, $message,  $status)
    {
        parent::__construct($resource);
        $this->$message = $message;
        $this->totalElement = count($resource);
        $this->status = $status;
    }

    public function toArray(Request $request): array
    {
        return [
            'TotalElemen' => $this->totalElement,
            'konten' => $this->resource
        ];
    }
}
