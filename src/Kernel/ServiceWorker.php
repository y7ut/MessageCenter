<?php

declare(strict_types=1);

namespace App\Kernel;

use App\Exception\ArgumentException;
use App\Exception\SendMailException;

class ServiceWorker{

    protected $container;

    protected $logger;

    protected $redis;

    /**
     * ServiceWorker constructor.
     *
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->redis = $container->get('redis');
        $this->logger = $container->get('logger');
    }

    /**
     * 工作进程启动
     *
     * @param $server
     * @param $worker_id
     */
    public function onWorkerStart($server, $worker_id)
    {
        $this->logger->Info(sprintf('WORK ID is %s', $worker_id));
    }

    /**
     * 任务结束
     *
     * @param $server
     * @param int   $task_id
     * @param array $data   [template, taskNo]
     */
    public function onFinish($server, int $task_id, array $data)
    {
        $this->redis->getset('mess_task:'.$data[1], (int) 1);
        $this->logger->Info(sprintf('Use %s Template, Success!', $data[0]), ['no' => $data[1]]);
    }

    /**
     * 任务处理逻辑
     *
     * @param $server
     * @param $taskID
     * @param $workID
     * @param $data
     */
    public function onTask($server, $taskID, $workID, $data)
    {
        $taskNo = $data['task_no'] ?? null;
        if (null === $taskNo) {
            $this->logger->error('Error task!');
        }
        $template         = $data['task_data'][0] ?? null;
        $templateArgument = $data['task_data'][1] ?? null;

        if (null === $template || null === $templateArgument) {
            $this->logger->error(sprintf('Error keyword with %s [template] , %s [argument_json]', $template, $templateArgument), ['no' => $taskNo]);
        }

        $this->logger->Info(sprintf('Template @%s@ is loading :Handle Task ID is %s , Work ID is %s, no is %s ', $template, $taskID, $workID, $taskNo), ['no' => $taskNo]);

        // 获取一个邮件发送服务
        $Mailer = $this->container->make('Mail', [
                'template' => $template,
                'taskNo'   => $taskNo, ]
        );

        try {
            if ($result = $Mailer->sendMail($templateArgument)) {
                $server->finish([$template, $taskNo]);
            }
        } catch (ArgumentException $e) {
            $this->logger->error(sprintf('Template @%s@ has running but params is wrong ! Look at %s :', $template, $e->getMessage()), ['no' => $taskNo]);
        } catch (SendMailException $e) {
            $this->logger->error(sprintf('Template @%s@ has running but something is wrong ! Look at %s :', $template, $e->getMessage()), ['no' => $taskNo]);
        }
    }
}
