@extends('layouts.app');

<div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-1" id="store-list" style="display: none">
        @livewire('store-list')
    </div>
    <div class="d-flex align-items-center" id="loading">
        <div id="spinner" class="spinner-border ml-auto text-warning m-auto p-6 m-6" role="status" aria-hidden="true"></div>
    </div>    
</div>
<script>
    window.onload = function () {
        document.getElementById('store-list').style.display = 'block';
        document.getElementById('loading').style.display = 'none';
        document.getElementById('spinner').style.display = 'none';
    }   
</script>