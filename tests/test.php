<?php

require __DIR__ . '/../vendor/autoload.php';

use telegram\bot\TelegramBot;
use telegram\bot\types\Filters;
use telegram\bot\types\MessageHandler;
use telegram\bot\types\MessageHandlers;
use telegram\bot\types\MiddlewareHandlers;
use telegram\bot\types\MiddlewareHandler;
use telegram\bot\types\InlineCallbackKeyboardButton;
use telegram\bot\types\InlineCallbackKeyboardRow;
use telegram\bot\types\InlineCallbackKeyboardMarkup;
use telegram\bot\types\InlineCallbackHandler;
use telegram\bot\types\InlineCallbackHandlers;
use telegram\bot\types\KeyboardHandlers;
use telegram\bot\types\KeyboardHandler;
use telegram\bot\types\StateHandler;

$botton = new InlineCallbackKeyboardButton('text', ['callback_data' =>'123']);

$handler = function (TelegramBot $telegram, $update) {
    $markup = new InlineCallbackKeyboardMarkup;
    $markup->add_row(new InlineCallbackKeyboardRow([
        new InlineCallbackKeyboardButton('test', ['callback_data' =>'12'])
    ]));
    $res = $telegram->send_message_text(5020408899,'12', [], $markup);
};

$handler1 = function ($telegram, $update) {
    $message = $update->getMessage()->reply('收到');
    echo "Handler executed1.";
};

$bot = new TelegramBot('5601845467:AAGW1UpUpK3ZoTcxCuFyfYbLUf3rxonlWeQ');

$bot->add_handlers(
    new KeyboardHandlers([
        new KeyboardHandler('TEST', [], function($telegram, $update) {
            var_dump("自定义按键控制器1");
        }),
        new KeyboardHandler('TEST2', [], function($telegram, $update) {
            var_dump("自定义按键控制器2");
        }),
    ])
);

$bot->add_handlers(
    new MiddlewareHandlers([
        new MiddlewareHandler(function($telegram, $update) {
            var_dump("中间件控制器1");
        }),
        new MiddlewareHandler(function($telegram, $update) {
            var_dump("中间件控制器2");
        }),
    ])
);

$bot->add_handlers(
    new InlineCallbackHandlers([
        new InlineCallbackHandler(new Filters(['all']), function($telegram, $update) {
            var_dump("内联控制器1");
        }),

        new InlineCallbackHandler(new Filters(['all']), function($telegram, $update) {
            var_dump("内联控制器2");
        }),
    ])
);

$bot->add_handlers(
    new MessageHandlers([
        new MessageHandler(new Filters(['all']), function($telegram, $update) {
            var_dump("消息控制器1");
        }),
        new MessageHandler(new Filters(['all']), function($telegram, $update) {
            var_dump("消息控制器2");
        }),
    ])
);

$bot->add_handler(
    new StateHandler(
        [
            new MessageHandler(new Filters(['/开始状态/']), function($telegram, $update) {
                var_dump("开始状态");
                return 'WAIT';
            })
        ],
        [
            "WAIT"=>[new MessageHandler(new Filters(['/WAIT/']), function($telegram, $update) {
                var_dump("开始状态2");
            })]
        ],
        [
            new MessageHandler(new Filters(['/取消/']), function($telegram, $update) {
                var_dump("开始状态3");
                return StateHandler::END;
            })
        ],
        StateHandler::USER_STATE
    )
);
$me = $bot->getMe();
var_dump($me->username);
$bot->run();
// $update->getChat();
// $update->getFromId();
// $update->getMessage();
// $bot = new TelegramBot('<your_bot_token>');
// print_r($bot->getMe());