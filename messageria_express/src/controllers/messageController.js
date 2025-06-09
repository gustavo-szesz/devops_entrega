const {
  sendMessageService,
  receiveMessageService,
} = require("../services/messageService");

exports.sendMessageController = async (req, res) => {
  try {
    const token = req.headers.authorization?.replace("Bearer ", "");

    if (!token) {
      return res.status(401).json({
        success: false,
        error: "Token de autenticação não fornecido",
      });
    }

    if (!req.body.message || !req.body.userIdSend || !req.body.userIdReceive) {
      return res.status(400).json({
        success: false,
        error:
          "Parâmetros incompletos (message, userIdSend, userIdReceive são obrigatórios)",
      });
    }

    const result = await sendMessageService(
      req.body.message,
      req.body.userIdSend,
      req.body.userIdReceive,
      token
    );

    if (!result.success) {
      if (result.error === "Usuário não autenticado") {
        return res.status(401).json({
          success: false,
          error: result.error,
        });
      }
      return res.status(result.statusCode || 400).json({
        success: false,
        error: result.error || "Erro ao enviar mensagem",
      });
    }

    return res.status(200).json({
      success: true,
      data: "Message sent successfully",
    });
  } catch (error) {
    console.error("Erro no controller:", error);
    return res.status(500).json({
      success: false,
      error: "Erro interno no servidor",
      details:
        process.env.NODE_ENV === "development" ? error.message : undefined,
    });
  }
};

exports.receiveMessageController = async (req, res) => {
  try {

    const token = req.headers.authorization?.replace("Bearer ", "");

    if (!token) {
      return res.status(401).json({
        success: false,
        error: "Token de autenticação não fornecido",
      });
    }

    if (!req.body.userIdSend || !req.body.userIdReceive) {
      return res.status(400).json({
        success: false,
        error:
          "Parâmetros incompletos (userIdSend, userIdReceive são obrigatórios)",
      });
    }

    result = await receiveMessageService(
      req.body.userIdSend,
      req.body.userIdReceive,
      token
    );

    if (!result.success) {
      if (result.error === "Usuário não autenticado") {
        return res.status(401).json({
          success: false,
          error: result.error,
        });
      }
      return res.status(result.statusCode || 400).json({
        success: false,
        error: result.error || "Erro ao enviar mensagem",
      });
    }

    return res.status(200).json({
      success: true,
      data: "Message received successfully",
    });
  } catch (error) {
    console.error("Erro no controller:", error);
    return res.status(500).json({
      success: false,
      error: "Erro interno no servidor",
      details:
        process.env.NODE_ENV === "development" ? error.message : undefined,
    });
  }
};
