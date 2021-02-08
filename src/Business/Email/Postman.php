<?php

declare(strict_types=1);

namespace App\Business\Email;

use Swift_Message;

/**
 * 消息服务接口
 */
interface Postman
{
    public function send(Swift_Message $message);
}
