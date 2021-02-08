<?php

declare(strict_types=1);

namespace App\Provider;

use App\Business\Email\Postman;
use App\Exception\ArgumentException;
use App\Exception\SendMailException;
use InvalidArgumentException;
use Monolog\Logger;
use Swift_RfcComplianceException;

/**
 * Class MailProvider
 * 邮件发送器
 */
class MailProvider
{
    /**
     * @var Postman 邮件基础服务
     */
    protected $server;

    /**
     * @var string 邮件模板名
     */
    protected $template;

    /**
     * @var Logger 日志
     */
    protected $logger;

    /**
     * @var string 任务编号
     */
    protected $task_no;

    const MESSAGE_NAMESPACE = 'App\\Business\\Email\\Message\\';

    /**
     * MailCenter constructor.
     *
     * 使用前需要声明所使用的的邮件模板
     *
     * @param Postman $postman
     * @param Logger  $logger
     * @param string  $template
     * @param string  $taskNo
     */
    public function __construct(Postman $postman, Logger $logger, string $template, string $taskNo)
    {
        $this->server   = $postman;
        $this->logger   = $logger;
        $this->template = self::MESSAGE_NAMESPACE.$template;
        $this->task_no  = $taskNo;
    }

    /**
     * 邮件发送
     *
     * @param $argument
     *
     * @throws ArgumentException
     * @throws SendMailException
     *
     * @return mixed
     */
    public function sendMail($argument)
    {
        try {
            $message = call_user_func([$this->template, 'format'], $argument);
            $result  = $this->server->send($message);
        } catch (InvalidArgumentException $e) {
            $this->logger->error(sprintf('params error！'), ['no' => $this->task_no, 'params' => $argument]);
            throw new ArgumentException($e->getMessage());
        } catch (Swift_RfcComplianceException $e) {
            $this->logger->error(sprintf('email sender error！'), ['no' => $this->task_no, 'params' => $argument]);
            throw new SendMailException($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error(sprintf('some terrible error！'), ['no' => $this->task_no, 'params' => $argument]);
            throw new SendMailException($e->getMessage());
        }

        if ($result) {
            $this->logger->Info(sprintf('Email has success send！'), ['no' => $this->task_no, 'params' => $argument]);
        }

        return $result;
    }
}
