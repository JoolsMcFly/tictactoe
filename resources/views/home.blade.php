@extends('layouts.app')

@section('content')
<div class="container" id="app" v-cloak>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    <p>@{{ me.name }}, you have lost <span v-text="me.losses"></span> time@{{me.losses != 1 ? 's' : ''}} and won <span v-text="me.wins"></span> time@{{ me.wins != 1 ? 's' : ''}}.</p>
                    <div v-show="alert" class="alert alert-warning alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        @{{ alert }}
                    </div>
                    <div v-if="game_request" class="alert alert-warning alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        @{{ game_request.name }} wants to play with you!
                        <button class="btn btn-primary" @click="acceptGameRequest">Accept</button>&nbsp;<button class="btn btn-danger" @click="refuseGameRequest">Refuse</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <button class="btn btn-primary" v-show="!this.gameStarted" @click="newGameVsComp">Me Vs Comp</button>
            <board v-if="gameStarted" :opponent="opponent" :me="me" :starts_game="starts_game" v-on:gameover="gameover"></board>
        </div>
        <div class="col-md-2">
            Players online:<br />
            <ul>
                <li class="list-unstyled" v-for="player in players">
                    @{{ player.name }}<span class="glyphicon glyphicon-eye-open" title="view player details" @click="showDetails(player.id)" aria-hidden="true"></span>&nbsp;<span title="start a game with player" class="glyphicon glyphicon-play" aria-hidden="true" @click="ping(player.id)"></span>
                </li>
            </ul>
        </div>
    </div>
    <modal v-if="showPlayerDetails" @close="showPlayerDetails = false" :player_details="player_details"></modal>
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
</div>
@endsection
