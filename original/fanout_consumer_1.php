<?php
declare(strict_types = 1); //针对参数类型开启严格模式，进行数据类型检验，默认是弱类型校验
require_once '../config.php';

// 交换器名称
$exchangeName = 'fanout_exchange';
// 队列名称
$queueName = 'fanout_product_1';
// RouteKey
$routeKey = 'fanout_routing_key_1';
// 定义mq连接信息
$amq = new AMQPConnection([
    'host' => $config_host,
    'port' => $config_port,
    'vhost' => $config_vhost,
    'login' => $config_user,
    'password' => $config_password
]);
// 连接mq
$amq->connect();

// 通过mq连接创建信道
$channel = new AMQPChannel($amq);

// 通过信道创建交换机对象
$exchange = new AMQPExchange($channel);
// 设置交换机名称
$exchange->setName($exchangeName);
//指定类型为扩散型，一个交换机对应多个队列,交换机下所有队列都能收到消息
$exchange->setType(AMQP_EX_TYPE_FANOUT); 
//持久化
$exchange->setFlags(AMQP_DURABLE); 
$exchange->declareExchange();


// 声明自己监听哪个队列
$queue = new AMQPQueue($channel);
$queue->setName($queueName);
$queue->setFlags(AMQP_DURABLE); //持久化
$queue->declareQueue();

//绑定交换机与队列，并指定路由键
$queue->bind($exchangeName,$routeKey);

//阻塞模式接收消息
echo "Message:\n";
while (true) {
    // 若不加第二个参数，则回调里要手动发送应答，为避免处理程序终止，导致消息丢失，通常采用处理程序手动应答的方式处理消息。
    // $queue->consume('processMessage', AMQP_AUTOACK); //自动ACK应答
    $queue->consume('processMessage'); //手动ACK应答
}
$conn->disconnect();

/**
 * 消费回调函数
 * 处理业务
 */
function processMessage($envelope, $queue) {
    $msg = $envelope->getBody();
    echo $msg."\n"; //处理消息
    // 处理完成，手动发送ack应答，清除此消息
    $queue->ack($envelope->getDeliveryTag());
}

