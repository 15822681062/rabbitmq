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

$routingKey1  = 'alone.ex.queue1';
$routingKey2  = 'alone.ex.queue2';
$exchangeName = 'alone-ex-topic';

$channel->exchange_declare($exchangeName, AMQPExchangeType::TOPIC, false, true, false);

//向交换机和routingkey = alone-ex-queue1中推送10条数据
for ($i = 0; $i < 10; $i++) {
	$channel->basic_publish(
		new AMQPMessage("this is a queue1 message" . $i . ".", [
    		'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT //消息持久化，重启rabbitmq，消息不会丢失
    	]), 
		$exchangeName, 
		$routingKey1
	);
}
//向交换机和routingkey = alone-ex-queue2中推送10条数据
for ($i = 0; $i < 10; $i++) {
	$channel->basic_publish(
		new AMQPMessage("this is a queue2 message" . $i . ".", [
    		'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT //消息持久化，重启rabbitmq，消息不会丢失
    	]), 
		$exchangeName, 
		$routingKey2
	);
}

//关闭连接
$channel->close();
$amqp->close();
