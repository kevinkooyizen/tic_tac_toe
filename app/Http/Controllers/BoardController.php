<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Board;
use App\Http\Resources\Board as BoardResource;

class BoardController extends Controller {

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {

    $board = Board::getLatestUnfinishedBoard();

    return new BoardResource($board);
  }


  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
      /*$article = $request->isMethod('put') ? Article::findOrFail($request->article_id) : new Article;

      $article->id = $request->input('article_id');
      $article->title = $request->input('title');
      $article->body = $request->input('body');

      if($article->save()) {
          return new ArticleResource($article);
      }*/
      
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    $board = Board::checkWinner($id);

    // Return single article as a resource
    return new BoardResource($board);
  }

  public function update(Request $request, $id) {
    $board = Board::click($request, $id);

    // Return single article as a resource
    return new BoardResource($board);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
      /*// Get article
      $article = Article::findOrFail($id);

      if($article->delete()) {
          return new ArticleResource($article);
      }*/    
  }
}
