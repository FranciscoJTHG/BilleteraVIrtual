const errorHandler = (err, req, res, next) => {
  console.error('[ERROR]', {
    message: err.message,
    stack: err.stack,
    path: req.path,
    method: req.method,
    timestamp: new Date().toISOString()
  });

  const statusCode = err.statusCode || 500;
  const isDevelopment = process.env.NODE_ENV === 'development';

  res.status(statusCode).json({
    success: false,
    cod_error: 'ERR_' + statusCode,
    message_error: err.message || 'Error interno del servidor',
    details: isDevelopment ? err.stack : undefined
  });
};

module.exports = errorHandler;
