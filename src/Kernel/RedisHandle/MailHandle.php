<?php

declare(strict_types=1);

namespace App\Kernel\RedisHandle;

use Ramsey\Uuid\Nonstandard\Uuid;
use Swoole\Redis\Server;

/**
 * Mail Keyword 处理方法
 */
class MailHandle{
    /**
     * 回调状态保存时间（小时）
     */
    const STATUS_SAVE_HOUR = 24;

    protected $logger;
    protected $redis;
    protected $server;

    /**
     * Mail constructor.
     * @param $logger
     * @param $redis
     * @param $server
     */
    public function __construct($logger, $redis, $server)
    {
        $this->logger = $logger;
        $this->redis = $redis;
        $this->server = $server;

    }

    /**
     * keyWord Handle
     *
     * @param $fd
     * @param $data
     * @return mixed
     */
    public  function __invoke($fd, $data)
    {
        $template         = $data[0] ?? null;
        $templateArgument = $data[1] ?? null;

        if (null === $template || null === $templateArgument) {
            return $this->server->send($fd, Server::format(Server::ERROR, 'redis key word is invalid'));
        }

        if (!$this->templateCheckUp($template)) {
            return $this->server->send($fd, Server::format(Server::ERROR, 'Template ['.$template.']is not ready yet'));
        }

        $task_no = substr(Uuid::uuid1()->toString(), 0, 6);

        $taskData = [
            'task_data' => $data,
            'task_no'   => $task_no,
        ];

        $taskID = $this->server->task($taskData);

        $this->logger->Info(sprintf('Create Task ID is %s', $taskID), ['no' => $taskData['task_no']]);

        if (false === $taskID) {
            return $this->server->send($fd, Server::format(Server::ERROR));
        } else {
            $this->redis->set('mess_task:'.$task_no, (int) 0, 60 * 60 * self::STATUS_SAVE_HOUR);

            return $this->server->send($fd, Server::format(Server::STRING, $task_no));
        }
    }

    /**
     * 检测模板是否存在
     *
     * @param $template
     * @return bool
     */
    protected function templateCheckUp($template){
        $dir = __DIR__ . '/../../Business/Email/Message';
        $dh = opendir($dir);
        $files = [];
        while (($file = readdir($dh)) !== false) {
            $files[] = $file;
        }
        closedir($dh);
        if (!in_array($template . '.php', $files)) {
            return false;
        }
        return true;
    }
}
