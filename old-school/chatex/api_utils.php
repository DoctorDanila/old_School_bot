<?php

function get_buttons($state) {
    $buttons = array();
    
    $a = db_query("SELECT state, button FROM buttons WHERE state='".$state."' OR state='all' ORDER BY sort");

    
    return $a;
}

function chat_seller($user, $text)
{
    $log = get_log($user);

    //Заранее известные команды
   [ $text, $state,  $buttons ] = s_event($text);
    if ($state != null){
        set_field_log($user, 'state' , $state );
        
        $buttons = get_buttons($state);
        
        $arr = array(
         'text' => $text,
         'next_state' => $state, 
         'button' => $buttons
        );
        
       return json_encode($arr);
    }else{

    $state  = get_state($log);
    $answer = answers_bd($state);

    $contract = get_contract( $log['contract'] );
    set_field_log($user, 'contract' , $contract['id'] );


    //Проверка текста пользователя
    switch (validator($state, $text,  $contract , $user)) {
        case 'ok' :
            $state    = $answer['next_state'];

            //Текст следующего шага
            $buttons =  get_buttons($state);
            $text    =  answers_bd($state)['answer'];
            break;

        case 'ini' :
            //Статус сбрасываем на начальный
            $state    = 'home';
//            $answer   = answers_bd('ini');


            //Текст следующего шага
            $buttons =  get_buttons($state);
            $text  =  answers_bd($state)['answer'];
            break;

        case 'err' :
            //Статус не меняется
            $state    = $state;

            //Текст следующего шага
            $buttons =  get_buttons($state);
            $text    =  answers_bd($state)['answer_err'];
            break;

        default:
            $text  = 'Неизвестный ответ validator';
            break;
    }

    $text = set_replace($text, $contract);
    set_field_log($user, 'state' , $state );
    
    $arr = array(
      'text' => $text,
      'next_state' => $state, 
      'button' => $buttons
    );
        
    return json_encode($arr);
    }
}

function chat_buyer($user, $text)
{
    $user_test  = 'main';

    $log = get_log($user_test);

    //Заранее известные команды
    [ $text, $state,  $buttons ] = s_event($text);
    if ($state != null){
        set_field_log($user, 'state' , $state );

        $buttons = get_buttons($state);

        $arr = array(
            'text' => $text,
            'next_state' => $state,
            'button' => $buttons
        );

        return json_encode($arr);
    }else{

        $state    = get_state($log);
        $buttons  = get_buttons($state);
        $answer   = answers_bd($state);
        $contract = get_contract( $log['contract'] );

        if ( $contract['s_accept'] == false ){
            $text     =  'Ожидайте создания контракта';
        }elseif($contract['pay_b'] == '' ){
            $text = mb_strtolower( $text );
            if  ($text == "да") {
                pay_user($user );
                set_field_contract($contract['id'], 'pay_b' , "T"  );
                $text     =  'Оплата успешно совершена ' . $contract['amount'] ." btc заморожено на счету" ;
            }else{
                $text     =  'Для оплаты, скажите ДА';
            }
        }elseif($contract['give_s'] == '' ){
            $text     =  'Подождите отправку товара ' . $contract['product'] . " в STEAM"  ;
        }elseif($contract['b_accept'] == '0' ){

            if  ($text == "да") {
                set_field_contract($contract['id'], 'b_accept' , "T"  );
                $text     =  'Отлично!' ;
            }else{
                $text     =  'Товар отправлен, если вы получили товар, скажите "Да", если пользователь не хочет отправлять товар, скажите "Арбитраж"';;
            }

        }elseif($contract['b_accept'] == 'T' ){
            $text     =  'Отлично, сделка завершена!';
        }else{
            $text     =  'Ошибка';

        }
        //Проверка текста пользователя

        $arr = array(
            'text' => $text,
            'next_state' => $state,
            'button' => $buttons
        );

        return json_encode($arr);
    }
}



