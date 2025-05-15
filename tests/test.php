<?php

require __DIR__ . '/../vendor/autoload.php';

use telegram\bot\TelegramBot;
use telegram\bot\types\MiddlewareHandlers;
use telegram\bot\types\MiddlewareHandler;

$handler = function ($telegram, $update) {
    $message = $update->getMessage()->reply('收到');
    echo "Handler executed.";
};
$bot = new TelegramBot('5601845467:AAGW1UpUpK3ZoTcxCuFyfYbLUf3rxonlWeQ');

$bot->add_handlers(
    new MiddlewareHandlers([
        new MiddlewareHandler($handler)
    ])
);
$me = $bot->getMe();
var_dump($me->username);
$bot->run();
// $update->getChat();
// $update->getFromId();
// $update->getMessage();
// $bot = new TelegramBot('<your_bot_token>');
// print_r($bot->getMe());