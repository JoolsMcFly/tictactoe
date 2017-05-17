
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('board', require('./components/Board.vue'));

const app = new Vue({
    el: '#app',

    data: {
        opponent: {
            id: null,
            name: 'Comp'
        },
        me: {
            id: null,
            name: "me"
        },

        gameStarted: false,
        
        starts_game: false,

        players: []
    },

    methods: {
        listen() {
            Echo.private('App.User.' + this.me.id)
                    .listen('GameRequestEvent', e => {
                        console.log('game request from ' + e.name + "(" + e.id + ")")
                        axios.post('/new-game-accepted/' + e.id).then(() => {
                            console.log('Sent game accepted to ' + e.id);
                        })
                    })
                    .listen('GameStartedEvent', data => {
                        console.log("Received game started event")
                        console.log(data);
                        this.gameStarted = true
                        this.opponent = data.user
                        this.cur_player = data.starts_game ? this.me : this.opponent
                        this.starts_game = data.starts_game
                    });
            Echo.join('tictactoe')
                    .here(users => {
                        this.players = users.filter(u => u.id != this.me.id)
                    })
                    .joining(user => {
                        this.players.push(user)
                    })
                    .leaving(user => {
                        this.players = this.players.filter(u => u.id != user.id)
                        if (user.id == this.opponent.id) {
                            this.gameStarted = false
                            this.opponent = {id: null, name: 'Comp'}
                        }
                    });
        },

        ping(user_id) {
            axios.post('/new-game-request/' + user_id).then(e => {
            }).catch(e => {
                console.log('game request error');
                console.log(e);
            });
        }
    },

    mounted() {
        this.me = window.ttt_user
        this.listen()
    }
});
