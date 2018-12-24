<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model {

  protected $hidden = [
    'id',
    'created_at',
    'updated_at',
  ];

  protected $casts = [
    'bot' => 'bool'
  ];

  public static function click($request, $id) {
    if ($request->clicked == "bot_game") {
      $board = self::initiateBotGame($id);
    } elseif ($request->clicked == "bot_start") {
      $board = self::startBotGame($request, $id);
    } elseif ($request->action == "check_bot_turn") {
      $board = self::clickBox($request, $id);
    } else {
      $board = self::clickBox($request, $id);
    }

    return $board;
  }

  public static function getLatestUnfinishedBoard() {
    $board = Board::orderBy('id', "desc")->first();

    if (!$board) {
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

  public static function initiateBotGame($id) {
    $board = Board::findOrFail($id);
    $board->bot_game = true;
    $board->save();

    return $board;
  }

  private static function clickBox($request, $id) {
    $board = Board::findOrFail($id);
    $countX = 0;
    $countO = 0;

    if (!$board->bot_turn) {
      if ($board->{$request->clicked}) return $board;
      foreach ($board->getAttributes() as $key => $value) {
        if (!in_array($key, ['id','finished', 'created_at', 'updated_at', 'winner', 'bot_game', 'bot_turn'])) {
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
      if ($board->bot_game) $board->bot_turn = true;
    } elseif ($board->bot_game && $board->bot_turn) {
      $board = self::botPlay($board);
    }
    $board->save();

    return $board;
  }

  private static function startBotGame($request, $id) {
    $board = Board::findOrFail($id);
    $board->bot_turn = true;
    $board->save();

    self::botPlay($board);

    return $board;
  }

  private static function botPlay($board) {
    // Check if board is empty
    /*if (!$board->top_left && !$board->top && !$board->top_right && !$board->left && !$board->center && !$board->right && !$board->bottom_left && !$board->bottom && !$board->bottom_right) {
      $board->top_left = "O";
      $board->bot_character = "O";
    }*/

    // Find first character to match
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

    $placedCharacter = false;
    while ($placedCharacter == false) {
      if (empty($filledBoxes)) {
        $board->top_left = "O";
        $board->bot_character = "O";
        $placedCharacter = true;
      } elseif (!empty($filledBoxes)) {
        // Random
        $board->{$emptyBoxes[rand(0, sizeof($emptyBoxes)-1)]} = $board->bot_character;
        $placedCharacter = true;
      }

    }

    $board->bot_turn = false;
    $board->save();

    return $board;
  }

  private static function checkBotTurn($request, $id) {
    $board = findOrFail($id);

    // if (!$board->bot_turn) return $board;

    // $board = self::botPlay($board);

    return $board;
  }
}