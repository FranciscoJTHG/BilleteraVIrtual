const express = require('express');
const cors = require('cors');
const swaggerUi = require('swagger-ui-express');
const swaggerSpec = require('../swagger/swaggerConfig');
const routes = require('./routes');
const errorHandler = require('./middlewares/errorHandler');
const { morganLogger, consoleMorganLogger } = require('./config/logger');

const app = express();

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

const isDevelopment = process.env.NODE_ENV === 'development';
app.use(isDevelopment ? consoleMorganLogger : morganLogger);

app.use('/api-docs', swaggerUi.serve);
app.get('/api-docs', swaggerUi.setup(swaggerSpec));

app.use('/', routes);

app.use(errorHandler);

module.exports = app;
