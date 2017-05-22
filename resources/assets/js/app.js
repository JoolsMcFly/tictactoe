require('./bootstrap');

window.Vue = require('vue');

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

        showPlayerDetails: false,

        player_details: {},

        alert: '',

        players: [],

        game_request: null,

        default_grid_width: 3,

        send_request_details: {user_id: null, grid_width: 3},

        playbackdata: null,
        
        video_mode: false,
        
        games: []
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
                            this.game_request.grid_width = parseInt(e.grid_width) || 3
                        }
                    })
                    .listen('GameRefusedEvent', data => {
                        this.alert = data.reason
                    })
                    .listen('GameStartedEvent', data => {
                        this.playbackdata = null
                        if (this.game_request) {
                            this.default_grid_width = parseInt(this.game_request.grid_width)
                        }
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
                this.default_grid_width = parseInt(this.game_request.grid_width)
                this.game_request = null
            }).catch(e => {
                console.log('Error when accepting game request')
                console.log(e)
            })
        },

        refuseGameRequest() {
            axios.post('/new-game-refused/' + this.game_request.id, {reason: "Game request refused because player doesn't feel like playing right now."}).then(this.game_request = null)
        },

        showGameRequestModal(user_id) {
            this.send_request_details = {user_id: user_id}
            $('#modal-game-request').modal('show')
        },

        sendGameRequest() {
            this.default_grid_width = parseInt(this.send_request_details.grid_width)
            axios.post('/new-game-request/' + this.send_request_details.user_id, {
                grid_width: this.send_request_details.grid_width
            }).then(e => {
            }).catch(e => {
                console.log('game request error');
                console.log(e);
            });
        },

        newGameVsComp() {
            this.opponent = {id: null, name: "Comp"}
            this.cur_player = this.me
            this.starts_game = true
            this.gameStarted = true
            this.playbackdata = null
        },

        replayGame(game) {
            this.gameStarted = false
            const comp =  {id: null, name: 'Comp'}
            if (game.winner === null) {
                game.winner = comp
            }
            else if (game.looser === null) {
                game.looser = comp
            }

            if (game.winner.id == this.me.id) {
                this.opponent = game.looser
            }
            else {
                this.opponent = game.winner
            }
            if (game.first_player == this.me.id) {
                this.cur_player = this.me.id
                this.starts_game = true
            }
            else {
                this.cur_player = this.opponent.id
                this.starts_game = false
            }
            this.default_grid_width = game.extra.size
            this.playbackdata = JSON.parse(JSON.stringify(game.extra))
            this.gameStarted = true
        },

        showDetails(user_id) {
            if (user_id === null) {
                return false
            }
            axios.post('/player/' + user_id).then(e => {
                this.player_details = e.data
                let size_played = []
                for (size in e.data.size_played) {
                    size_played.push(size + " x " + size + ": " + e.data.size_played[size] + " times")
                }
                this.player_details.size_played = size_played.join("<br />")
                $('#modal-player-details').modal('show')
            })
        }
    },

    mounted() {
        this.me = window.ttt_user
        this.games = window.games
        this.listen()
    }
});
