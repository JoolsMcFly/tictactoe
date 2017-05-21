<template>
    <div>
        <div class="panel-body">
            <cell v-for="cell in cells" :key="cell.index" :index="cell.index" :display="cell.display" @click.native="handleClick(cell.index)" :class="{clear: cell.index % grid_width == 0}"></cell>
        </div>
        <div class="panel-body">
            <button class="btn btn-primary btn-sm" :disabled="!vsComp" @click="incSize(1)">Inc size</button> - <button class="btn btn-primary btn-sm" :disabled="!vsComp" @click="incSize(-1)">Dec size</button>
            <p v-if="cur_player">{{cur_player.name}}'s turn</p>
            Number of moves: {{ moves.length }}<br />
            <button v-show="vsComp" class="btn btn-primary" @click="reset">Reset</button>
        </div>
        <div class="modal fade" :class="{in: isGameOver}" tabindex="-1" role="dialog" id="modal-game-over" style="display: none">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" @click="$emit('close')">&times;</span></button>
                        <h4 class="modal-title">Game over</h4>
                    </div>
                    <div class="modal-body">
                        <p v-text="gameOverMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal" @click="$emit('gameover')">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Cell from './Cell.vue';
    import axios from 'axios';
    import moment from 'moment';

            export default {
                props: ['opponent', 'me', 'starts_game', 'playbackdata', 'default_grid_width'],

                components: {Cell},

                data() {
                    return {
                        cells: [],
                        grid_width: this.default_grid_width,
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
                        } else {
                            this.cur_player = this.opponent
                        }
                    },

                    incSize(step) {
                        if ((this.grid_width <= 3 && step < 0) || (this.grid_width >= 10 && step > 0)) {
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
                    
                    handlePlayBackClick () {
                        this.lastCell = this.recordedMoves.shift()
                        this.applyMove()
                        if (!this.isGameOver) {
                            this.changePlayerTurn()
                        }
                        else {
                           $('#modal-game-over').modal('show') 
                        }
                    },

                    handleClick(index, user_triggered_click = true) {
                        if (this.isGameOver || this.cells[index].display !== '') { 
                            return false
                        }
                        if (this.isPlayingBack) {
                            return this.handlePlayBackClick()
                        }
                        if (this.moves.length === 0) {
                            this.time_start = moment()
                        }
                        if (this.notMyTurn(user_triggered_click)) {
                            return false
                        }
                        this.lastCell = index
                        this.applyMove()
                        if (!this.isGameOver) {
                            this.changePlayerTurn()
                        }
                        if (user_triggered_click !== false && !this.vsComp && !this.isPlayingBack) {
                            this.sendMoveToOpponent();
                        }
                        if (this.isGameOver) {
                            $('#modal-game-over').modal('show')
                            this.saveGame()
                        } else if (this.vsComp && this.cur_player.id != this.me.id) {
                            this.letComputerPlay()
                        }
                    },
                    
                    letComputerPlay () {
                        // Comp is stupid
                        let first_empty_cell = this.cells.find(c => c.display == '')
                        if (first_empty_cell !== undefined) {
                            this.handleClick(first_empty_cell.index)
                        }  
                    },

                    changePlayerTurn() {
                        if (this.cur_player.id == this.me.id) {
                            this.cur_player = this.opponent
                        } else {
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
                        if (!this.starts_game || this.isPlayingBack) {
                            return false
                        }
                        let data = {
                            elapsed: moment().diff(this.time_start, 'seconds'),
                            moves: this.moves,
                            first_player: this.starts_game ? this.me.id : this.opponent.id,
                            winner: this.cur_player.id,
                            looser: (this.cur_player.id === this.opponent.id ? this.me.id : this.opponent.id),
                            size: this.grid_width
                        }
                        axios.post('/game-save', data)
                                .then(this.notifySaved)
                                .catch(error => console.log(error))
                    },
                    
                    notMyTurn (user_triggered_click) {
                        return !this.vsComp && user_triggered_click && this.cur_player.id != this.me.id
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
                        return this.drawGame = this.moves.length === this.nb_cells
                    },

                    gameOverMessage() {
                        if (!this.cur_player) {
                            return {}
                        }
                        if (this.drawGame) {
                            return "It's a draw game!"
                        }
                        return this.cur_player.id == this.me.id ? 'YOU WIN!' : this.cur_player.name + " WINS!"
                    },
                    
                    isPlayingBack () {
                        return this.playbackdata !== null
                    }
                }
            }
</script>
