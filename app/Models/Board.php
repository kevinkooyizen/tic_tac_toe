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
      $board = self::checkBotTurn($request, $id);
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
      if (!$board->winner) $board = self::botPlay($board);
    }
    $board->save();
    $board = self::checkWinner($board->id);

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
    if ($board->winner) return $board;

    // Check if board is empty to determine if bot is first or second player to set bot_character
    if (!$board->bot_character) {
      if ($board->top_left || $board->top || $board->top_right || $board->left || $board->center || $board->right || $board->bottom_left || $board->bottom || $board->bottom_right) {
        $board->bot_character = "X";
      } elseif (!($board->top_left || $board->top || $board->top_right || $board->left || $board->center || $board->right || $board->bottom_left || $board->bottom || $board->bottom_right)) {
        $board->bot_character = "O";
      }
    }

    // Find first character to match
    $filledBoxes = [];
    $emptyBoxes = [];
    foreach ($board->getAttributes() as $key => $value) {
      if (!in_array($key, ['id', 'winner', 'bot_game', 'bot_turn', 'bot_character', 'created_at', 'updated_at'])) {
        if ($value) {
          $filledBoxes[] = $key;
        } elseif (!$value) {
          $emptyBoxes[] = $key;
        }
      }
    }
    if ($board->bot_character == "O") {
      $humanCharacter = "X";
    } elseif ($board->bot_character == "X") {
      $humanCharacter = "O";
    }
    $corners = ['top_left', 'top_right', 'bottom_left', 'bottom_right'];
    $middle = 'center';
    $sides = ['top', 'left', 'right', 'bottom'];
    if (empty($filledBoxes)) {
      $board->{$corners[rand(0, sizeof($corners)-1)]} = $board->bot_character;
    } elseif (!empty($filledBoxes)) {
      // Random - Easy Bot
      // $board->{$emptyBoxes[rand(0, sizeof($emptyBoxes)-1)]} = $board->bot_character;

      $placedCharacter = false;

      // Check if can win by placing in center
      if ($board->top_left == $board->bot_character && $board->bottom_right == $board->bot_character && !$board->center) {
        $board->center = $board->bot_character;
        $placedCharacter = true;
      }

      if ($board->top_right == $board->bot_character && $board->bottom_left == $board->bot_character && !$board->center) {
        $board->center = $board->bot_character;
        $placedCharacter = true;
      }

      // Check if opponent can win through sides by placing at corners
      if (!$placedCharacter) {
        if ($board->top == $humanCharacter && $board->top_left == $humanCharacter && !$board->top_right) {
          $board->top_right = $board->bot_character;
          $placedCharacter = true;
        }
        if ($board->top == $humanCharacter && $board->top_right == $humanCharacter && !$board->top_left) {
          $board->top_left = $board->bot_character;
          $placedCharacter = true;
        }
        if ($board->left == $humanCharacter && $board->top_left == $humanCharacter && !$board->bottom_left) {
          $board->bottom_left = $board->bot_character;
          $placedCharacter = true;
        }
        if ($board->left == $humanCharacter && $board->bottom_left == $humanCharacter && !$board->top_left) {
          $board->top_left = $board->bot_character;
          $placedCharacter = true;
        }
        if ($board->bottom == $humanCharacter && $board->bottom_left == $humanCharacter && !$board->bottom_right) {
          $board->bottom_right = $board->bot_character;
          $placedCharacter = true;
        }
        if ($board->bottom == $humanCharacter && $board->bottom_right == $humanCharacter && !$board->bottom_left) {
          $board->bottom_left = $board->bot_character;
          $placedCharacter = true;
        }
        if ($board->right == $humanCharacter && $board->bottom_right == $humanCharacter && !$board->top_right) {
          $board->top_right = $board->bot_character;
          $placedCharacter = true;
        }
        if ($board->right == $humanCharacter && $board->top_right == $humanCharacter && !$board->bottom_right) {
          $board->bottom_right = $board->bot_character;
          $placedCharacter = true;
        }
      }

      // Check if opponent can win diagonally by placing at corners
      if (!$placedCharacter) {
        if ($board->center == $humanCharacter && $board->top_left == $humanCharacter && !$board->bottom_right) {
          $board->bottom_right = $board->bot_character;
          $placedCharacter = true;
        }
        if ($board->center == $humanCharacter && $board->top_right == $humanCharacter && !$board->bottom_left) {
          $board->bottom_left = $board->bot_character;
          $placedCharacter = true;
        }
        if ($board->center == $humanCharacter && $board->bottom_right == $humanCharacter && !$board->top_left) {
          $board->top_left = $board->bot_character;
          $placedCharacter = true;
        }
        if ($board->center == $humanCharacter && $board->bottom_left == $humanCharacter && !$board->top_right) {
          $board->top_right = $board->bot_character;
          $placedCharacter = true;
        }
      }

      // Check in can win by placing at sides
      if (!$placedCharacter) {
        if ($board->top_left == $board->bot_character && $board->bottom_left == $board->bot_character && !$board->left) {
          $board->left = $board->bot_character;
          $placedCharacter = true;
        }
        if ($board->top_left == $board->bot_character && $board->top_right == $board->bot_character && !$board->top) {
          $board->top = $board->bot_character;
          $placedCharacter = true;
        }
        if ($board->top_right == $board->bot_character && $board->bottom_right == $board->bot_character && !$board->right) {
          $board->right = $board->bot_character;
          $placedCharacter = true;
        }
        if ($board->bottom_left == $board->bot_character && $board->bottom_right == $board->bot_character && !$board->bottom) {
          $board->bottom = $board->bot_character;
          $placedCharacter = true;
        }
      }

      // Check if opponent can win by sides
      if (!$placedCharacter) {
        if ($board->top_left == $humanCharacter && $board->bottom_left == $humanCharacter && !$board->left) {
          $board->left = $board->bot_character;
          $placedCharacter = true;
        }
        if (!$placedCharacter) {
          if ($board->top_left == $humanCharacter && $board->top_right == $humanCharacter && !$board->top) {
            $board->top = $board->bot_character;
            $placedCharacter = true;
          }
        }
        if (!$placedCharacter) {
          if ($board->top_right == $humanCharacter && $board->bottom_right == $humanCharacter && !$board->right) {
            $board->right = $board->bot_character;
            $placedCharacter = true;
          }
        }
        if (!$placedCharacter) {
          if ($board->bottom_left == $humanCharacter && $board->bottom_right == $humanCharacter && !$board->bottom) {
            $board->bottom = $board->bot_character;
            $placedCharacter = true;
          }
        }
      }

      // Check if opponent can win by placing at center
      if (!$placedCharacter) {
        if ($board->top_left == $humanCharacter && $board->bottom_right == $humanCharacter && !$board->center) {
          $board->center = $board->bot_character;
          $placedCharacter = true;
        }

        if ($board->top_right == $humanCharacter && $board->bottom_left == $humanCharacter && !$board->center) {
          $board->center = $board->bot_character;
          $placedCharacter = true;
        }
      }

      // Check if opponent can win by placing at sides
      if (!$placedCharacter) {
        if ($board->center == $humanCharacter) {
          if ($board->top == $humanCharacter && !$board->bottom) {
            $board->bottom = $board->bot_character;
            $placedCharacter = true;
          }
          if ($board->left == $humanCharacter && !$board->right) {
            $board->right = $board->bot_character;
            $placedCharacter = true;
          }
          if ($board->right == $humanCharacter && !$board->left) {
            $board->left = $board->bot_character;
            $placedCharacter = true;
          }
          if ($board->bottom == $humanCharacter && !$board->top) {
            $board->top = $board->bot_character;
            $placedCharacter = true;
          }
        }
      }

      // Check starting if need to block in center
      if (!$placedCharacter) {
        if (sizeof($filledBoxes) == 1 && ($board->top_left || $board->top_right || $board->bottom_left || $board->bottom_right)) {
          $board->center = $board->bot_character;
          $placedCharacter = true;
        }
      }

      // Check for filled/empty corners
      $botCharactersInCorner = 0;
      foreach ($corners as $corner) {
        // Check for filled bot character corners
        if (in_array($corner, $filledBoxes) && $board->{$corner} == $board->bot_character) {
          $botCharactersInCorner++;
        }
      }
      if ($botCharactersInCorner < 3 && !$placedCharacter) {
        foreach ($corners as $corner) {
          // Check for filled bot character corners
          if ($placedCharacter) continue;
          if (!in_array($corner, $filledBoxes)) {
            // Check if blocked
            if (in_array($corner, ['top_left', 'top_right']) && $board->top == $humanCharacter) continue;
            if (in_array($corner, ['top_left', 'bottom_left']) && $board->left == $humanCharacter) continue;
            if (in_array($corner, ['bottom_left', 'bottom_right']) && $board->bottom == $humanCharacter) continue;
            if (in_array($corner, ['top_right', 'bottom_right']) && $board->right == $humanCharacter) continue;
            $board->{$corner} = $board->bot_character;
            $placedCharacter = true;
          }
        }
      }
      // Attempt to win
      $filledBotCorners = [];
      if (!$placedCharacter) {
        // Check winning slots
        foreach ($filledBoxes as $filledBox) {
          if ($board->{$filledBox} == $board->bot_character) {
            $filledBotCorners[] = $filledBox;
          }
        }

        $uncheckedBotCorners = [];
        if (in_array('top_left', $filledBotCorners) && in_array('bottom_left', $filledBotCorners)) {
          $uncheckedBotCorners[] = ['top_left', 'bottom_left'];
        }
        if (in_array('top_left', $filledBotCorners) && in_array('top_right', $filledBotCorners)) {
          $uncheckedBotCorners[] = ['top_left', 'top_right'];
        }
        if (in_array('top_right', $filledBotCorners) && in_array('bottom_right', $filledBotCorners)) {
          $uncheckedBotCorners[] = ['top_right', 'bottom_right'];
        }
        if (in_array('bottom_left', $filledBotCorners) && in_array('bottom_right', $filledBotCorners)) {
          $uncheckedBotCorners[] = ['bottom_left', 'bottom_right'];
        }

        foreach ($uncheckedBotCorners as $key => $uncheckedBotCorner) {
          if ($placedCharacter) continue;
          if (in_array('top_left', $uncheckedBotCorner) && in_array('bottom_left', $uncheckedBotCorner) && $board->left != $humanCharacter) {
            $board->left = $board->bot_character;
            $placedCharacter = true;
          }
          if (in_array('top_left', $uncheckedBotCorner) && in_array('top_right', $uncheckedBotCorner) && $board->top != $humanCharacter) {
            $board->top = $board->bot_character;
            $placedCharacter = true;
          }
          if (in_array('top_right', $uncheckedBotCorner) && in_array('bottom_right', $uncheckedBotCorner) && $board->right != $humanCharacter) {
            $board->right = $board->bot_character;
            $placedCharacter = true;
          }
          if (in_array('bottom_left', $uncheckedBotCorner) && in_array('bottom_right', $uncheckedBotCorner) && $board->bottom != $humanCharacter) {
            $board->bottom = $board->bot_character;
            $placedCharacter = true;
          }
        }

      }

      if (!$placedCharacter) {
        // $board->{$emptyBoxes[rand(0, sizeof($emptyBoxes)-1)]} = $board->bot_character;
      }
    }

    $board->bot_turn = false;
    $board->save();

    return $board;
  }

  private static function checkBotTurn($request, $id) {
    $board = Board::findOrFail($id);

    if ($board->bot_turn && !$board->winner) $board = self::botPlay($board);

    return $board;
  }
}