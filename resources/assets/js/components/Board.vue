<template>
    <div class="panel panel-default">
        <div class="panel-heading">Game board - <button class="btn btn-primary btn-sm" @click="incSize(1)">Inc size</button> - <button class="btn btn-primary btn-sm" @click="incSize(-1)">Dec size</button></div>
        <div class="panel-body">
            <cell v-for="cell in cells" :key="cell.index" :index="cell.index" :display="cell.display" @click.native="handleClick(cell.index)" :class="{clear: cell.index % grid_width == 0}"></cell>
            <p class="pull-right" v-if="isGameOver">VICTORY!</p>
            <p class="pull-right" v-if="drawGame">DRAW!</p>
        </div>
        <div class="panel-body">
            Number of moves: {{ moves.length }}<br />
            <button class="btn btn-primary" @click="reset">Reset</button>
        </div>
    </div>
</template>

<script>
    import Cell from './Cell.vue';
    import axios from 'axios';
    import moment from 'moment';

    export default {
        props: ['player2'],

        components: {Cell},

        data() {
            return {
                cells: [],
                firstUserTurn: true,
                grid_width: 3,
                nb_cells: 9,
                moves: [],
                drawGame: false,
                lastCell: -1,
                time_start: moment()
            }
        },

        mounted() {
            this.initBoard()
        },

        methods: {
            initBoard() {
                this.cells = []
                this.nb_cells = this.grid_width * this.grid_width
                for (let i = 0; i < this.nb_cells; i++) {
                    this.cells.push({
                        index: i,
                        display: ''
                    });
                }
            },

            incSize(step) {
                if ((this.grid_width <= 3 && step < 0) || (this.grid_width >= 9 && step > 0)) {
                    return false
                }
                this.grid_width += step
                this.nb_cells = this.grid_width * this.grid_width
                this.reset()
            },

            reset() {
                this.firstUserTurn = true
                this.moves = []
                this.drawGame = false
                this.lastCell = -1
                this.initBoard()
            },

            handleClick(index) {
                if (this.isGameOver) {
                    return false
                }
                this.moves.push(index)
                this.lastCell = index
                let cell = this.cells[index]
                cell.display = this.firstUserTurn ? 'X' : 'O'
                this.firstUserTurn = !this.firstUserTurn
                Vue.set(this.cells, index, cell)
                if (this.moves.length == this.nb_cells && !this.isGameOver) {
                    this.drawGame = true
                    this.saveGame()
                } else if (this.isGameOver) {
                    this.saveGame()
                }
            },

            saveGame() {
                let data = {
                    elapsed: moment().diff(this.time_start, 'seconds'),
                    moves: this.moves,
                    winner: this.lastCell % 2 == 0 ? "p1" : "p2",
                    size: this.grid_width
                }
                axios.post('/game-save', data)
                        .then(this.notifySaved)
                        .catch(error => console.log(error))
            },

            notifySaved() {

            },

            winsRow() {
                const row = Math.floor(this.lastCell / this.grid_width)
                let cells = this.cells.slice(row * this.grid_width, (row + 1) * this.grid_width)
                return this.winsAllCells(cells)
            },

            winsColumn() {
                const col = this.lastCell % this.grid_width
                let cells = []
                for (let i = 0; i < this.grid_width; i++) {
                    cells.push(this.cells[i * this.grid_width + col])
                }
                return this.winsAllCells(cells)
            },

            winsDiagonal() {
                let cells = []
                if (this.lastCell % (this.grid_width + 1) === 0) {
                    // check top-left->bottom-right diagonal
                    for (let i = 0; i < this.grid_width; i++) {
                        cells.push(this.cells[i * (this.grid_width + 1)])
                    }
                    if (this.winsAllCells(cells)) {
                        return true
                    }
                }
                if (this.lastCell % (this.grid_width - 1) === 0) {
                    // check top-right->bottom-left diagonal
                    cells = []
                    for (let i = 0; i < this.grid_width; i++) {
                        cells.push(this.cells[(i + 1) * (this.grid_width - 1)])
                    }
                    if (this.winsAllCells(cells)) {
                        return true
                    }
                }

                return false
            },

            winsAllCells(cells) {
                const value_to_check = this.cells[this.lastCell].display
                return cells.filter(function (cell) {
                    return cell.display === value_to_check
                }, this).length === this.grid_width
            }
        },

        computed: {
            isGameOver() {
                if (this.lastCell === -1 || this.moves.length < this.grid_width * 2 - 1) {
                    return false
                }
                if (this.winsDiagonal() || this.winsRow() || this.winsColumn()) {
                    return true
                }
                return false
            }
        }
    }
</script>
