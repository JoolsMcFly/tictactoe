<template>
    <div class="panel panel-default">
        <div class="panel-heading">Game board - <button class="btn btn-primary btn-sm" :disabled="!vsComp" @click="incSize(1)">Inc size</button> - <button class="btn btn-primary btn-sm" :disabled="!vsComp" @click="incSize(-1)">Dec size</button></div>
        <div class="panel-body">
            <cell v-for="cell in cells" :key="cell.index" :index="cell.index" :display="cell.display" @click.native="handleClick(cell.index)" :class="{clear: cell.index % grid_width == 0}"></cell>
            <p class="pull-right" v-if="isGameOver">{{ cur_player.name }} WINS!</p>
            <p class="pull-right" v-if="drawGame">DRAW!</p>
        </div>
        <div class="panel-body">
            <p>{{ me.name }} is playing against {{ opponent.name }}</p>
            <p v-if="cur_player">{{cur_player.name}}'s turn</p>
            Number of moves: {{ moves.length }}<br />
            <button v-show="vsComp" class="btn btn-primary" @click="reset">Reset</button>
        </div>
    </div>
</template>

<script>
    import Cell from './Cell.vue';
    import axios from 'axios';
    import moment from 'moment';

    export default {
        props: ['opponent', 'me', 'starts_game'],

        components: {Cell},

        data() {
            return {
                cells: [],
                grid_width: 3,
                nb_cells: 9,
                moves: [],
                drawGame: false,
                lastCell: -1,
                time_start: moment(),
                cur_player: null
            }
        },

        mounted() {
            this.initBoard()
            Echo.private('App.User.' + this.me.id)
                    .listen('NewMoveEvent', e => {
                        this.handleClick(e.move.index, false)
                    })
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
                if (this.starts_game) {
                    this.cur_player = this.me
                }
                else {
                    this.cur_player = this.opponent
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
                this.moves = []
                this.drawGame = false
                this.lastCell = -1
                this.cur_player = null
                this.initBoard()
            },

            handleClick(index, user_click = true) {
                if (this.isGameOver) {
                    return false
                }
                if (user_click && this.cur_player.id != this.me.id) {
                    return false
                }
                this.lastCell = index
                this.applyMove()
                if (!this.isGameOver) {
                    this.changePlayerTurn()
                }
                if (user_click !== false && this.opponent.id !== null) {
                    this.sendMoveToOpponent();
                }
                if (this.moves.length == this.nb_cells && !this.isGameOver) {
                    this.drawGame = true
                    this.saveGame()
                } else if (this.isGameOver) {
                    this.saveGame()
                }
            },
            
            changePlayerTurn() {
                if (this.cur_player.id == this.me.id) {
                    this.cur_player = this.opponent
                }
                else {
                    this.cur_player = this.me
                }
            },

            applyMove() {
                let cell = this.cells[this.lastCell]
                cell.display = this.moves.length % 2 === 0 ? 'X' : '0'
                Vue.set(this.cells, this.lastCell, cell)
                this.moves.push(this.lastCell)
            },

            saveGame() {
                let data = {
                    elapsed: moment().diff(this.time_start, 'seconds'),
                    moves: this.moves,
                    winner: this.cur_player.name,
                    size: this.grid_width
                }
                axios.post('/game-save', data)
                        .then(this.notifySaved)
                        .catch(error => console.log(error))
            },

            notifySaved() {

            },

            sendMoveToOpponent() {
                axios.post('/new-move/' + this.opponent.id, {index: this.lastCell, display: this.cells[this.lastCell].display})
                        .then(e => {
                            console.log("Move sent");
                            console.log(e);
                        });
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
            vsComp() {
                return this.opponent.id == null
            },

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