function set_replace($text, $contract){
    $contract = get_contract( $contract['id'] );
    $text = str_replace('saller', $contract['saller'], $text);
    $text = str_replace('amount', $contract['amount'], $text);
    $text = str_replace('product', $contract['product'], $text);
    $text = str_replace('buyer', $contract['buyer'], $text);

    return $text ;

}
function s_event($text){
    $text = mb_strtolower( $text );

    switch ($text) {
        case 'выход' :
            $answer   = answers_bd('exit');
            $state    = $answer['next_state'];
            $text     = $answer['answer'];
            $buttons  = $answer['buttons'];
            break;

        case 'помощь' :
            $answer   = answers_bd('help');
            $state    = $answer['next_state'];
            $text     = $answer['answer'];
            $buttons  = $answer['buttons'];
            break;

        case 'арбитраж' :
            //Статус сбрасываем на начальный
            $text     = 'Для арбитража, необходимо ввести данные от Steam Аккаунта https://afisha.live?contract=7 и система автоматически проверит историю сделок' ;
            break;

        default:
            $text = $text;
            $state    = null;
            $buttons  = null;
            break;
    }


    return  [$text, $state,  $buttons ];
}

function validator($state, $text, $contract , $user)
{
    $id = $contract['id'];
    $text = mb_strtolower( $text );

    switch ($state) {
        case 'ini':
            $res = 'ok';
            break;

        case 'home' :
            if ($text == 'да'){
                $res = 'ok';
                set_field_contract($id, 'seller' ,  $user );
            }elseif($text == 'нет'){
                $res = 'ini'; //СБРОС Статуса
            }else{
                $res = 'err';
            }
            break;

        case 'get_partner' :
            if ($text == 'lesst'){
                set_field_contract($id, 'buyer' , $text  );
                $res = 'ok';
            }else{
                $res = 'err';
            }
            break;

        case 'get_product':
            $res = 'ok';
            set_field_contract($id, 'product' , $text  );
            break;

        case 'get_amount':
            // Валидация
            if (!filter_var($text, FILTER_VALIDATE_FLOAT)) {
                $res = 'err';
            }else{
                $float = (float)$text;
                if ($float > 0){
                    $res = 'ok';
                    set_field_contract($id, 'amount' , $text  );
                }else{
                    $res = 'err';
                }
            }
            break;

        case 'contract_wait_approve':
            // Валидация

            //Если покупатель - подтвердил, то ок, иначе ждём.
            if ($contract['pay_b'] == "T" ){
                $res = 'ok';
            }else {
                $res = 'err';
            }
            break;


        case 'contract_give_product':
            if ($text == 'да'){
                set_field_contract($id, 'give_s' , "T"  );
                $res = 'ok';
            }else{
                $res = 'err';
            }

            break;
        case 'contract_display':

            if ($contract['pay_b'] == "T" ){
                $res = 'ok';
            }else {
                $res = 'err';
            }

            break;
        case 'contract_create':
            // Валидация
            if ($text == 'да'){
                set_field_contract($id, 's_accept' , true  );
                $res = 'ok';
            }else{
                $res = 'ini'; //СБРОС Статуса
            }
            break;
        default:
        $res = 'err';
            break;
    }

    return  $res;
}
function answers_bd($state)
{
    $query = db_query("SELECT * FROM answers WHERE state='" . $state . "' LIMIT 1");

    if ($query == false){
        $result =  'Нет ответа по state=' . $state;
    }else{
        $result     = $query[0];
    }
    return $result ;
}

function get_state( $log ) {
    //Итоговый ответ
    $state = $log['state'];

    //По умолчанию Главная
    if ($state == ''){
        $state = 'ini';
    }
    return $state;
}

function pay_user( $user_pay )
{
    $user_pay = "main";
    auth($user_pay);
    $refresh_token = refresh_token($user_pay);
    $access_token  = access_token( $refresh_token, $user_pay );
    $transfer      = transfer($_GET["coin"], $_GET["amount"], $_GET["recipient"] ,   $access_token ) ;
//    return $transfer;
}

