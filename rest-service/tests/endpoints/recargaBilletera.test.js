const request = require('supertest');
const express = require('express');
const validate = require('../../src/middlewares/validator');
const { recargaBilleteraSchema } = require('../../src/validators/schemas');
const errorHandler = require('../../src/middlewares/errorHandler');

jest.mock('../../src/services/soapClient', () => ({
  registroCliente: jest.fn(),
  recargaBilletera: jest.fn(),
  pagar: jest.fn(),
  confirmarPago: jest.fn(),
  consultarSaldo: jest.fn()
}));

const walletController = require('../../src/controllers/walletController');
const soapClient = require('../../src/services/soapClient');

const app = express();
app.use(express.json());

app.post('/recarga-billetera', validate(recargaBilleteraSchema), walletController.recargaBilletera);
app.use(errorHandler);

describe('RecargaBilletera Endpoint', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  test('Caso exitoso: recarga billetera con datos válidos', async () => {
    const mockResponse = {
      success: true,
      cod_error: '000',
      message_error: 'Recarga realizada exitosamente',
      data: {
        transaccionId: 1,
        nuevoSaldo: '100.00',
        monto: '100.00',
        referencia: 'REF001'
      }
    };

    soapClient.recargaBilletera.mockResolvedValue(mockResponse);

    const response = await request(app)
      .post('/recarga-billetera')
      .send({
        clienteId: 1,
        documento: '1234567890',
        celular: '3001234567',
        monto: 100.00,
        referencia: 'REF001'
      });

    expect(response.status).toBe(200);
    expect(response.body.success).toBe(true);
    expect(response.body.data.nuevoSaldo).toBe('100.00');
    expect(soapClient.recargaBilletera).toHaveBeenCalledWith(
      1,
      '1234567890',
      '3001234567',
      100.00,
      'REF001'
    );
  });

  test('Validación: rechaza monto negativo', async () => {
    const response = await request(app)
      .post('/recarga-billetera')
      .send({
        clienteId: 1,
        documento: '1234567890',
        celular: '3001234567',
        monto: -50.00,
        referencia: 'REF001'
      });

    expect(response.status).toBe(400);
    expect(response.body.success).toBe(false);
    expect(response.body.cod_error).toBe('VAL_001');
  });

  test('Validación: rechaza cuando documento está vacío', async () => {
    const response = await request(app)
      .post('/recarga-billetera')
      .send({
        clienteId: 1,
        documento: '',
        celular: '3001234567',
        monto: 100.00
      });

    expect(response.status).toBe(400);
    expect(response.body.success).toBe(false);
    expect(response.body.details).toBeDefined();
  });
});
