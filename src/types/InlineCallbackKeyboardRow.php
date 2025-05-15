<?php

namespace telegram\bot\types;

class InlineCallbackKeyboardRow {
    public array $row;

    public function __construct(array $buttons)
    {
        foreach ($buttons as $button) {
            if (!$button instanceof InlineCallbackKeyboardButton) {
                throw new \Exception('按键类型错误');
            }
        }

        $this->row = $buttons;
    }

    public function add_button(InlineCallbackKeyboardButton $button)
    {
        array_push($this->row, $button);
    }

    public function delete()
    {
        $this->row = [];
    }
}
