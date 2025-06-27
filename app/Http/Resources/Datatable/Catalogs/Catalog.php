<?php

namespace App\Http\Resources\Datatable\Catalogs;

use Illuminate\Http\Resources\Json\JsonResource;

class Catalog extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => htmlspecialchars($this->title),
            'description' => $this->description,
            'image' => $this->image ? asset($this->image) : asset('/empty.jpg'),
            'link' => $this->id,
            'status' => $this->status,
            'created_at' => jdate($this->created_at)->format('%d %B %Y'),
            'links' => [
                'edit' => route('admin.catalog.edit', ['catalog' => $this]),
                'destroy' => route('admin.catalog.destroy', ['catalog' => $this]),
            ]
        ];
    }
}
