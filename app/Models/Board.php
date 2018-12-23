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

  public static function click($request, $id) {
    $board = Board::findOrFail($id);
    $countX = 0;
    $countO = 0;

    if ($board->{$request->clicked}) return $board;
    foreach ($board->getAttributes() as $key => $value) {
      if (!in_array($key, ['id','finished', 'created_at', 'updated_at', 'winner', 'bot'])) {
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
    } elseif ($board->winner) {
      $board = new Board;
      $board->save();
    }

    return $board;
  }

  public static function checkWinner($id) {
    $board = Board::findOrFail($id);

    if ($board->top_left == $board->top && $board->top == $board->top_right && $board->top) { // top row
      $board->winner = $board->top;
    } elseif ($board->left == $board->center && $board->center == $board->right && $board->center) { // middle row
      $board->winner = $board->center;
    } elseif ($board->bottom_left == $board->bottom && $board->bottom == $board->bottom_right && $board->bottom) { // bottom row
      $board->winner = $board->bottom;
    } elseif ($board->top_left == $board->left && $board->left == $board->bottom_left && $board->left) { // left column
      $board->winner = $board->left;
    } elseif ($board->top == $board->center && $board->center == $board->bottom && $board->center) { // middle column
      $board->winner = $board->center;
    } elseif ($board->top_right == $board->right && $board->right == $board->bottom_right && $board->right) { // right column
      $board->winner = $board->right;
    } elseif ($board->top_left == $board->center && $board->center == $board->bottom_right && $board->center) { // left diagonal
      $board->winner = $board->center;
    } elseif ($board->top_right == $board->center && $board->center == $board->bottom_left && $board->center) { // right diagonal
      $board->winner = $board->center;
    }
    $board->save();

    return $board;
  }
}
