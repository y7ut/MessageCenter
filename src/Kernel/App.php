<?php

declare(strict_types=1);

namespace App\Kernel;

use App\Kernel\RedisHandle\MailHandle;
use DI\Container;

/**
 * 应用核心
 */
class App
{
    protected $server;

    protected $logger;

    /**
     * 加载容器 分配给各个行为事件回调方法
     * @param Container $container
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function __construct(Container $container)
    {
        $this->server = $container->get('RedisServer');

        $worker = new ServiceWorker($container);

        $this->server->set([
            'task_worker_num' => 4,
            'worker_num'      => 4,
        ]);

        $this->server->setHandler('MAIL', $container->make('HandleMail', ['server' => $this->server]));
        $this->server->on('WorkerStart', [$worker, 'onWorkerStart']);
        $this->server->on('Task', [$worker, 'onTask']);
        $this->server->on('Finish', [$worker, 'onFinish']);

        $logger = $container->get('logger');
        $logger->Info('APP is running.');
    }

    /**
     * 启动app
     */
    public function start()
    {
        $this->server->start();
    }
}
