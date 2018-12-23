<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Board extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);

        return [
            'id' => $this->id,
            'top_left' => $this->top_left,
            'top' => $this->top,
            'top_right' => $this->top_right,
            'left' => $this->left,
            'center' => $this->center,
            'right' => $this->right,
            'bottom_left' => $this->bottom_left,
            'bottom' => $this->bottom,
            'bottom_right' => $this->bottom_right,
            'winner' => $this->winner,
            'bot' => $this->bot,
        ];
    }

    public function with($request) {
        return [
            /*'version' => '1.0.0',
            'author_url' => url('http://traversymedia.com')*/
        ];
    }
}
