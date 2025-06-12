<?php
namespace Telegram\Bot\Objects;

use App\TelegramBot\AccountSeller\Bot;

class UserData 
{
    protected array $attributes = [];

    // 获取数据
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    // 设置数据
    public function set(string $key, mixed $value): bool
    {
        $this->attributes[$key] = $value;
        return true;
    }

    // 修改数据
    public function update(string $key, array $data): bool
    {
        $this->attributes[$key] = $data;
        return true;
    }

    // 删除数据
    public function delete(string $key): bool
    {
        unset($this->attributes[$key]);
        return true;
    }

    // 清空数据
    public function clear(): bool
    {
        $this->attributes = [];
        return true;
    }

    // 获取所有数据
    public function all(): array
    {
        return $this->attributes;
    }
}
