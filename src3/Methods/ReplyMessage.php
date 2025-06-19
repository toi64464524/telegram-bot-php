<?php

namespace Telegram\Bot\Methods;

use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Objects\Message as MessageObject;
use Telegram\Bot\Traits\Telegram;

/**
 * Class ReplyMessage.
 *
 * @mixin Http
 */
trait ReplyMessage
{
    use Telegram;
    
    /**
     * @throws TelegramSDKException
     */
    public function replyMessage(array $params): MessageObject
    {
        $params['chat_id'] = $this->getChat()->id;
        $params['reply_to_message_id'] = $this->message_id;
        
        if (isset($params['photo'])) {
            return $this->getTelegram()->sendPhoto($params);
        } else if (isset($params['text'])) {
            return $this->getTelegram()->sendMessage($params);
        } else if (isset($params['audio'])) {
            return $this->getTelegram()->sendAudio($params);
        } else if (isset($params['document'])) {
            return $this->getTelegram()->sendDocument($params);
        } else if (isset($params['video'])) {
            return $this->getTelegram()->sendVideo($params);
        } else if (isset($params['animation'])) {
            return $this->getTelegram()->sendAnimation($params);
        } else if (isset($params['voice'])) {
            return $this->getTelegram()->sendVoice($params);
        } else if (isset($params['video_note'])) {
            return $this->getTelegram()->sendVideoNote($params);
        } else if (isset($params['media'])) {
            return $this->getTelegram()->sendMediaGroup($params);
        } else if (isset($params['latitude'])) {
            return $this->getTelegram()->sendVenue($params);
        } else if (isset($params['phone_number'])) {
            return $this->getTelegram()->sendContact($params);
        } else if (isset($params['question'])) {
            return $this->getTelegram()->sendPoll($params);
        }

        throw new TelegramSDKException("send error not type");
    }

    public function editMessage(array $params): MessageObject
    {
        $params['chat_id'] =$this->getChat()->id;
        $params['message_id'] = $this->message_id;

        if (isset($params['text'])) {
            return $this->getTelegram()->editMessageText($params);
        } else if (isset($params['caption'])) {
            return $this->getTelegram()->editMessageCaption($params);
        } else if (isset($params['reply_markup'])) {
            return $this->getTelegram()->editMessageReplyMarkup($params);
        } else if (isset($params['media'])) {
            return $this->getTelegram()->editMessageMedia($params);
        } else {
            throw new TelegramSDKException("param error");
        }
    }

    public function deleteMessage()
    {
        $params['chat_id'] = $this->getChat()->id;
        $params['message_id'] = $this->message_id;
        return $this->getTelegram()->deleteMessage($params);
    }
}
