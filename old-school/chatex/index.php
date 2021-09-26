<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/bots/discord/old-school/chatex/api_utils.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/bots/discord/old-school/config.php";


header('Content-Type: application/json');

switch ($_GET['method']) {
    case 'auth':
        //https://afisha.live/bots/discord/old-school/chatex/index.php?method=auth&user=main
        $user = auth($_GET["user"]);
        echo $user;
        break;

    case 'token':
        //https://afisha.live/bots/discord/old-school/chatex/index.php?method=token&user=main
        $refresh_token = refresh_token($_GET["user"]);
        echo $refresh_token;
        break;

    case 'access':
        //https://afisha.live/bots/discord/old-school/chatex/index.php?method=access&user=main

        $refresh_token = get_auth($_GET["user"])['refresh_token'];
        $access_token = access_token(   $refresh_token, $_GET["user"] );
        echo  $access_token ;
        break;

    case 'transfer':
        //https://afisha.live/bots/discord/old-school/chatex/index.php?method=transfer&user=main&coin=btc&amount=0.001&recipient=lesst

        $access_token = get_auth($_GET["user"])['access_token'];

        $transfer     = transfer($_GET["coin"], $_GET["amount"], $_GET["recipient"] ,  $access_token)    ;
        echo  $transfer ;
        break;

    case 'seller':
        //https://afisha.live/bots/discord/old-school/chatex/index.php?method=seller&text=Привет

        $user =  $_GET["user"];
        $chat = chat_seller($user, $_GET["text"]);
        echo  $chat;
        break;

    case 'buyer':
        //https://afisha.live/bots/discord/old-school/chatex/index.php?method=buyer&text=Привет

        $user =  $_GET["user"];
        $chat = chat_buyer($user, $_GET["text"]);
        echo  $chat;
        break;
    default:
        break;
}








