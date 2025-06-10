const { connectRabbitMQ } = require("./config/rabbitmq");
const { createClient } = require("redis");
const axios = require("axios");

let redisClient;

(async () => {
  redisClient = createClient({
    socket: {
      host: process.env.REDIS_HOST || "redis",
      port: process.env.REDIS_PORT || 6379,
    },
  });
  redisClient.on("error", (err) => console.error("Redis Client Error", err));
  await redisClient.connect();
})();

function parseMessageContent(content) {
  try {
    return JSON.parse(content.toString());
  } catch {
    return { message: content.toString() };
  }
}

async function callMessageAPI(messageData) {
  try {
    const response = await axios.post("http://localhost:8000/messages", messageData);
    return response.data;
  } catch (error) {
    console.error("Erro ao chamar API externa:", error.message);
    throw error;
  }
}

function startWorker() {
  connectRabbitMQ((err, channel) => {
    if (err) {
      console.error("Erro ao conectar RabbitMQ:", err);
      process.exit(1);
    }

    const queueName = "userIdSend_userIdReceive";

    channel.assertQueue(queueName, { durable: true });

    console.log(`[*] Aguardando mensagens na fila ${queueName}`);

    channel.consume(
      queueName,
      async (msg) => {
        if (msg) {
          try {
            const messageContent = parseMessageContent(msg.content);
            const messageData = {
              message: messageContent.message,
              user_id_send: messageContent.userIdSend,
              user_id_receive: messageContent.userIdReceive,
              timestamp: messageContent.timestamp || new Date().toISOString(),
            };

            await callMessageAPI(messageData);

          
            const redisKey = `messages:${messageData.user_id_send}_${messageData.user_id_receive}`;
            await redisClient.rPush(redisKey, msg.content.toString());
            await redisClient.expire(redisKey, 120);

            channel.ack(msg);
            console.log("[x] Mensagem processada e confirmada");
          } catch (error) {
            console.error("Erro ao processar mensagem:", error);
            channel.nack(msg, false, true);
          }
        }
      },
      { noAck: false }
    );
  });
}

startWorker();
