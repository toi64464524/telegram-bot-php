<?php
namespace App\Bot;

use App\Bot\TelegramBot;
use App\Bot\Types\Update;
use App\Bot\Types\Filters;
use App\Bot\Types\CommandHandler;
use App\Bot\Types\CommandHandlers;
use App\Bot\Types\InlineCallbackHandler;
use App\Bot\Types\InlineCallbackHandlers;
use App\Bot\Types\KeyboardHandler;
use App\Bot\Types\KeyboardHandlers;
use App\Bot\Types\MessageHandler;
use App\Bot\Types\MessageHandlers;
use App\Bot\Types\StateHandlers;
use App\Bot\Types\MiddlewareHandler;
use App\Bot\Types\MiddlewareHandlers;
use App\Bot\Types\InlineCallbackKeyboardMarkup;
use App\Bot\Types\KeyboardMarkup;
use App\Models\TelegramChat;
use App\Models\TelegramUser;
use App\Models\MerchantUser;
use App\Models\ProductGroup;
use App\Models\ProductGood;
use App\Models\TelegramAccount;
use App\Models\ProductGoodTelegramAccountMap;
use App\Services\SellerService;
use App\Services\MerchantUserWalletService;

class SellerTelethonBot extends TelegramBot
{
    public array $user_id_map;
    public array $user_lang_map;
    public array $user_data;
    public array $admin_ids;
    public TelegramBotText $bot_text;

    public function __construct(string $token, array $admin_ids=[], TelegramBotText $bot_text=null)
    {
        // $token = "7045129649:AAGBknsedjGCW-lRS69LSDWdeyrThxrcF1E";
        parent::__construct($token);
        $this->bot_text = $bot_text;
        $this->admin_ids = $admin_ids;
        $this->user_id_map =[];
        $this->user_lang_map =[];
        $this->user_data =[];
        $middleware_handlers = new MiddlewareHandlers([
            new MiddlewareHandler(\Closure::fromCallable([$this, 'middleware_handler'])),
        ]);

        $command_handlers = new CommandHandlers([
            new CommandHandler('start', '开始', \Closure::fromCallable([$this, 'handler_start_command'])),
        ]);
        
        $keyboard_handlers = new KeyboardHandlers([
            [
                new KeyboardHandler($this->bot_text->get('zh-hans', 'command.product_group_menu'), [], \Closure::fromCallable([self::class, 'handler_product_group_menu_command'])),
            ],
            [
                new KeyboardHandler($this->bot_text->get('zh-hans', 'command.user_center'), [], \Closure::fromCallable([self::class, 'handler_user_center_command'])),
                new KeyboardHandler($this->bot_text->get('zh-hans', 'command.energy_menu'), [], \Closure::fromCallable([self::class, 'handler_energy_menu_command'])),
            ],
            [
                new KeyboardHandler($this->bot_text->get('zh-hans', 'command.cluster_sender'), [], \Closure::fromCallable([self::class, 'handler_cluster_sender_command'])),
                new KeyboardHandler($this->bot_text->get('zh-hans', 'command.cloud_control'), [], \Closure::fromCallable([self::class, 'handler_cloud_control_command'])),
            ],
            [
                new KeyboardHandler($this->bot_text->get('zh-hans', 'command.customer_service'), [], \Closure::fromCallable([self::class, 'handler_reply_recharge_amount_command'])),
            ],
        ]);

        $inline_callback_handlers = new InlineCallbackHandlers([[
            new InlineCallbackHandler(new Filters(["/^group:\d/"]),  \Closure::fromCallable([$this, 'handler_product_group_menu_command'])),
            new InlineCallbackHandler(new Filters(["/^cancel/"]),  \Closure::fromCallable([$this, 'handler_cancel_command'])),
        ]]);

        $state_handles = new StateHandlers(
            [
                new InlineCallbackHandler(new Filters(["/^goods:\d+/"]),  \Closure::fromCallable([$this, 'handler_start_buy_goods_command'])),
                new InlineCallbackHandler(new Filters(["/^user_wallet_recharge/"]),  \Closure::fromCallable([$this, 'handler_user_wallet_recharge_command'])),
            ],
            [
                'WAIT_BUY_COUNT' => [new MessageHandler(new Filters(["/\d+/"]), \Closure::fromCallable([self::class, 'handler_reply_buy_count_command']))],
                'WAIT_BUY_CONFIRM' => [
                    new InlineCallbackHandler(new Filters(["/^confirm_ok/"]),  \Closure::fromCallable([$this, 'handler_buy_confirm_ok_command'])),
                    new InlineCallbackHandler(new Filters(["/^confirm_fail/"]),  \Closure::fromCallable([$this, 'handler_buy_confirm_fail_command']))
                ],
                'WAIT_RECHARGE_AMOUNT' => [
                    new MessageHandler(new Filters(["/\d+/"]), \Closure::fromCallable([self::class, 'handler_reply_recharge_amount_command']))
                ]
            ],
            [
                new InlineCallbackHandler(new Filters(["/^cancel/"]),  \Closure::fromCallable([$this, 'handler_cancel_command'])),
                new CommandHandler('cancel', '取消', \Closure::fromCallable([self::class, 'handler_cancel_command'])),
            ],
            StateHandlers::USER_STATE
        );

        // $message_handlers = new MessageHandlers([
        // ]);

        $this->add_state_handler($state_handles);
        $this->add_middleware_handler($middleware_handlers);
        $this->add_handlers($command_handlers);
        $this->add_handlers($keyboard_handlers);
        $this->add_handlers($inline_callback_handlers);
        // $this->add_handlers($message_handlers);
    }

