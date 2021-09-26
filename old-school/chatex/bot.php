<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/lib/functions.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/bots/discord/old-school/chatex/api_utils.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/bots/discord/old-school/config.php";

$user = $_GET['user'];
$text = $_GET['text'];

if (empty($user))
  exit();
  
if ($user == 'lesst') {
    $answerJson = chat_buyer($user, $text);
}

else if ($user == 'main') {
    $answerJson = chat_seller($user, $text);
}

else {
    $answerJson = chat_buyer($user, $text);
}
  
//file_put_contents($_SERVER['DOCUMENT_ROOT'].'/bots/discord/old-school/files/test.txt',$user .' - '.$text); 

//$answerJson = chat($user, $text);

if (!empty($answerJson)) {
    
    $arr = json_decode($answerJson,true);
    
    $text = $arr['text'];
    $next_state = $arr['next_state'];
    
    // отправляем сообщение пользователю
    
    $webhookurl = $xc['webhook_url'];

    $timestamp = date("c", strtotime("now"));

    

    $json_data = json_encode([
    // Сообщение
    "content" => $user.": ".$text,

    // Ник бота который отправляет сообщение
    "username" => "old_school",

    // URL Аватара.
    // Можно использовать аватар загруженный при создании бота, или указанный ниже
    "avatar_url" => "https://afisha.live/bots/discord/old-school/img/bot_avatar2.jpg",

    // Преобразование текста в речь
    "tts" => false,

    // Загрузка файла
    // "file" => "",

    // Массив Embeds
    "embeds" => [
    /*
        [
            // Заголовок
            "title" => "PHP - Send message to Discord (embeds) via Webhook",

            // Тип Embed Type, не меняем ничего.
            "type" => "rich",

            // Описание
            "description" => "Description will be here, someday",

            // Ссылка в заголовке
            "url" => "https://gist.github.com/Mo45/cb0813cb8a6ebcd6524f6a36d4f8862c",

            // Таймштамп, обязательно в формате ISO8601
            "timestamp" => $timestamp,

            // Цвет границы слева, в HEX
            "color" => hexdec( "3366ff" ),

            // Подпись и аватар в подвале
            "footer" => [
                "text" => "GitHub.com/Mo45",
                "icon_url" => "https://ru.gravatar.com/userimage/28503754/1168e2bddca84fec2a63addb348c571d.jpg?size=375"
            ],

            // Изображение внутри Embed
            "image" => [
                "url" => "https://ru.gravatar.com/userimage/28503754/1168e2bddca84fec2a63addb348c571d.jpg?size=600"
            ],

            // Превью (thumbnail)
            //"thumbnail" => [
            //    "url" => "https://ru.gravatar.com/userimage/28503754/1168e2bddca84fec2a63addb348c571d.jpg?size=400"
            //],

            // Автор
            "author" => [
                "name" => "krasin.space",
                "url" => "https://krasin.space/"
            ],

            // Дополнительные поля
            "fields" => [
                // Field 1
                [
                    "name" => "Поле #1",
                    "value" => "Значение #1",
                    "inline" => false
                ],
                // Field 2
                [
                    "name" => "Поле #2",
                    "value" => "Значение #2",
                    "inline" => true
                ]
                // И т.д...
            ]
        ]
        */
    ]

], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );


$ch = curl_init( $webhookurl );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
curl_setopt( $ch, CURLOPT_POST, 1);
curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt( $ch, CURLOPT_HEADER, 0);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec( $ch );
// Если что-то не работает, раскомментируйте строку ниже, и почитайте в чём беда :)
// echo $response;
curl_close( $ch );
    
// ---------------------------------------------------------------------------------
    
    // записываем на каком шаге находится пользователь
    /*
    $isUser = db_query("SELECT * FROM log2 WHERE user='".clearData($user)."' LIMIT 1");
    
    if ($isUser == false) {
        $add = db_query("INSERT INTO log2 (
        user,
        state,
        text,
        datetime) VALUES (
        '".clearData($user)."',
        '".$next_state."',
        '".clearData($text)."',
        '".time()."'
        )","i");
    }
    
    else {
        $upd = db_query("UPDATE log2 
        SET state='".$next_state."',
        text='".clearData($text)."',
        datetime='".time()."'
        ","u");
    }
    */
}




?>