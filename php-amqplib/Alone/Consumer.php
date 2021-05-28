<?php
require_once '../vendor/autoload.php';
require_once '../../config.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

//创建mq链接
$amqp = new AMQPStreamConnection($config_host, $config_port, $config_user, $config_password, $config_vhost, false, 'AMQPLAIN', null, 'en_US', 30, 30);
//创建信道
$channel = $amqp->channel();

$exchangeName = 'alone-ex-topic';
$queueName    = 'alone-consumer-ex-topic';
$routingKey   = 'alone.ex.*';//消费规则定义

//创建队列
$channel->queue_declare($queueName, false, true, false, false, false, []);
//绑定到交换机
$channel->queue_bind($queueName, $exchangeName, $routingKey, false, [], null);

//消费
$callback = function ($message) {
    var_dump("Received Message : " . date("Y-m-d H:i:s") . $message->body);//print message
    sleep(2);//处理耗时任务
    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);//ack
};


//只有consumer已经处理并确认了上一条message时queue才分派新的message给它
$channel->basic_qos(null, 1, null);
$channel->basic_consume($queueName, '', false, false, false, false, $callback);
while ($channel->is_consuming()) {
    $channel->wait();
}


//关闭连接
$channel->close();
$amqp->close();
