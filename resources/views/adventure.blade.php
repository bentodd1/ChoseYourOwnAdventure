@extends('layouts.app')

@section('content')
    <div class="container">
    <h1>Choose your own adventure</h1>

    <div> <strong>{{ $message }}</strong></div>

    <form action="/adventure/store" enctype="multipart/form-data" method="post">
        @csrf
        <label for="response"></label>
        <input id="response"
               type="text"
               name="response"
               value=""
        >
        <div>
        <button type="submit" class="btn btn-primary">
            {{ __('Send') }}
        </button>
            <button type="submit" formaction="/adventure/create" class="btn btn-primary">
                {{ __('Start New') }}
            </button>
        </div>
        <img src={{$imageUrl}}  alt="">
    </form>
</div>
@endsection
