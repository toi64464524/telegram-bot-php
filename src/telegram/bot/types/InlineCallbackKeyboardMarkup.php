<?php

namespace telegram\bot\types;

class InlineCallbackKeyboardMarkup
{
    public array $rows;
    
    public function __construct(array $rows=[]) 
    {
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
            foreach ($row as $button) {
                if ($button) {
                    array_push($keyboard_row, $button);
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