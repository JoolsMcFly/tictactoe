@extends('layouts.app')

@section('content')
<div class="container" id="app">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    You are logged in!
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <board v-if="gameStarted" :opponent="opponent" :me="me"></board>
        </div>
        <div class="col-md-2">
            Players online:<br />
            <ul>
                <li v-for="player in players" @click="ping(player.id)">
                    @{{ player.name }}
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
