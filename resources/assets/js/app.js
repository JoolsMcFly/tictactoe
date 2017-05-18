
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
Vue.component('modal', {
    template: '#modal-player-details',
    props: ['player_details']
});

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

        showPlayerDetails: false,

        player_details: {},

        alert: '',

        players: [],

        game_request: null
    },
    methods: {
        gameover() {
            this.gameStarted = false
        },

        listen() {
            Echo.private('App.User.' + this.me.id)
                    .listen('GameRequestEvent', e => {
                        if (this.gameStarted) {
                            axios.post('/new-game-refused/' + e.id, {reason: 'Game request refused. Player is busy... Playing!'})
                        } else {
                            this.game_request = this.players.find(p => p.id == e.id)
                        }
                    })
                    .listen('GameRefusedEvent', data => {
                        this.alert = data.reason
                    })
                    .listen('GameStartedEvent', data => {
                        this.gameStarted = true
                        this.opponent = data.user
                        this.cur_player = data.starts_game ? this.me : this.opponent
                        this.starts_game = data.starts_game
                    })
                    .listen('GameOverEvent', data => {
                        this.me = data
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

        acceptGameRequest() {
            axios.post('/new-game-accepted/' + this.game_request.id).then(() => {
                this.game_request = null
            }).catch(e => {
                console.log('Error when accepting game request')
                console.log(e)
            })
        },

        refuseGameRequest() {
            axios.post('/new-game-refused/' + this.game_request.id, {reason: "Game request refused because player doesn't feel like playing right now."}).then(this.game_request = null)
        },

        newGameVsComp() {
            this.opponent = {id: null, name: "Comp"}
            this.cur_player = this.me
            this.starts_game = true
            this.gameStarted = true
        },

        showDetails(user_id) {
            axios.post('/player/' + user_id).then(e => {
                this.player_details = e.data
                let size_played = []
                for (size in e.data.size_played) {
                    size_played.push(size + " x " + size + ": " + e.data.size_played[size] + " times")
                }
                this.player_details.size_played = size_played.join("<br />")
                this.showPlayerDetails = true
            })
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