    // 中间件
    public static function middleware_handler(SellerTelethonBot $telegram, Update $update) {
        $from = $update->getFrom();
        $chat = $update->getChat();
        if (!isset($telegram->user_lang_map[$from->id])) {
            // $telegram->user_lang_map[$from->id] = $from->language_code === 'en' ? $from->language_code : 'zh-hans';
            $telegram->user_lang_map[$from->id] = 'zh-hans';
        }

        if (isset($telegram->user_id_map[$from->id])) {
            $user = MerchantUser::findCached($telegram->user_id_map[$from->id]);
            return true;
        }else {
            TelegramChat::firstOrCreate( ['id' => $chat->id], [
                'id' => $chat->id,
                'type' => $chat->type,
                'username' => $chat->username,
                'title' => $chat->title
            ]);
            TelegramUser::firstOrCreate(['id' => $from->id], [
                'id' => $from->id,
                'username' => $from->username,
                'first_name' => $from->first_name,
                'last_name' => $from->last_name,
                'username' => $from->username
            ]);
            $user = MerchantUser::firstOrCreate(
                ['telegram_id' => $from->id],
                [
                    'telegram_id' => $from->id,
                    'nickname' => $from->first_name . $from->first_name ? " {$from->first_name}" : "",
                    'username' => $from->username,
                ]
            );
            $user = MerchantUser::findCached($user->id);
            $telegram->user_id_map[$from->id] = $user->id;
            return true;
        }
    }

    // 处理取消状态等待
    public static function handler_cancel_command(SellerTelethonBot $telegram, Update $update) {
        $telegram->delete_message($update->getChatId(), $update->getMessageId());
        return StateHandlers::END;
    }

