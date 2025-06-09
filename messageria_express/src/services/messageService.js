const { connectRabbitMQ } = require("../config/rabbitmq");
const axios = require("axios");
const { createClient } = require("redis");
let redisClient;

(async () => {
  redisClient = createClient({
    socket: {
    host: process.env.REDIS_HOST || 'redis',
    port: process.env.REDIS_PORT || 6379
  }
  });
  redisClient.on("error", (err) => console.error("Redis Client Error", err));
  await redisClient.connect();
})();

async function autenticateUser(token) {
  const redisKey = `auth:${token}`;

  try {
    let cachedData = await redisClient.get(redisKey);
    let authData = cachedData ? JSON.parse(cachedData) : null;

    if (!authData) {
      const response = await axios.get("http://auth-app:80/users/fetch", {
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
        },
        timeout: 5000,
      });

      authData = response.data;

      await redisClient.set(redisKey, JSON.stringify(authData), {
        EX: 120,
        NX: true,
      });
    }

    if (!authData?.success) {
      return { success: false, error: "Usuário não autenticado" };
    }

    return { success: true, data: authData };
  } catch (error) {
    console.error("Erro na autenticação:", error.message);
    if (error.message.includes("Autenticação falhou")) {
      await redisClient.del(redisKey);
    }

    return {
      success: false,
      error: error.response?.data?.message || error.message,
    };
  }
}

async function sendMessageService(message, userIdSend, userIdReceive, token) {
  try {
    const authenticator = await autenticateUser(token);
    if (!authenticator.success) {
      return { success: false, error: "Usuário não autenticado" };
    }

    const result = await new Promise((resolve, reject) => {
      connectRabbitMQ((error, channel) => {
        if (error) {
          return reject({ success: false, error: "Erro no RabbitMQ" });
        }

        const queueName = `${userIdSend}_${userIdReceive}`;

        channel.assertQueue(queueName, { durable: true });
        channel.sendToQueue(queueName, Buffer.from(JSON.stringify(message)), {
          persistent: true,
        });

        resolve({ success: true, data: "Mensagem enviada com sucesso" });
      });
    });

    return result;
  } catch (error) {
    return {
      success: false,
      error: error.message || "Erro ao enviar mensagem",
    };
  }
}

async function callMessageAPI(messageData) {
  try {
    const response = await axios.post(
      "http://localhost:8000/messages",
      messageData
    );
    return response.data;
  } catch (error) {
    throw error;
  }
}

function parseMessageContent(content) {
  const str = content.toString();

  try {
    const parsed = JSON.parse(str);
    return {
      message: parsed.message || parsed.text || str,
      ...parsed,
    };
  } catch {
    return { message: str };
  }
}

async function receiveMessageService(userIdSend, userIdReceive, token) {
  return new Promise(async (resolve, reject) => {
    try {
      // 2. Autenticação
      const authenticator = await autenticateUser(token);
      if (!authenticator.success) {
        return resolve({ success: false, error: "Usuário não autenticado" });
      }

      // 3. Verifica cache no Redis
      const redisKey = `messages:${userIdSend}_${userIdReceive}`;
      let cachedMessages = [];

      try {
        cachedMessages = await redisClient.lRange(redisKey, 0, -1);
        if (cachedMessages.length > 0) {
          return resolve({
            success: true,
            data: cachedMessages,
            source: "redis",
          });
        }
      } catch (redisErr) {}

      // 4. Busca mensagens no RabbitMQ
      connectRabbitMQ(async (error, channel) => {
        if (error) {
          return reject({ success: false, error: "Erro no RabbitMQ" });
        }

        const queueName = `${userIdSend}_${userIdReceive}`;

        channel.assertQueue(queueName, { durable: true }, async (err) => {
          if (err) {
            channel.close();
            return reject({ success: false, error: "Erro ao declarar fila" });
          }

          channel.get(queueName, { noAck: true }, async (err, msg) => {
            channel.close();

            if (err) {
              return reject({
                success: false,
                error: "Erro ao receber mensagem",
              });
            }

            if (!msg) {
              return resolve({
                success: true,
                data: [],
                source: "rabbitmq",
                message: "Nenhuma mensagem na fila",
              });
            }

            try {
              // Processa a mensagem
              const messageContent = parseMessageContent(msg.content);
              const messageData = {
                message: messageContent.message,
                user_id_send: parseInt(userIdSend),
                user_id_receive: parseInt(userIdReceive),
                timestamp: messageContent.timestamp || new Date().toISOString(),
              };

              // Armazena no Redis
              try {
                await redisClient.rPush(redisKey, msg.content.toString());
                await redisClient.expire(redisKey, 120);
              } catch (redisErr) {}

              // Chama API externa
              await callMessageAPI(messageData);

              resolve({
                success: true,
                data: [msg.content.toString()],
                source: "rabbitmq",
              });
            } catch (error) {
              reject({
                success: false,
                error: "Erro ao processar mensagem",
                details: error.message,
              });
            }
          });
        });
      });
    } catch (error) {
      reject({
        success: false,
        error: "Erro interno",
        details: error.message,
      });
    }
  });
}

module.exports = { sendMessageService, receiveMessageService };
