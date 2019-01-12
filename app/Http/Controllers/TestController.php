<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Board;

class TestController extends Controller {
  public function index(Request $request) {
    $board = Board::getLatestUnfinishedBoard();
    $request->action = 'check_bot_turn';
    $board = Board::click($request, $board->id);

    // Return single article as a resource
    dd ($board->getAttributes());
  }

}
