@extends('layouts.main')
@section('menu-item')
    Setup
@endsection
@section('title')
    Edit Environment
@endsection
@section('content')
    <livewire:environment.edit :id="$id">
@endsection
