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

    /**
     * 设置键盘是否为一次性键盘
     * @param bool $value 是否为一次性键盘
     * @return self 返回当前实例以支持链式调用
     */
    public function set_one_time_keyboard(bool $value): self
    {
        $this->one_time_keyboard = $value;
        return $this;
    }

    /**
     * 设置键盘是否自动调整大小
     * @param bool $value 是否自动调整大小
     * @return self 返回当前实例以支持链式调用
     */
    public function set_resize_keyboard(bool $value): self
    {
        $this->resize_keyboard = $value;
        return $this;
    }

    /**
     * 设置键盘是否持久化
     * @param bool $value 是否持久化
     * @return self 返回当前实例以支持链式调用
     */
    public function set_is_persistent(bool $value): self
    {
        $this->is_persistent = $value;
        return $this;
    }

    /**
     * 添加一行键盘
     * @param KeyboardRow $row 键盘行对象
     * @return self 返回当前实例以支持链式调用
     */
    public function add_row(KeyboardRow $row) :self
    {
        array_push($this->rows, $row);
        return $this;
    }

    /**
     * 生成键盘的JSON格式
     * @return string 返回键盘的JSON字符串
     */
    public function make(): string
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