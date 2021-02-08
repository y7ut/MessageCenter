<?php

namespace App\Business\Email\Message;

use Swift_Attachment;
use Swift_Message;

/**
 * 模板的Message类
 */
class TestDemo extends EmailMessage
{

    /**
     * 主题
     *
     * @var string
     */
    protected $subject = '邮件服务测试';

    /**
     * 发件人
     *
     * @var string
     */
    protected $from = [
        'postman@mail.ijiwei.com' => '小微',
    ];

    /**
     * 邮件内容格式
     *
     * @var string
     */
    protected $contentType = 'text/html';

    /**
     * 模板参数
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * 模板变量参数
     *
     * @var array
     */
    protected $templateArgumentRules = [
        'address' => [
            'require' => true,
            'type'    => 'string', // 可用条件 int enum (需要配合枚举字段使用) string
        ]
    ];

    /**
     * CreateCustomerNotify constructor.
     *
     * @param string $data json参数字符串
     */
    protected function __construct(string $data)
    {
        $this->arguments = json_decode($data, true);
        $this->argumentCheck();
    }

    /**
     * 生产格式化的Swift_Message
     *
     * @param string $data
     *
     * @return Swift_Message
     */
    public static function format(string $data)
    {
        $message = new self($data);
        
        $email    = $message->arguments['address'];

        //将大写类名转换为下划线分割的小写模板名
        $template_name = strtolower(preg_replace('/([a-z])([A-Z])/', '$1'.'_'.'$2', substr(strrchr(__CLASS__, '\\'), 1)));
        $template      = sprintf('/../../../../static/template/email/%s.html', $template_name);
        $body          = sprintf(file_get_contents(__DIR__.$template));
        

        // Create the message
        $swift_message = (new Swift_Message())

            // Give the message a subject
            ->setSubject($message->subject)

            // Set the From address with an associative array
            ->setFrom($message->from)

            // Set the To addresses with an associative array (setTo/setCc/setBcc)
            ->setTo([$email])
    
            // Optionally add any attachments
            ->attach(Swift_Attachment::fromPath('http://s.laoyaoba.com/jwImg/advertItem/2020/09/10/15997273996323.jpg'))
            // Give it a body
            ->setBody($body, $message->contentType);

        return $swift_message;
    }
}
