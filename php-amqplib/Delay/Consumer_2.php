<?php
require_once '../vendor/autoload.php';
require_once '../../config.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

/**
  * 消费者(处理订单)
  */

//创建mq链接
$amqp = new AMQPStreamConnection($config_host, $config_port, $config_user, $config_password, $config_vhost, false, 'AMQPLAIN', null, 'en_US', 30, 30);
//创建信道
$channel = $amqp->channel();

$queueName = 'ttl-order-queue';//订单queue

$callback = function ($msg) {
    echo $msg->body . PHP_EOL;
    //处理下单逻辑。。。

    //成功后应答，并删除队列中的订单消息
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);  
    sleep(10);
};

/**
 * 消费订单信息，进行处理
 */
//只有consumer已经处理并确认了上一条message时queue才分派新的message给它
$channel->basic_qos(null, 1, null);
$channel->basic_consume($queueName, '', false, false, false, false, $callback);
while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$amqp->close();
