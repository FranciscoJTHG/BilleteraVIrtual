jest.mock('axios');
jest.mock('../services/soapClient');

module.exports = {
  MOCK_SOAP_SUCCESS: {
    success: true,
    cod_error: '000',
    message_error: 'Operación exitosa',
    data: {}
  },
  MOCK_SOAP_ERROR: {
    success: false,
    cod_error: '001',
    message_error: 'Error en la operación',
    data: null
  }
};
