<?php

declare(strict_types=1);

namespace App\Business\Email;

use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

/**
 * 基于SMTP的邮件服务
 */
class SmtpServer implements Postman
{
    const MESSAGE_TEMPLATE = [
        'created_customer_notify',
    ];

    /** @var Swift_SmtpTransport */
    private $transport;

    /** @var Swift_Mailer */
    private $mailer;

    /**
     * Postman constructor.
     *
     * @param string $smtp_host 邮件服务器
     * @param int    $port      端口
     * @param string $user      用户名
     * @param string $pass      密码
     */
    public function __construct(string $smtp_host, int $port, string $user, string $pass)
    {
        $this->transport =  (new Swift_SmtpTransport($smtp_host, $port))
            ->setUsername($user)
            ->setPassword($pass)
        ;
        $this->mailer = new Swift_Mailer($this->transport);
    }

    /**
     * 发送邮件
     *
     * @param Swift_Message $message
     *
     * @return int
     */
    public function send(Swift_Message $message)
    {
        $result = $this->mailer->send($message);
        // 释放空间
        $this->destroy();
        return $result;
    }

    /**
     * 释放内存
     */
    public function destroy()
    {
        $this->transport = null;
        $this->mailer    = null;
    }
}
