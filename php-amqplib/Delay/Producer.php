<?php
require_once '../vendor/autoload.php';
require_once '../../config.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
/**
  * 生产者
  */

//创建mq链接
$amqp = new AMQPStreamConnection($config_host, $config_port, $config_user, $config_password, $config_vhost, false, 'AMQPLAIN', null, 'en_US', 30, 30);
//创建信道
$channel = $amqp->channel();

//设置订单信息
$ttl            = 1000 * 100;//订单100s后超时
$delayExName    = 'delay-order-exchange';//超时exchange
$delayQueueName = 'delay-order-queue';//超时queue
$queueName      = 'ttl-order-queue';//订单queue

$args = new AMQPTable([
    'x-dead-letter-exchange'    => $delayExName,
    'x-message-ttl'             => $ttl, //消息存活时间
    'x-dead-letter-routing-key' => $queueName
]);

//创建订单队列
$channel->queue_declare($queueName, false, true, false, false, false, $args);
//绑定死信queue
$channel->exchange_declare($delayExName, AMQPExchangeType::DIRECT, false, true, false);
$channel->queue_declare($delayQueueName, false, true, false, false);
$channel->queue_bind($delayQueueName, $delayExName, $queueName, false);

//10个订单信息，每个订单超时时间都是100s
for ($i = 0; $i < 15; $i++) {
    $data = [
        'order_id' => $i + 1,
        'remark'   => 'this is a order test'
    ];
    $data = json_encode($data);
    $message = new AMQPMessage($data, []);
    $channel->basic_publish($message, '', $queueName);
    sleep(1);
}


$channel->close();
$amqp->close();