    // 处理start命令
    public static function handler_start_command(SellerTelethonBot $telegram, Update $update) {
        $text = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'message.start');
        $telegram->send_message_text($update->getChatId(), $text, [], $telegram->keyboard_markup);
    }

    // 处理个人中心命令
    public static function handler_user_center_command(SellerTelethonBot $telegram, Update $update) {
        $from = $update->getFrom();
        $user = MerchantUser::findCached($telegram->user_id_map[$from->id]);
        $name = $from->first_name . $from->last_name ? ' ' . $from->last_name : '';
        $text = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'message.user_center', ['id'=>$from->id,'name'=>$name,'balance'=>$user->balance,'create_at'=>$user->created_at]);
        $inline_callback_keyboard_markup = new InlineCallbackKeyboardMarkup;
        $inline_callback_keyboard_markup->add_row([['text' => $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'button.wallet.recharge'), 'callback_data' => "user_wallet_recharge"]]);
        return $telegram->send_message_text($update->getChat()->id, $text, [], $inline_callback_keyboard_markup);
    }

    // 余额充值
    public static function handler_user_wallet_recharge_command(SellerTelethonBot $telegram, Update $update) {
        $text = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'user.wallet.please_reply_recharge_amount');
        $inline_callback_keyboard_markup = new InlineCallbackKeyboardMarkup;
        $inline_callback_keyboard_markup->add_row([['text' => $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'button.close'), 'callback_data' => "cancel"]]);
        $telegram->send_message_text($update->getChat()->id, $text, [], $inline_callback_keyboard_markup);
        return "WAIT_RECHARGE_AMOUNT";
    }

    // 收到回复的充值金额
    public static function handler_reply_recharge_amount_command(SellerTelethonBot $telegram, Update $update) {
        $amount = abs(intval($update->getMessageText()));
        if (!$amount) {
            $text = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'user.wallet.recharge.amount_error');
            return $telegram->send_message_text($update->getChat()->id, $text);
        }

        $user = MerchantUser::findCached($telegram->user_id_map[$update->getFromId()]);
        $wallet = new MerchantUserWalletService($user);
        $order = $wallet->recharge('usdt', $amount);
        $text = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'user.wallet.recharge.show_pay_info',['address'=>$order->pay_address, 'amount'=>$order->pay_amount]);
        $inline_callback_keyboard_markup = new InlineCallbackKeyboardMarkup;
        $inline_callback_keyboard_markup->add_row([['text' => $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'button.close'), 'callback_data' => "cancel"]]);
        $telegram->send_message_text($update->getChat()->id, $text, [], $inline_callback_keyboard_markup);
        return StateHandlers::END;
    }

    // 处理能量租赁命令
    public static function handler_energy_menu_command(SellerTelethonBot $telegram, Update $update) {
        $telegram->send_message_text($update->getChat()->id, 'https://t.me/TRC20');
    }

    // 处理群发器命令
    public static function handler_cluster_sender_command(SellerTelethonBot $telegram, Update $update) {
        return $telegram->send_message_text($update->getChat()->id, 'https://t.me/doutu');
    }

    // 处理云控命令
    public static function handler_cloud_control_command(SellerTelethonBot $telegram, Update $update) {
        return $telegram->send_message_text($update->getChat()->id, 'https://t.me/haioukongke');
    }

    // 处理在线客服命令
    public static function handler_customer_service_command(SellerTelethonBot $telegram, Update $update) {
        $inline_callback_keyboard_markup = new InlineCallbackKeyboardMarkup;
        $inline_callback_keyboard_markup->add_row([['text'=>$telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()],'button.customer_service'), 'url'=>'https://t.me/Xiaooumei']]);
        $telegram->send_message_text($update->getChat()->id, '👇', [], $inline_callback_keyboard_markup);
    }

    // 商品分组主菜单
    public static function handler_product_group_menu_command(SellerTelethonBot $telegram, Update $update) {    
        $parent_id = null;
        $callback_data = $update->getCallbackData();
        if ($callback_data) {
            // 从消息点击进入
            $parent_id = $callback_data['args'][0];
        }
        // 如果还有下级分组 继续显示
        $groups = $parent_id ? ProductGroup::where('parent_id', $parent_id)->get() : ProductGroup::whereNull('parent_id')->get();

        if (!$groups->isEmpty()) {
            $text = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'message.product_group_menu');
            $inline_callback_keyboard_markup = new InlineCallbackKeyboardMarkup;
            foreach ($groups as $group) {
                $goods_ids = ProductGood::whereIn('product_group_id', $group->getAllDescendantIds())->pluck('id');
                $cnt = ProductGoodTelegramAccountMap::whereIn('good_id', $goods_ids )->count();
                if ($cnt) {
                    $group_name = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], $group->telegram_bot_show_text);
                    $inline_callback_keyboard_markup->add_row(array(['text' => "{$group_name} ({$cnt})", 'callback_data' => "group:{$group['id']}"]));
                }
            }

            $inline_callback_keyboard_markup->add_row([['text' => $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'button.close'), 'callback_data' => "cancel"]]);
            return $telegram->send_message_text($update->getChat()->id, $text, [], $inline_callback_keyboard_markup);
        } else if ($goods = ProductGood::where('product_group_id', $parent_id)->get()) {
            $text = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'message.product_good_menu');
            $inline_callback_keyboard_markup = new InlineCallbackKeyboardMarkup;
            foreach ($goods as $good) {
                $cnt = ProductGoodTelegramAccountMap::where('good_id', $good->id)->count();
                if ($cnt) {
                    $good_name = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], $good->telegram_bot_show_text);
                    $inline_callback_keyboard_markup->add_row(array(['text' => "{$good_name} ({$cnt})", 'callback_data' => "goods:{$good['id']}"]));
                }
            }

            $inline_callback_keyboard_markup->add_row([['text' => $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'button.close'), 'callback_data' => "cancel"]]);
            return $telegram->send_message_text($update->getChat()->id, $text, [], $inline_callback_keyboard_markup);
        } 
    }

    // 购买流程
    public static function handler_start_buy_goods_command(SellerTelethonBot $telegram, Update $update) {
        $message_id = $update->getMessageId();
        $callback_data = $update->getCallbackData();
        $args = $callback_data['args'];
        $good = ProductGood::find($args[0]);
        if (!$good || !$good->status) {
            $text = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'product.good.product.good.not_find');
            return $telegram->send_message_text($update->getChat()->id, $text);
        }

        $text = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'product.good.please_reply_buy_count');
        $inline_callback_keyboard_markup = new InlineCallbackKeyboardMarkup;
        $inline_callback_keyboard_markup->add_row([['text' => $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'button.close'), 'callback_data' => "cancel"]]);
        $telegram->send_message_text($update->getChat()->id, $text, [], $inline_callback_keyboard_markup);
        $telegram->user_data[$update->getFromId()]['good_id'] = $args[0];
        return "WAIT_BUY_COUNT";
        
    }

    // 收到回复的购买数量
    public static function handler_reply_buy_count_command(SellerTelethonBot $telegram, Update $update) {
        $good_id = $telegram->user_data[$update->getFromId()]['good_id'];
        
        $count = abs(intval($update->getMessageText()));
        $good = ProductGood::find($good_id);
        if (!$good || !$good->status) {
            $text = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'product.good.product.good.not_find');
            return $telegram->send_message_text($update->getChat()->id, $text);
        }

        if (!$count) {
            $text = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'product.good.count_error');
            return $telegram->send_message_text($update->getChat()->id, $text);
        }

        $telegram->user_data[$update->getFromId()]['buy_count'] = $count;;
        $text = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'product.good.confirm_buy_menu',['good_id'=>$good_id, 'count'=>$count, 'price'=>$good->price,'total_amount'=>$good->price*$count]);
        $inline_callback_keyboard_markup = new InlineCallbackKeyboardMarkup;
        $inline_callback_keyboard_markup->add_row([
            ['text' => $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'button.confirm_ok'), 'callback_data' => "confirm_ok"],
            ['text' => $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'button.confirm_fail'), 'callback_data' => "confirm_fail"]
        ]);
        $telegram->send_message_text($update->getChat()->id, $text, [], $inline_callback_keyboard_markup);
        $telegram->user_data[$update->getFromId()]['buy_count'] = $count;
        return "WAIT_BUY_CONFIRM";
    }

    // 确认购买
    public static function handler_buy_confirm_ok_command(SellerTelethonBot $telegram, Update $update) {
        $good_id = $telegram->user_data[$update->getFromId()]['good_id'];
        $count = $telegram->user_data[$update->getFromId()]['buy_count'];
        $user = MerchantUser::findCached($telegram->user_id_map[$update->getFromId()]);
        $good = ProductGood::find($good_id);
        
        if (!$good || !$good->status) {
            $text = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'product.good.not_find');
            return $telegram->send_message_text($update->getChat()->id, $text);
        }
        
        try {
            $seller = new SellerService($user);
            $order = $seller->make_telegram_account_order($good_id, $count);
            $text = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'product.good.buy_success');
            $telegram->send_message_text($update->getChat()->id, $text);
            return StateHandlers::END;
        } catch (\Exception $e) {
            // throw $e;
            if ($e->getMessage() === '余额不足') {
                $text = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'message.user.not_balance');
            } else if ($e->getMessage() === '库存不足') {
                $text = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'product.good.not_count');
            } else {
                $text = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'product.good.buy_error');
            }

            $telegram->send_message_text($update->getChat()->id, $text);
            return StateHandlers::END;
        }
    }

    // 取消购买
    public static function handler_buy_confirm_fail_command(SellerTelethonBot $telegram, Update $update) {
        $text = $telegram->bot_text->get($telegram->user_lang_map[$update->getFromId()], 'product.good.buy_cancel_success');
        $telegram->send_message_text($update->getChat()->id, $text);
        return StateHandlers::END;
    }
} 