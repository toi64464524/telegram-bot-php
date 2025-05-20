<?php

namespace Telegram\Bot\Markups;

class InlineCallbackKeyboardMarkup
{
    public array $rows; // [InlineCallbackKeyboardRow]
    
    public function __construct(array $rows=[]) 
    {
        foreach ($rows as $row) {
            if (!$row instanceof InlineCallbackKeyboardRow) {
                throw new \Exception('按键类型错误');
            }
        }
        $this->rows = $rows;
    }

    public function add_row(InlineCallbackKeyboardRow $row)
    {
        array_push($this->rows, $row);
    }

    public function make()
    {
        $keyboard = [];
        foreach ($this->rows as $row) {
            $keyboard_row = [];
            foreach ($row as $buttons) {
                foreach($buttons as $button) {
                    if ($button && $button->make()) {
                        array_push($keyboard_row, $button->make());
                    }
                }
            }

            if ($keyboard_row) {
                array_push($keyboard, $keyboard_row);
            }
        }

        $reply_markup = array('inline_keyboard' => $keyboard);
        return json_encode($reply_markup);
    }
}