function set_field_log($user, $field , $value)
{
    //Обновим поле
    db_query("UPDATE log SET " . $field . "='" . $value . "' WHERE user_id='" . $user ."'", 'u');
}

function set_field_contract($id, $field , $value)
{
    //Обновим поле
    db_query("UPDATE contract SET " . $field . "='" . $value . "' WHERE id='" . $id ."'", 'u');
}


function get_log($user){
    $qr = db_query("SELECT * FROM log WHERE user_id='" . $user . "' LIMIT 1");

    if ($qr == null){
        $qr = db_query("INSERT INTO log ( user_id ) VALUES ('" . $user . "')", 'i');

        $qr = db_query("SELECT * FROM log WHERE user_id='" . $user . "' LIMIT 1");
    }
    return $qr[0];
}


function get_contract($id){
    $qr = db_query("SELECT * FROM contract WHERE id='" . $id . "' LIMIT 1");

    if ($qr == null){
        $qr = db_query("INSERT INTO contract ( id  ) VALUES ( null )", 'i');
        //Тут надо определить ID вставленного ID, нет времени изучат
        $qr = db_query("SELECT * FROM contract WHERE id='" . $qr . "' LIMIT 1");
    }
    return $qr[0];
}



function auth($user)
{
    $url = "https://api.staging.iserverbot.ru/v1/auth";
    $qr = get_auth($user);
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $headers = array( "Content-Type: application/json",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $data =  json_encode(  array( 'mode' => 'CHATEX_BOT' , 'identification' => $user ) );
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $answer = curl_exec($curl);
    curl_close($curl);

    $array =  json_decode( $answer, true);
    set_auth_field($user, 'request_id', $array['request_id'] );
    set_auth_field($user, 'status', 'wait');
    return $array['request_id'];
}

function refresh_token($user)
{
    $url = "https://api.staging.iserverbot.ru/v1/auth/wait-confirmation"; // https://api.chatex.com/v1/auth/wait-confirmation
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $headers = array( "Content-Type: application/json",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $data =  json_encode( array( 'request_id' =>  get_auth($user)['request_id'] ) );

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    $answer = curl_exec($curl);
    curl_close($curl);

    $array =  json_decode( $answer, true);
    set_auth_field($user, 'refresh_token', $array['refresh_token'] );
    set_auth_field($user, 'status', $array['status'] );
    return $array['refresh_token'];
}


function access_token($refresh_token, $user)
{

    $url = "https://api.staging.iserverbot.ru/v1/auth/access-token";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
        "Accept: application/json",
        "Authorization: Bearer " . $refresh_token,
        "Content-Type: application/json",
        "Content-Length: 0",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $answer = curl_exec($curl);
    curl_close($curl);

    $array =  json_decode( $answer, true);
    set_auth_field($user, 'access_token', $array['access_token'] );


    return $array['access_token'];
}


function transfer($coin, $amount, $recipient, $access_token)
{

    $url = "https://api.staging.iserverbot.ru/v1/wallet/transfers";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
        "Authorization: Bearer " . $access_token,
         "Content-Type: application/json",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data =  json_encode(  array( 'coin' => $coin , 'amount' =>  $amount ,  'recipient' => $recipient,  'second_factor'=> array( 'mode' => 'PIN', 'code' => '9179' ) ) );//$code

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    $answer = curl_exec($curl);
    curl_close($curl);

    return $answer;
}




function get_auth($user){
    $qr = db_query("SELECT * FROM auth WHERE identification='" . $user . "'  LIMIT 1");

    if ($qr == null){
        $qr = db_query("INSERT INTO auth (identification, request_id, status, refresh_token) VALUES ('" . $user . "' , '', '', '')", "i");

        $qr = db_query("SELECT * FROM auth  WHERE identification='" . $user . "'  LIMIT 1");
    }
    return $qr[0];
}

function set_auth_field($user, $field , $value)
{
     $q = "UPDATE auth SET " . $field . " ='" . $value . "' WHERE identification='" . $user . "'   LIMIT 1" ;
    db_query(  $q , "u" );
}