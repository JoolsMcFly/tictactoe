
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

        players: []
    },

    methods: {
        listen() {
            Echo.channel('user-activity')
                    .listen('UserLoginEvent', (e) => this.players.push(e));
            Echo.channel('user-activity')
                    .listen('UserLogoutEvent', (e) => {
                        this.players = this.players.filter(p => p.id != e.id)
                    });
            Echo.private('App.User.' + this.me.id)
                    .listen('GameRequestEvent', e => {
                        console.log('game request')
                        console.log(e)
                        axios.post('/new-game-accepted/' + e.id).then( () => {
                            console.log('game accepted sent');
                        })
                    })
                    .listen('GameStartedEvent', user => {
                        this.gameStarted = true
                        this.opponent = user;
                        console.log("game started")
                    })
        },

        ping(user_id) {
            axios.post('/new-game-request/' + user_id).then(e => {
                console.log('game request return');
                console.log(e);
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
