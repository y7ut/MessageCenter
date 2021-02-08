<?php
declare(strict_types=1);

use App\Business\Email\SmtpServer;
use function DI\get;
use function DI\autowire;
use Monolog\Logger;

/*
 * 容器定义
 */

$container = [
    //默认的redis 连接实例
    'redis' => function ($c) {
        $redis = new \Redis();
        $host = $c->get('settings')['redis']['default']['host'];
        $port = $c->get('settings')['redis']['default']['port'];
        $auth = $c->get('settings')['redis']['default']['password'];
        if (is_null($host) || is_null($port) || is_null($auth)) {
            throw new \Exception (sprintf('Redis lack of necessary configuration'));
        }
        $redis->connect($host, $port);
        if (!$redis->auth($auth)) {
            throw new \Exception (sprintf('Fail to auth the redis server,%s', $redis->getLastError()));
        }
        $redis->select(0);
        return $redis;
    },
    'logger' => function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger = new Logger($settings['name'] . '-handle');
        $date = date('Ymd', time());
        $path = $settings['path'] . '/' . $settings['name'] . '_handle_' . $date . '.log';
        $handler = new Monolog\Handler\StreamHandler($path, Monolog\Logger::DEBUG);
        // 换回使用文本格式，因为 JSON 格式在控制台观察时冗余信息太多了，比较聒噪
        $formatter = new Monolog\Formatter\LineFormatter(
            '[%datetime%] %channel%.%level_name%: %message% %context%' . PHP_EOL,
            'Y-m-d H:i:s',
            false,
            false
        );

        //设置格式
        $handler->setFormatter($formatter);
        // 设置处理器
        $logger->pushHandler($handler);
        return $logger;
    },
    'worker_logger' => function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger = new Logger($settings['name'] . '-worker');
        $date = date('Ymd', time());
        $path = $settings['path'] . '/' . $settings['name'] . '_worker_' . $date . '.log';
        $handler = new Monolog\Handler\StreamHandler($path, Monolog\Logger::DEBUG);
        // 换回使用文本格式，因为 JSON 格式在控制台观察时冗余信息太多了，比较聒噪
        $formatter = new Monolog\Formatter\LineFormatter(
            '[%datetime%] %channel%.%level_name%: %message% %context%' . PHP_EOL,
            'Y-m-d H:i:s',
            false,
            false
        );

        //设置格式
        $handler->setFormatter($formatter);
        // 设置处理器
        $logger->pushHandler($handler);
        return $logger;
    },
    // 核心服务
    'RedisServer'=> autowire(Swoole\Redis\Server::class)
        ->constructor(
            get('service.host'),
            get('service.port'),
            SWOOLE_PROCESS,
            SWOOLE_SOCK_TCP
        ),
    'SmtpServer' => autowire(SmtpServer::class)
        ->constructor(
            get('smtp.host'),
            get('smtp.port'),
            get('smtp.user'),
            get('smtp.password')
        ),
    'HandleMail' => autowire(\App\Kernel\RedisHandle\MailHandle::class)
        ->constructorParameter('logger', get('logger'))
        ->constructorParameter('redis', get('redis')),
    //邮件服务所需要的配置 ，目前MailCenter 依赖注入的是smtp的服务器
    'Mail' => autowire(\App\Provider\MailProvider::class)
        ->constructorParameter('postman', get('SmtpServer'))
        ->constructorParameter('logger', get('worker_logger')),
];

$Settings = require_once(__DIR__ . '/settings.php');
$container = array_merge($container, $Settings);

return $container;
