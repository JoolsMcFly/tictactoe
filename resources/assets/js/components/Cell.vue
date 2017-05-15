<template>
    <div class="cell">{{ display }}</div>
</template>

<script>
    export default {
        props: ['index', 'display'],
    }
</script>
<style>
    .cell {
        width: 30px;
        height: 30px;
        border: 1px solid black;
        margin: 5px;
        padding: 5px;
        float: left;
        padding-left: 9px;
    }
    .clear {
        clear: both
    }
</style>