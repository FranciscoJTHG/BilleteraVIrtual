const fs = require('fs');
const path = require('path');

const logsDir = path.join(__dirname, '../../logs');
if (!fs.existsSync(logsDir)) {
  fs.mkdirSync(logsDir, { recursive: true });
}

const errorLogStream = fs.createWriteStream(
  path.join(logsDir, 'error.log'),
  { flags: 'a' }
);

const errorHandler = (err, req, res, next) => {
  const errorLog = {
    timestamp: new Date().toISOString(),
    method: req.method,
    url: req.url,
    statusCode: err.statusCode || 500,
    message: err.message,
    stack: process.env.NODE_ENV === 'development' ? err.stack : undefined,
    remoteAddr: req.ip
  };

  const logEntry = `${errorLog.timestamp} | ${errorLog.method} ${errorLog.url} | Status: ${errorLog.statusCode} | Message: ${errorLog.message} | IP: ${errorLog.remoteAddr}${errorLog.stack ? '\n' + errorLog.stack : ''}\n`;
  
  errorLogStream.write(logEntry);
  
  if (process.env.NODE_ENV === 'development') {
    console.error('[ERROR]', errorLog);
  }

  const statusCode = errorLog.statusCode;

  res.status(statusCode).json({
    success: false,
    cod_error: 'ERR_' + statusCode,
    message_error: err.message || 'Error interno del servidor',
    details: process.env.NODE_ENV === 'development' ? err.stack : undefined
  });
};

module.exports = errorHandler;
