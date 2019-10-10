<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        * {
            box-sizing: border-box
        }
        body {
            background: rgba(0, 0, 0, .02);
            padding: 20px;
        }
        .main {
            max-width: 100%;
            width: 1000px;
            margin: auto;
            background: #fff;
            padding:40px 40px 40px;
            box-shadow: 0 0 40px rgba(0, 0, 0, 0.03);
            border: 1px solid #f1f1f1;
            background: rgba(255, 255, 255, .95);
            position: relative;
        }
        .main::before {
            content: '';
            display: block;
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            background-image: url(https://cdn.pixabay.com/photo/2018/04/18/18/56/check-3331239_960_720.png);
            z-index: -1;
        }
        h1 {
            color: #3c4858;
            margin: 0;
            text-align: center;
        }
        p {
            font-size: 14px;
            font-family: 'Open Sans','Arial',Helvetica,sans-serif,sans-serif;
            color: #3c4858;
            line-height: 21px;
            text-align: justify;
        }
        .btn {
            box-shadow: 0 3px 1px -2px rgba(0,0,0,.2), 0 2px 2px 0 rgba(0,0,0,.14), 0 1px 5px 0 rgba(0,0,0,.12);
            background-color: #3f51b5;
            color: #fff;
            padding: .8rem 1rem;
            border-radius: 7px;
            margin: 5px auto;
            text-decoration: none;
            display: block;
            width: 150px;
            text-align: center;
        }
        .logo {
            filter: invert(75%);
            max-width: 100%;
            width: 200px;
            margin: 5rem 20px 1rem ;
            display: block;
        }
        .link {
            text-align: center;
        }
        .link a{
            text-align: center;
            text-decoration: none;
            color: #348eda;
            font-weight: 400;
            text-decoration: none;
            font-size: 12px;
            padding: 0 5px;
        }
        hr {
            width: 500px;
            opacity: .1;
            max-width: 100%;
        }
    </style>
</head>
<body>
    <img class="logo" src="https://www.sanjose.gub.uy/wp-content/themes/Savvy/img/logo_san_jose_gris.png" alt="" srcset="">
    <div class="main">
        <h1><?php echo $title;?> </h1>
        <p>
            <?php echo $message;?>
            
        </p>
        <br>
        <hr>
        <br>
        <a class="btn" href="<?php echo $link;?>"><?php echo $nameLink;?></a>
    </div>
    <div class="link">
        <a href="">Web</a>
        <a href="">Twitter</a>
        <a href="">Facebook</a>
        <a href="">LinkedIn</a>
    </div>
       
</body>
</html>