<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
  protected $hidden = [
    'id',
    'created_at',
    'updated_at',
  ];

  protected $casts = [
    'finished' => 'bool'
  ];

  public static function click($request, $id) {
    $board = Board::findOrFail($id);
    $countX = 0;
    $countO = 0;

    if ($board->{$request->clicked}) return $board;
    foreach ($board->getAttributes() as $key => $value) {
      if (!in_array($key, ['id','finished', 'created_at', 'updated_at'])) {
        if ($value == "X") {
          $countX ++;
        } elseif ($value == "O") {
          $countO ++;
        }
      }
    }
    if ($countX > $countO || $countX == $countO) {
      $board->{$request->clicked} = "O";
    } elseif ($countO > $countX) {
      $board->{$request->clicked} = "X";
    }
    $board->save();

    return $board;
  }

  public static function getLatestUnfinishedBoard() {
    $board = Board::orderBy('id', "desc")->first();

    if (!$board) {
      $board = new Board;
      $board->save();
    } elseif ($board->finished) {
      $board = new Board;
      $board->save();
    }

    return $board;
  }
}
