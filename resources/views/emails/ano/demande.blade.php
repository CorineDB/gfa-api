<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config("app.name") }}</title>

<style type="text/css">

    .myBody{
        box-sizing: border-box;
        font-family: 'Nunito', sans-serif;
        font-family: 'Segoe UI','Roboto',Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';
        background-color: #edf2f7;
        margin: 0;
        padding: 0;
        width: 100%;
    }

    .myStyle{
        box-sizing: border-box;
        font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';
        padding: 25px 0;
        text-align: center;
        margin: 0;
        /* padding: 23px;background: #edf2f7; box-sizing: border-box; */
    }

    .content{
        box-sizing: border-box;
        font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';
        background-color: #ffffff;
        border-color: #e8e5ef;
        border-radius: 2px;
        border-width: 1px;
        margin: 0 auto;
        padding: 0;
        width: 570px;
        color: #222;
    }

    .contenu{
        box-sizing: border-box;
        font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';
        padding: 32px;
        font-size: 16px;
        line-height: 1.5em;
        margin-top: 0;
        text-align: left;
        color: #222;
    }

    .myFooter{
        box-sizing: border-box;
        font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';
        margin: 0 auto;
        padding: 0;
        text-align: center;
        width: 570px;
        max-width: 100vw;
        padding: 32px;
        line-height: 1.5em;
        margin-top: 0;
        color: #b0adc5;
        font-size: 12px;
        text-align: center;
    }

</style>

</head>
<body class="myBody">

<center>
    <h2 class="myStyle">
        <a href="javascript:void(0)">{!! $details["subject"] !!}</a>
    </h2>
</center>

<div class="content">

    <div class="contenu" style="color: #718096;">

        <p>
            Bonjour monsieur/madame
        </p>

        <p>
            La durée de validité de votre mot de passe est expiré veuillez change
        </p>        

    </div>
</div>


<center>
    <p class="myStyle" style="color: #b0adc5;">
        &copy; 2024 {{ config("app.name") }}. All rights reserved.
    </p>
</center>

</body>
</html>
