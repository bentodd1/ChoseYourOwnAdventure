<!DOCTYPE html>
<html>
<head>
    <title>The Mind Nexus</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <style>
        .centered-image {
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4; /* Neutral background */
            color: #333;
        }
        .container {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
        }
        .btn-primary {
            background-color: #007bff; /* Accent color */
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>The Mind Nexus</h1>

            <!-- Multiline Text Blob -->
            <p>
                {{ $message }}
            </p>

            <!-- Image -->
            <img src={{ $imageUrl }} alt="Your Image" class="centered-image">

            <!-- Dynamic Buttons -->
            <div class="d-flex flex-column">
                @foreach($options as $option)
                    <form  method="POST">
                        @csrf
                        <input id="response"
                               type="hidden"
                               name="response"
                               value="{{ $option }}"
                        >
                        <button type="submit" formaction="/adventure/store" class="btn btn-primary mb-2 w-100">{{ $option }}</button>
                    </form>
                @endforeach
                    <form  method="POST">
                        @csrf
                        <button type="submit" formaction="/adventure/create" class="btn btn-primary mb-2 w-100"> Start Over</button>
                    </form>
                </div>
        </div>
    </div>
</div>

</body>
</html>
