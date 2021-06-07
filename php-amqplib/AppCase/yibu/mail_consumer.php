<?php
require_once '../../vendor/autoload.php';
require_once '../../../config.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

/**
  * 消费者（发送邮件）
  */

//创建mq链接
$amqp = new AMQPStreamConnection($config_host, $config_port, $config_user, $config_password, $config_vhost, false, 'AMQPLAIN', null, 'en_US', 30, 30);
//创建信道
$channel = $amqp->channel();

$queueName = 'yibu-ex-mail';
$routingKey  = 'yibu-ex-routing';
$exchangeName = 'yibu-ex-direct';

//声明交换机
$channel->exchange_declare($exchangeName, AMQPExchangeType::DIRECT, false, true, false);
// 声明队列
$channel->queue_declare($queueName, false, false, false, false);
// 使用routeKey绑定交换机和队列
$channel->queue_bind($queueName, $exchangeName, $routingKey);


/**
 * 消费队列消息
 */
$callback = function ($msg) {
    echo $msg->body . PHP_EOL;
    //发送邮件，并处理相关业务
    //......................

    //成功后发送应答，删除队列中对应的消息
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};
//只有consumer已经处理并确认了上一条message时queue才分派新的message给它
$channel->basic_qos(null, 1, null);
$channel->basic_consume($queueName, '', false, false, false, false, $callback);
while ($channel->is_consuming()) {
    $channel->wait();
}


//关闭
$channel->close();
$amqp->close();
