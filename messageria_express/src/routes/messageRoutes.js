const express = require('express');
const router = express.Router();
const { sendMessageController, receiveMessageController } = require('../controllers/messageController');
const { healthController } = require('../controllers/healthController');

router.get('/health', healthController)
router.post('/message', sendMessageController);
router.post('/message/worker', receiveMessageController);

module.exports = router;