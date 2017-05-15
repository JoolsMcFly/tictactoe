
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
        player2: {
            id: 10,
            name: 'dawg'
        },
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
        }
    },

    mounted() {
        this.listen();
    }
});
