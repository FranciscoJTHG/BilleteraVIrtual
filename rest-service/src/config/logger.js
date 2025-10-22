const morgan = require('morgan');
const fs = require('fs');
const path = require('path');

const logsDir = path.join(__dirname, '../../logs');

if (!fs.existsSync(logsDir)) {
  fs.mkdirSync(logsDir, { recursive: true });
}

const getLogFilePath = (filename) => path.join(logsDir, filename);

const accessLogStream = fs.createWriteStream(
  getLogFilePath('access.log'),
  { flags: 'a', encoding: 'utf-8' }
);

const errorLogStream = fs.createWriteStream(
  getLogFilePath('error.log'),
  { flags: 'a', encoding: 'utf-8' }
);

accessLogStream.on('error', (err) => {
  console.error('Access log stream error:', err);
});

errorLogStream.on('error', (err) => {
  console.error('Error log stream error:', err);
});

const morganFormat = ':date[iso] | :method :url | Status: :status | Response: :response-time ms | IP: :remote-addr';

const morganLogger = morgan(morganFormat, {
  stream: accessLogStream,
  skip: (req, res) => false
});

const consoleMorganLogger = morgan(morganFormat);

const errorLogger = (err, req, res, next) => {
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
  
  errorLogStream.write(logEntry, (err) => {
    if (err) console.error('Error writing to error log:', err);
  });
  
  if (process.env.NODE_ENV === 'development') {
    console.error('[ERROR LOG]', errorLog);
  }
  
  next(err);
};

module.exports = {
  morganLogger,
  consoleMorganLogger,
  errorLogger,
  logsDir
};
