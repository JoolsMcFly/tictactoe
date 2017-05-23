import Vue from 'vue';

import Cell from '../components/Cell.vue';

describe('Cell.vue', () => {
    it('shows the right display', () => {
        const Ctor = Vue.extend(Cell)
        const vm = new Ctor({propsData: {
                index: 1,
                display: 'X'
            }}).$mount()
        expect(vm.$el.textContent).toBe('X')
    })
})