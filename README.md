# php使用RabbitMQ的example

# 加载rabbitmq的composer包
composer require php-amqplib

# 目录说明
## original 原生php应用rabbitmq案例
direct_consumer_1.php  交换机类型为direct的消费者案例
direct_consumer_2.php  交换机类型为direct的消费者案例
direct_producer.php  交换机类型为direct的生产者案例

fanout_consumer_1.php  交换机类型为fanout的消费者案例
fanout_consumer_2.php  交换机类型为fanout的消费者案例
fanout_producer.php  交换机类型为fanout的生产者案例

headers_consumer_1.php  交换机类型为headers的消费者案例
headers_consumer_2.php  交换机类型为headers的消费者案例
headers_producer.php  交换机类型为headers的生产者案例

topic_consumer_1.php  交换机类型为topic的消费者案例
topic_consumer_2.php  交换机类型为topic的消费者案例
topic_producer.php  交换机类型为topic的生产者案例


## php-amqplib 依赖php-amqplib composer包开发
### Alone 
实现多个独立消费者
exchange的type=direct/topic

### Delay 目录
利用message的ttl、死信特性来实现延时队列
