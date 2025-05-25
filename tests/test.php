<?php

require __DIR__ . '/../vendor/autoload.php';

use Telegram\Bot\TelegramBot;
use Telegram\Bot\Markups\InlineCallbackKeyboardButton;
use Telegram\Bot\Markups\InlineCallbackKeyboardRow;
use Telegram\Bot\Markups\InlineCallbackKeyboardMarkup;
use Telegram\Bot\Handlers\MessageHandler;
use Telegram\Bot\Handlers\MessageHandlers;
use Telegram\Bot\Handlers\MiddlewareHandlers;
use Telegram\Bot\Handlers\MiddlewareHandler;
use Telegram\Bot\Handlers\InlineCallbackHandler;
use Telegram\Bot\Handlers\InlineCallbackHandlers;
use Telegram\Bot\Handlers\KeyboardHandlers;
use Telegram\Bot\Handlers\KeyboardHandler;
use Telegram\Bot\Handlers\StateHandler;
use Telegram\Bot\Handlers\Handlers;
use Telegram\Bot\Filters\Filters;
use Telegram\Bot\Handlers\CommandHandler;

$handler = function (TelegramBot $telegram, $update) {
    $markup = new InlineCallbackKeyboardMarkup;
    $markup->add_row(new InlineCallbackKeyboardRow([
        new InlineCallbackKeyboardButton('按键测试', ['callback_data' =>'button'])
    ]));
    $res = $telegram->send_message_text(5020408899,'12', [], $markup);
};

// $handler1 = function ($telegram, $update) {
//     $message = $update->getMessage()->reply('收到');
//     echo "Handler executed1.";
// };

$bot = new TelegramBot('5601845467:AAGW1UpUpK3ZoTcxCuFyfYbLUf3rxonlWeQ');

$bot->add_handlers(
    new Handlers([
        new InlineCallbackHandler(new Filters(['/button/']), $handler),
        new KeyboardHandler('自定义按键测试', function($telegram, $update) {
            $message = $update->getMessage()->reply('自定义按键测试');
        }),

        new MessageHandler(new Filters(['/正则消息/']), function($telegram, $update) {
            $markup = new InlineCallbackKeyboardMarkup;
            $markup->add_row(new InlineCallbackKeyboardRow([
                new InlineCallbackKeyboardButton('按键测试', ['callback_data' =>'button'])
            ]));
            $message = $update->getMessage()->reply('正则消息', [], $markup);
        }),

        new MessageHandler(new Filters(['/test消息/', '|', '/test/']), function($telegram, $update) {
            $markup = new InlineCallbackKeyboardMarkup;
            $markup->add_row(new InlineCallbackKeyboardRow([
                new InlineCallbackKeyboardButton('按键测试', ['callback_data' =>'button'])
            ]));
            $telegram->send_message_text(5020408899,'收到普通消息 test消息', [], $markup);
        }),

        new CommandHandler('start', function($telegram, $update) {
            $message = $update->getMessage()->reply('start命令测试');
        }),
    ])
);

// $bot->add_handlers(
//     new MiddlewareHandlers([
//         new MiddlewareHandler(function($telegram, $update) {
//             var_dump("中间件控制器1");
//         }),
//         new MiddlewareHandler(function($telegram, $update) {
//             var_dump("中间件控制器2");
//         }),
//     ])
// );

// $bot->add_handlers(
//     new InlineCallbackHandlers([
//         new InlineCallbackHandler(new Filters(['all']), function($telegram, $update) {
//             var_dump("内联控制器1");
//         }),

//         new InlineCallbackHandler(new Filters(['all']), function($telegram, $update) {
//             var_dump("内联控制器2");
//         }),
//     ])
// );

// $bot->add_handlers(
//     new MessageHandlers([
//         new MessageHandler(new Filters(['all']), function($telegram, $update) {
//             var_dump("消息控制器1");
//         }),
//         new MessageHandler(new Filters(['all']), function($telegram, $update) {
//             var_dump("消息控制器2");
//         }),
//     ])
// );

// $bot->add_handler(
//     new StateHandler(
//         [
//             new MessageHandler(new Filters(['/开始状态/']), function($telegram, $update) {
//                 var_dump("开始状态");
//                 return 'WAIT';
//             })
//         ],
//         [
//             "WAIT"=>[new MessageHandler(new Filters(['/WAIT/']), function($telegram, $update) {
//                 var_dump("开始状态2");
//             })]
//         ],
//         [
//             new MessageHandler(new Filters(['/取消/']), function($telegram, $update) {
//                 var_dump("开始状态3");
//                 return StateHandler::END;
//             })
//         ],
//         StateHandler::USER_STATE
//     )
// );
$me = $bot->getMe();
var_dump($me->username);
$bot->run();
// $update->getChat();
// $update->getFromId();
// $update->getMessage();
// $bot = new TelegramBot('<your_bot_token>');
// print_r($bot->getMe());