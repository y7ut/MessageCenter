<?php

namespace App\Business\Email\Message;

use Swift_Attachment;
use Swift_Message;

/**
 * 模板的Message类
 */
class CreateCustomerNotify extends EmailMessage
{
    /**
     *  HR手册路径 必须使用英文字母开头 不然附件名会被截取
     */
//    const HR_ATTACH_FILE = 'https://static.ijiwei.com/HR版集微招聘平台操作指南.pdf';
    const HR_ATTACH_FILE = '/../../../../static/attachment/HR版集微招聘平台操作指南.pdf';

    /**
     * 猎头手册路径 必须使用英文字母开头 不然附件名会被截取
     */
//    const HUNTER_ATTACH_FILE = 'https://static.ijiwei.com/EMP集微招聘平台操作指南猎头版.pdf';
    const HUNTER_ATTACH_FILE = '/../../../../static/attachment/EMP集微招聘平台操作指南猎头版.pdf';

    /**
     * 主题
     *
     * @var string
     */
    protected $subject = '爱集微客户服务';

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
        ],
        'username' => [
            'require' => true,
            'type'    => 'string',
        ],
        'password' => [
            'require' => true,
            'type'    => 'string',
        ],
        'customer_type' => [
            'require'     => false,
            'type'        => 'enum',
            'default'     => 'hr',
            'enum_params' => [
                'hr',
                'hunter',
            ],
        ],
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

        $username = $message->arguments['username'];
        $password = $message->arguments['password'];
        $email    = $message->arguments['address'];
        $type     = $message->arguments['customer_type'];

        //将大写类名转换为下划线分割的小写模板名
        $template_name = strtolower(preg_replace('/([a-z])([A-Z])/', '$1'.'_'.'$2', substr(strrchr(__CLASS__, '\\'), 1)));
        $template      = sprintf('/../../../../static/template/email/%s.html', $template_name);
        $body          = sprintf(file_get_contents(__DIR__.$template), $username, $password);
        switch ($type) {
            case 'hunter':
                $attach = __DIR__.self::HUNTER_ATTACH_FILE;
                break;
            case 'hr':
                $attach = __DIR__.self::HR_ATTACH_FILE;
                break;
            default:
                $attach = __DIR__.self::HR_ATTACH_FILE;
                break;
        }

        // Create the message
        $swift_message = (new Swift_Message())

            // Give the message a subject
            ->setSubject($message->subject)

            // Set the From address with an associative array
            ->setFrom($message->from)

            // Set the To addresses with an associative array (setTo/setCc/setBcc)
            ->setTo([$email])

            // Give it a body
            ->setBody($body, $message->contentType)

            // Optionally add any attachments
            ->attach(Swift_Attachment::fromPath($attach));

        return $swift_message;
    }
}
