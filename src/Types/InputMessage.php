<?php

namespace Telegram\Bot\Types;

class InputMessage 
{
    private $type;
    private array $data = [];

    public function __construct(array $messageData, array $params = [])
    {
        // 处理主要消息内容
        $this->processMessageContent($messageData);
        
        // 处理可选参数
        $this->processOptionalParameters($params);
    }

    /**
     * 处理消息内容
     */
    private function processMessageContent(array $messageData): void
    {
        $supportedTypes = [
            'text', 'photo', 'video', 'audio', 'document', 
            'voice', 'sticker', 'video_note', 'location', 
            'contact', 'poll', 'venue', 'animation', 'dice',
            'media_group', 'invoice', 'game', 'story'
        ];

        foreach ($supportedTypes as $type) {
            if (isset($messageData[$type])) {
                $this->type = $type;
                $this->data[$type] = $messageData[$type];
                return;
            }
        }

        throw new \InvalidArgumentException("不支持的消息类型");
    }

    /**
     * 处理可选参数
     */
    private function processOptionalParameters(array $params): void
    {
        $optionalParams = [
            'parse_mode' => 'HTML',
            'caption' => null,
            'caption_entities' => null,
            'reply_markup' => null,
            'disable_notification' => null,
            'reply_to_message_id' => null,
            'allow_sending_without_reply' => null,
            'protect_content' => null,
            'entities' => null,
            'disable_web_page_preview' => null,
            'duration' => null,
            'width' => null,
            'height' => null,
            'thumb' => null,
            'performer' => null,
            'title' => null,
            'filename' => null,
            'disable_content_type_detection' => null,
            'latitude' => null,
            'longitude' => null,
            'horizontal_accuracy' => null,
            'live_period' => null,
            'heading' => null,
            'proximity_alert_radius' => null,
            'phone_number' => null,
            'first_name' => null,
            'last_name' => null,
            'vcard' => null,
            'question' => null,
            'options' => null,
            'is_anonymous' => null,
            'type' => null,
            'allows_multiple_answers' => null,
            'correct_option_id' => null,
            'explanation' => null,
            'explanation_entities' => null,
            'open_period' => null,
            'close_date' => null,
            'is_closed' => null,
            'address' => null,
            'foursquare_id' => null,
            'foursquare_type' => null,
            'google_place_id' => null,
            'google_place_type' => null,
            'emoji' => null,
            'media' => null,
            'price' => null,
            'currency' => null,
            'payload' => null,
            'provider_token' => null,
            'start_parameter' => null,
            'description' => null,
            'photo_url' => null,
            'photo_size' => null,
            'photo_width' => null,
            'photo_height' => null,
            'need_name' => null,
            'need_phone_number' => null,
            'need_email' => null,
            'need_shipping_address' => null,
            'send_phone_number_to_provider' => null,
            'send_email_to_provider' => null,
            'is_flexible' => null,
            'game_short_name' => null
        ];

        foreach ($optionalParams as $param => $defaultValue) {
            if (array_key_exists($param, $params)) {
                $this->data[$param] = $params[$param];
            } elseif ($defaultValue !== null) {
                $this->data[$param] = $defaultValue;
            }
        }
    }

    /**
     * 获取构建好的消息数据
     */
    public function make($chat_id): array
    {
        $this->data['chat_id'] = $chat_id;
        return $this->data;
    }
}