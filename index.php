<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
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
            background-color: #4CAF50;
            color: white;
        }

        .container button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Welcome!</h2>
        <p>Please choose an option:</p>
        <button onclick="window.location.href='login.php'">Login</button>
        <button onclick="window.location.href='signup.php'">Sign Up</button>
    </div>
</body>

</html>
