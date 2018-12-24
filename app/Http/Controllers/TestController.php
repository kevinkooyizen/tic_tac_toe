<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Board;

class TestController extends Controller {
  public function index() {
    $board = Board::getLatestUnfinishedBoard();

    $filledBoxes = [];
    $emptyBoxes = [];
    foreach ($board->getAttributes() as $key => $value) {
      if (!in_array($key, ['id','finished', 'created_at', 'updated_at', 'winner', 'bot_game', 'bot_turn', 'bot_character'])) {
        if ($value == $board->bot_character && $value) {
          $filledBoxes[] = $key;
        } elseif (!$value) {
          $emptyBoxes[] = $key;
        }
      }
    }

    dd($emptyBoxes);
    dd(rand(0, sizeof($emptyBoxes)-1));
  }

}
