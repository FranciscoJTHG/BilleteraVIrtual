const express = require('express');
const router = express.Router();
const walletRoutes = require('./wallet');

router.get('/health', (req, res) => {
  res.json({
    success: true,
    status: 'ok',
    service: 'rest-service',
    timestamp: new Date().toISOString()
  });
});

router.use('/wallet', walletRoutes);

module.exports = router;
