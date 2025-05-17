<?php

require __DIR__ . '/../vendor/autoload.php';

use telegram\bot\TelegramBot;
use telegram\bot\types\Filters;
use telegram\bot\types\MessageHandler;
use telegram\bot\types\MessageHandlers;
use telegram\bot\types\MiddlewareHandlers;
use telegram\bot\types\MiddlewareHandler;

$handler = function ($telegram, $update) {
    $message = $update->getMessage()->reply('收到',[
        
    ]);
    echo "Handler executed.";
};

$handler1 = function ($telegram, $update) {
    $message = $update->getMessage()->reply('收到');
    echo "Handler executed1.";
};

$bot = new TelegramBot('5601845467:AAGW1UpUpK3ZoTcxCuFyfYbLUf3rxonlWeQ');

$bot->add_handlers(
    new MiddlewareHandlers([
        new MiddlewareHandler($handler),
        new MiddlewareHandler($handler1)
    ])
);

$bot->add_handlers(
    new MessageHandlers([
        new MessageHandler(new Filters(['all']), $handler),
        new MessageHandler(new Filters(['all']), $handler1)
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