# MessageCenter

## 安装
需要Swoole4.4+
```
composer install --no-dev --optimize-autoloader --prefer-dist
```

## 启动服务

```shell
php bin/start.php
```

## 客户端

#### 通过 redis-cli 访问

 通过redis客户端登录服务后， 可以通过`mail`命令使用`CreateCustomerNotify` (与模板的Message类名一致)这个KEY来异步的发送`创建用户`的消息通知。

```shell
$ redis-cli -h 127.0.0.1  -p 9223
127.0.0.1:9223> mail CreateCustomerNotify '{"username":"hei bro","password":"1q","address":"820054058@qq.com","customer_type":"hr"}
127.0.0.1:9223> mail TestDemo '{"username":"hei bro"}
```

#### 通过任意语言的redis扩展访问

同上
