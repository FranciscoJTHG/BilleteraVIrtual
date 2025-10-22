const http = require('http');

const BASE_URL = process.env.REST_SERVICE_URL || 'http://localhost:3000';
const SOAP_URL = process.env.SOAP_URL || 'http://localhost:8000/soap';

const isHealthy = async (url, maxRetries = 30) => {
  for (let i = 0; i < maxRetries; i++) {
    try {
      const response = await new Promise((resolve, reject) => {
        const req = http.get(url, (res) => {
          if (res.statusCode < 500) resolve(true);
          else reject(new Error(`Status: ${res.statusCode}`));
        });
        req.on('error', reject);
        req.setTimeout(2000, () => {
          req.abort();
          reject(new Error('Timeout'));
        });
      });
      return response;
    } catch (err) {
      console.log(`Health check attempt ${i + 1}/${maxRetries} failed, retrying...`);
      await new Promise(resolve => setTimeout(resolve, 1000));
    }
  }
  throw new Error(`Services not healthy after ${maxRetries} attempts`);
};

beforeAll(async () => {
  console.log('Waiting for services to be healthy...');
  console.log(`REST Service: ${BASE_URL}`);
  console.log(`SOAP Service: ${SOAP_URL}`);
  
  await isHealthy(`${BASE_URL}/health`);
  console.log('✓ REST service is healthy');
  
  await isHealthy(`${SOAP_URL}`);
  console.log('✓ SOAP service is healthy');
}, 120000);

global.BASE_URL = BASE_URL;
global.SOAP_URL = SOAP_URL;
