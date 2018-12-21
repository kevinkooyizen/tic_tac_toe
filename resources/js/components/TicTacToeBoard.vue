<template>
  <div>
    <h1>Tic Tac Toe</h1>
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
        }, 
        edit: false
      }
    },

    created() {
      this.fetchBoard();
    },

    methods: {
      fetchBoard(page_url) {
        let vm = this;
        page_url = page_url || '/api/boards'
        fetch(page_url)
          .then(res => res.json())
          .then(res => {
            console.log(res.data);
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
            console.log(res.data);
            this.fetchBoard();
          })
          .catch(err => console.log(err));
      }
      /*makePagination(meta, links) {
        let pagination = {
          current_page: meta.current_page,
          last_page: meta.last_page,
          next_page_url: links.next,
          prev_page_url: links.prev,
        };

        this.pagination = pagination;
      },
      deleteArticle(id) {
        if (confirm('Are you sure you want to delete this article?')) {
          fetch(`api/article/${id}`, {
            method: 'delete'
          })
          .then(res => res.json())
          .then(data => {
            alert('Article Deleted');
            this.fetchArticles();
          })
          .catch(err => console.log(err));
        }
      },
      addArticle(id) {
        if (this.edit === false) {
          // Add 
          fetch('api/article', {
            method: 'post',
            body: JSON.stringify(this.article),
            headers: {
              'content-type': 'application/json'
            }
          })
          .then(res => res.json())
          .then(data => {
            this.article.title = '';
            this.article.body = '';
            alert('Article Created');
            this.fetchArticles();
          })
          .catch(err => console.log(err));
        } else {
          fetch('api/article', {
            method: 'put',
            body: JSON.stringify(this.article),
            headers: {
              'content-type': 'application/json'
            }
          })
          .then(res => res.json())
          .then(data => {
            this.article.title = '';
            this.article.body = '';
            alert('Article Updated');
            this.fetchArticles();
          })
          .catch(err => console.log(err));
        }
      },
      editArticle(article) {
        this.edit = true;
        this.article.id = article.id;
        this.article.article_id = article.id;
        this.article.title = article.title;
        this.article.body = article.body;
      }*/
    }
  }
</script>