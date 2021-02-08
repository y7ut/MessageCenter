<?php
/**
 * 服务启动文件
 * createBy: YiChu
 */
declare(strict_types=1);


require __DIR__."/../src/bootstrap.php";

// 协程化
Co::set(['hook_flags'=> SWOOLE_HOOK_ALL]);

$app->start();
