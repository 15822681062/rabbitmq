<?php
declare(strict_types = 1); //针对参数类型开启严格模式，进行数据类型检验，默认是弱类型校验
header('Content-Type: text/html; charset=utf-8');
require_once '../config.php';

// 交换器名称
$exchangeName = 'headers_exchange';
// 队列名称
$queueName = 'headers_product_2';
// RouteKey
$routeKey = 'headers.routing.key.2';
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
//指定类型为header，Exchange不依赖于routing key与binding key的匹配规则来路由消息，而是根据发送的消息内容中的headers属性进行匹配。
$exchange->setType(AMQP_EX_TYPE_HEADERS); 
//持久化
$exchange->setFlags(AMQP_DURABLE); 
$exchange->declareExchange();


// 声明自己监听哪个队列
$queue = new AMQPQueue($channel);
$queue->setName($queueName);
$queue->setFlags(AMQP_DURABLE); //持久化
$queue->declareQueue();

// 设定队列的headers信息，x-match：all：全匹配，消息的headers信息与队列的必须完全匹配，any：消息的headers消息与队列的任意一项匹配即可
$headers = array(
    'x-match' => 'any',
    'type' => 'headers',
    'user' => 'user'
);

//绑定交换机与队列，任意设置routing key
$queue->bind($exchangeName, $routeKey, $headers);

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
