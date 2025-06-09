const amqp = require('amqplib/callback_api');

function connectRabbitMQ(callback) {
  const host = process.env.RABBITMQ_HOST || 'localhost';
  const port = process.env.RABBITMQ_PORT || '5672';
  const user = process.env.RABBITMQ_USER || 'guest';
  const pass = process.env.RABBITMQ_PASS || 'guest';

  const url = `amqp://${user}:${pass}@${host}:${port}`;
  
  amqp.connect(url, (error0, connection) => {
    if (error0) {
      console.error('Erro ao conectar ao RabbitMQ:', error0);
      return callback(error0);
    }

    connection.createChannel((error1, channel) => {
      if (error1) {
        console.error('Erro ao criar canal:', error1);
        return callback(error1);
      }
      
      callback(null, channel);
    });
  });
}

module.exports = { connectRabbitMQ };