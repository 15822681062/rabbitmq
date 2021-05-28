<?php
declare(strict_types = 1); //针对参数类型开启严格模式，进行数据类型检验，默认是弱类型校验
require_once '../config.php';

// 定义mq连接信息
$amqp = new AMQPConnection([
    'host' => $config_host,
    'port' => $config_port,
    'vhost' => $config_vhost,
    'login' => $config_user,
    'password' => $config_password
]);
// 连接mq
$amqp->connect();
// 通过mq连接创建信道
$channel = new AMQPChannel($amqp);
// 通过信道创建交换机对象
$exchange = new AMQPExchange($channel);
// 设置交换机名称
$exchange->setName('direct_exchange');
//指定类型为直达型，一个交换机对应一个队列,只有绑定路由的队列能收到消息
$exchange->setType(AMQP_EX_TYPE_DIRECT);
//持久化
$exchange->setFlags(AMQP_DURABLE); 
// 声明交换机
$exchange->declareExchange();


//发送消息
for($i=0; $i<10; ++$i){
    sleep(1);
    echo "Send Message:".$exchange->publish("TEST MESSAGE" . date('H:i:s', time()), 'direct_routing_key_1')."\n";
}

//断开mq链接
$amqp->disconnect();
