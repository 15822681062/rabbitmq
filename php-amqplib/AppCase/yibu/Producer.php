<?php
require_once '../../vendor/autoload.php';
require_once '../../../config.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
/**
  * 生产者(用户注册)
  */

//用户注册，写入数据库
$servername = "127.0.0.1";
$username = "root";
$password = "123456";
$dbname = "test";
 
// 创建连接
$conn = new mysqli($servername, $username, $password, $dbname);
 
// 检测连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
} 
//插入数据
$sql = "INSERT INTO user (name, sex, age, phone, email) VALUES ('wangruishan', '男', '30', '15822681062', '1195965103@qq.com')";
if ($conn->query($sql) === FALSE) {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
$conn->close();


/**
 * 注册成功后，发送数据到mq
 */
//创建mq链接
$amqp = new AMQPStreamConnection($config_host, $config_port, $config_user, $config_password, $config_vhost, false, 'AMQPLAIN', null, 'en_US', 30, 30);
//创建信道
$channel = $amqp->channel();

$routingKey  = 'yibu-ex-routing';
$exchangeName = 'yibu-ex-direct';
// $queueName = 'yibu-ex-queue';

//推送成功
$channel->set_ack_handler(
    function (AMQPMessage $message) {
        echo "发送成功: " . $message->body . PHP_EOL;
        registerSuccess();
    }
);
 
//推送失败
$channel->set_nack_handler(
    function (AMQPMessage $message) {
        echo "发送失败: " . $message->body . PHP_EOL;
        registerFail();
    }
);
/*
 * 进入发布确认模式。
 * 如果在将通道引入此模式之前或之后调用$ch->tx_select()
 * 下一个调用$ch->wait()将导致发布确认模式和事务异常
 * 是互斥的
 */
$channel->confirm_select(); // 发布确认模式 

//声明交换机
$channel->exchange_declare($exchangeName, AMQPExchangeType::DIRECT, false, true, false);
// // 队列
// $channel->queue_declare($queueName, false, false, false, false);
// // 使用routeKey绑定交换机和队列
// $channel->queue_bind($queueName, $exchangeName, $routingKey);

//推送数据
$user_info = [
    'name' => 'wangruishan',
    'sex' => '男',
    'age' => '30',
    'phone' => '15822681062',
    'email' => '1195965103@qq.com'
]; 
$user_info = json_encode($user_info); 
$channel->basic_publish(
    new AMQPMessage($user_info, [
        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT //消息持久化，重启rabbitmq，消息不会丢失
    ]), 
    $exchangeName, 
    $routingKey
);

/*
 *您不必在每条消息发送后等待挂起的acks。事实上，这样会更有效率
 *等待尽可能多的邮件被屏蔽。
 */
$channel->wait_for_pending_acks();
// 监听成功或失败返回结束 成功/失败 => set_ack_handler/set_nack_handler


//关闭连接
$channel->close();
$amqp->close();





/**
 * 注册结果
 */
//注册成功
function registerSuccess(){
    echo "注册成功";
    //处理业务
}

//注册失败
function registerFail(){
    echo "注册失败";
    //处理业务
}