require('dotenv').config();
const app = require('./app');

const PORT = process.env.PORT || 3000;
const NODE_ENV = process.env.NODE_ENV || 'development';

app.listen(PORT, () => {
  console.log(`🚀 REST Service listening on port ${PORT} [${NODE_ENV}]`);
});
