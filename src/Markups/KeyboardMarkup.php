<?php

namespace Telegram\Bot\Markups;

class KeyboardMarkup
{
    public array $rows;
    public bool $one_time_keyboard;
    public bool $resize_keyboard;
    public bool $is_persistent;
    
    public function __construct(array $rows=[], bool $one_time_keyboard=False, bool $resize_keyboard=True, $is_persistent=true) 
    {
        foreach ($rows as $row) {
            if (!$row instanceof KeyboardRow) {
                throw new \Exception('按键类型错误');
            }
        }
        $this->rows = $rows;
        $this->one_time_keyboard = $one_time_keyboard;
        $this->resize_keyboard = $resize_keyboard;
        $this->is_persistent = $is_persistent;
    }

    public function set_one_time_keyboard(bool $value)
    {
        $this->one_time_keyboard = $value;
    }

    public function set_resize_keyboard(bool $value)
    {
        $this->resize_keyboard = $value;
    }

    public function set_is_persistent(bool $value)
    {
        $this->is_persistent = $value;
    }

    public function add_row(KeyboardRow $row)
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
        $reply_markup = array('keyboard' => $keyboard, 'one_time_keyboard'=>$this->one_time_keyboard, 'resize_keyboard' => $this->resize_keyboard, 'is_persistent'=> $this->is_persistent);
        return json_encode($reply_markup);
    }
}