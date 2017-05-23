import Vue from 'vue';

import Cell from '../components/Cell.vue';
import Board from '../components/Board.vue';
import Echo from 'laravel-echo'

        window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'd86b5266e48a5ff6a437',
    cluster: 'eu',
    encrypted: true
});

describe('Board.vue', () => {
    it("mounts and shows it is my turn", () => {
        const Constructor = Vue.extend(Board)
        const vm = new Constructor({propsData: {
                opponent: {
                    id: null,
                    name: 'Comp'
                },
                me: {
                    id: 1,
                    name: 'Jools'
                },
                starts_game: true,
                playbackdata: null,
                default_grid_width: 3
            }}).$mount()
        Vue.nextTick(() => {
            expect(vm.$el.textContent).toContain("Jools's turn");
        })
    })
    
    it("mounts and shows it is my turn", () => {
        const Constructor = Vue.extend(Board)
        const vm = new Constructor({propsData: {
                opponent: {
                    id: null,
                    name: 'Comp'
                },
                me: {
                    id: 1,
                    name: 'Jools'
                },
                starts_game: false,
                playbackdata: null,
                default_grid_width: 3
            }}).$mount()
        Vue.nextTick(() => {
            expect(vm.$el.textContent).toContain("Comp's turn");
        })
    })
})