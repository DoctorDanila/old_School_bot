<?php

require_once __DIR__.'/vendor3/autoload.php';

use Discord\Discord;

$discord = new Discord(array(
    'token' => 'ODkwNTgwMzQzMDU1MzM5NTQw.YUx3kw.QYLjfp4lT85jlSeyS7_i8eH7ieA'
));

$discord->on('ready', function ($discord) {
    echo "Bot is ready!", PHP_EOL;

    // Listen for messages.
    $discord->on('message', function ($message, $discord) {
        echo "{$message->author->username}: {$message->content}",PHP_EOL;
    });
});

$discord->run();