@extends('layouts.app')

@section('content')
<div class="container-fluid" id="app" v-cloak>
    <div class="row">
        <div class="col-xs-4">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>
                <div class="panel-body">
                    <p>@{{ me.name }}, you have <span class="bg-danger"> lost <span v-text="me.losses"></span> time@{{me.losses != 1 ? 's' : ''}}</span> and <span class="bg-success">won <span v-text="me.wins"></span> time@{{ me.wins != 1 ? 's' : ''}}</span>.</p>
                    <div v-show="alert" class="alert alert-warning alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        @{{ alert }}
                    </div>
                    <div v-if="game_request" class="alert alert-warning alert-dismissible fade in pull-right" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        @{{ game_request.name }} wants to play with you on a @{{ game_request.grid_width + ' x ' + game_request.grid_width }} grid!
                        <button class="btn btn-primary" @click="acceptGameRequest">Accept</button>&nbsp;<button class="btn btn-danger" @click="refuseGameRequest">Refuse</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-4">
            <div class="panel panel-default">
                <div class="panel-heading">Game board<span v-if="gameStarted"> - You're playing against <strong :class="{pointer: opponent.id}" :title="opponent.id ? 'view player details' : ''" @click="showDetails(opponent.id)">@{{ opponent.name }}</strong></span></div>
                <div class="panel-body" v-if="!gameStarted">
                    <p>
                        <span v-show="players.length == 0">You can wait until someone logs in </span>
                        <span v-show="players.length > 0">You can click on Play icon on a player on the right</span>
                        <br />or <button class="btn btn-primary" v-show="!this.gameStarted" @click="newGameVsComp">play a game vs comp</button>
                    </p>
                </div>
                <board v-if="gameStarted" :opponent="opponent" :me="me" :starts_game="starts_game" :default_grid_width="default_grid_width" v-on:gameover="gameover" :playbackdata="playbackdata"></board>
            </div>
        </div>
        <div class="col-xs-4">
            Players online:<br />
            <ul>
                <li class="list-unstyled" v-for="player in players">
                    @{{ player.name }}<span class="glyphicon glyphicon-eye-open" title="view player details" @click="showDetails(player.id)" aria-hidden="true"></span>&nbsp;<span title="start a game with player" class="glyphicon glyphicon-play" aria-hidden="true" @click="showGameRequestModal(player.id)"></span>
                </li>
            </ul>
        </div>
    </div>
    <div class="modal fade" tabindex="-1" role="dialog" id="modal-player-details">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Player details</h4>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-hover">
                        <tbody>
                            @foreach (['name', 'wins', 'losses', 'total_time', 'total_moves'] as $field)
                            <tr>
                                <th>{{ ucfirst(str_replace('_', ' ', $field))}}</th>
                                <td v-text="player_details.{{$field}}"></td>
                            </tr>
                            @endforeach
                            <tr>
                                <th>Grid sizes</th>
                                <td v-html="player_details.size_played"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" tabindex="-1" role="dialog" id="modal-game-request">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Game request</h4>
                </div>
                <div class="modal-body">
                    <p>What grid size would you like to play?</p>
                    <select v-model="send_request_details.grid_width">
                        @for ($size=3; $size < 12; $size++)
                        <option value="{{$size}}">{{ "$size x $size" }}</option>
                        @endfor
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" @click="sendGameRequest">Send</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
