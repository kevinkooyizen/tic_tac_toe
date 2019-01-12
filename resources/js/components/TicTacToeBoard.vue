<template>
  <div>
    <h1>Tic Tac Toe</h1>
    <div>
      <button @click="startNewGame()" type="button" class="btn btn-primary">Start New Game</button>
    </div>
    <div class="clearfix">
      <button @click="initiateBotGame()" id="bot-game-button" type="button" class="btn btn-primary float-right mr-5" v-show='!bot_game && !ongoing_game'>Initiate Bot Game</button>
      <div id="bot-notification" class="d-none" v-show='bot_game'>
        <div class="float-right mr-5">
          <h3>You're playing against a bot&nbsp; <i class="fas fa-robot text-primary float-right"></i></h3>
          <br>
          <div v-if="!board.top_left && !board.top && !board.top_right && !board.left && !board.center && !board.right && !board.bottom_left && !board.bottom && !board.bottom_right" v-show='!ongoing_game && toggle'>
            <h4>Do you want to start first?</h4>
            <button @click='toggle = !toggle' type="button" class="btn btn-success">Yes</button>
            <button @click="startBotGame('bot_start')" type="button" class="btn btn-secondary">No</button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="!board.top_left && !board.top && !board.top_right && !board.left && !board.center && !board.right && !board.bottom_left && !board.bottom && !board.bottom_right" v-show='!toggle'>
      <h1>O starts</h1>
    </div>
    <div v-show='winner'>
      <h1>{{ winning_character }} Wins!</h1>
    </div>
    <table>
      <tr>
        <td @click="fillBox('top_left')"><h1 id="top-left"></h1></td>
        <td @click="fillBox('top')" class="vert"><h1 id="top"></h1></td>
        <td @click="fillBox('top_right')"><h1 id="top-right"></h1></td>
      </tr>
      <tr>
        <td @click="fillBox('left')" class="hori"><h1 id="left"></h1></td>
        <td @click="fillBox('center')" class="vert hori"><h1 id="center"></h1></td>
        <td @click="fillBox('right')" class="hori"><h1 id="right"></h1></td>
      </tr>
      <tr>
        <td @click="fillBox('bottom_left')"><h1 id="bottom-left"></h1></td>
        <td @click="fillBox('bottom')" class="vert"><h1 id="bottom"></h1></td>
        <td @click="fillBox('bottom_right')"><h1 id="bottom-right"></h1></td>
      </tr>
    </table>
  </div>
</template>

<script type="text/javascript">
  export default {
    data() {
      return {
        board: {
          id: '',
          top_left: '',
          top: '',
          top_right: '',
          left: '',
          center: '',
          right: '',
          bottom_left: '',
          bottom: '',
          bottom_right: '',
          bot_game: '',
          bot_turn: '',
        },
        toggle: true,
        winner: false,
        bot_game: false,
        ongoing_game: false,
        winning_character: null
      }
    },

    created() {
      this.fetchBoard();
    },

    methods: {
      fetchBoard(page_url) {
        page_url = page_url || '/api/boards'
        fetch(page_url)
          .then(res => res.json())
          .then(res => {
            this.board = res.data;
            $('#top-left')[0].innerHTML = this.board.top_left;
            $('#top')[0].innerHTML = this.board.top;
            $('#top-right')[0].innerHTML = this.board.top_right;
            $('#left')[0].innerHTML = this.board.left;
            $('#center')[0].innerHTML = this.board.center;
            $('#right')[0].innerHTML = this.board.right;
            $('#bottom-left')[0].innerHTML = this.board.bottom_left;
            $('#bottom')[0].innerHTML = this.board.bottom;
            $('#bottom-right')[0].innerHTML = this.board.bottom_right;
            if (!this.board.bot_game) {
              $('#bot-game-button').removeClass("d-none");
              $('#bot-notification').addClass("d-none");
            } else if (this.board.bot_game) {
              $('#bot-game-button').addClass("d-none");
              $('#bot-notification').removeClass("d-none");
            }
            this.checkWinner();
          })
          .catch(err => console.log(err));
      },
      fillBox(box) {
        this.board.clicked = box;
        fetch('api/boards/' + this.board.id, {
            method: 'put',
            body: JSON.stringify(this.board),
            headers: {
              'content-type': 'application/json'
            }
          })
          .then(res => res.json())
          .then(res => {
            this.fetchBoard();
            this.checkBotTurn();
            this.ongoing_game = true;
          })
          .catch(err => console.log(err));
      },
      checkWinner() {
        fetch('api/boards/' + this.board.id)
          .then(res => res.json())
          .then(res => {
            if (res.data.winner) {
              this.winner = true;
              this.winning_character = res.data.winner;
              this.toggle = true;
            } else if (res.data.winner == null){
              this.winner = false;
            }
          })
          .catch(err => console.log(err));
      },
      initiateBotGame() {
        this.board.clicked = 'bot_game';
        fetch('api/boards/' + this.board.id, {
            method: 'put',
            body: JSON.stringify(this.board),
            headers: {
              'content-type': 'application/json'
            }
          })
          .then(res => res.json())
          .then(res => {
            this.fetchBoard();
            this.bot_game = true;
          })
          .catch(err => console.log(err));
      },
      startBotGame(starter) {
        this.board.clicked = starter;
        fetch('api/boards/' + this.board.id, {
            method: 'put',
            body: JSON.stringify(this.board),
            headers: {
              'content-type': 'application/json'
            }
          })
          .then(res => res.json())
          .then(res => {
            this.fetchBoard();
          })
          .catch(err => console.log(err));
      },
      checkBotTurn() {
        this.board.action = 'check_bot_turn';
        fetch('api/boards/' + this.board.id, {
            method: 'put',
            body: JSON.stringify(this.board),
            headers: {
              'content-type': 'application/json'
            }
          })
          .then(res => res.json())
          .then(res => {
            this.fetchBoard();
          })
          .catch(err => console.log(err));
      },
      startNewGame() {
        fetch('api/boards/', {
            method: 'post',
            body: JSON.stringify(this.board),
            headers: {
              'content-type': 'application/json'
            }
          })
          .then(res => res.json())
          .then(res => {
            this.fetchBoard();
            this.bot_game = false;
            this.ongoing_game = false;
          })
          .catch(err => console.log(err));
      }
    }
  }
</script>