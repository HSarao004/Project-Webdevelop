<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <style>
        body {
            color: darkblue;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: url('uploads/bk_image.jpeg');
            background-size: cover;
            background-position: center;
        }

        .container {
            text-align: center;
        }

        .container h2 {
            margin-bottom: 20px;
        }

        .container button {
            padding: 10px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #FF0000;
            color: white;
        }

        .container button:hover {
            background-color: #FF3333;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Welcome to the Wrist Rotation Website </h2>
        <p>Please choose an option:</p>
        <button onclick="href='login.php'">Login</button>
        <button onclick="href='signup.php'">Sign Up</button>
    </div>
</body>

</html>
