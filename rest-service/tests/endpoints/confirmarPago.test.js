const request = require('supertest');
const express = require('express');
const validate = require('../../src/middlewares/validator');
const { confirmarPagoSchema } = require('../../src/validators/schemas');
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

app.post('/confirmar-pago', validate(confirmarPagoSchema), walletController.confirmarPago);
app.use(errorHandler);

describe('ConfirmarPago Endpoint', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  test('Caso exitoso: confirma pago con token válido', async () => {
    const mockResponse = {
      success: true,
      cod_error: '000',
      message_error: 'Pago confirmado exitosamente',
      data: {
        transaccionId: 5,
        monto: '50.00',
        nuevoSaldo: '450.00',
        fecha: '2025-10-22 12:30:45'
      }
    };

    soapClient.confirmarPago.mockResolvedValue(mockResponse);

    const response = await request(app)
      .post('/confirmar-pago')
      .send({
        sessionId: 'uuid-session-123',
        token: '123456'
      });

    expect(response.status).toBe(200);
    expect(response.body.success).toBe(true);
    expect(response.body.data.transaccionId).toBe(5);
    expect(soapClient.confirmarPago).toHaveBeenCalledWith('uuid-session-123', '123456');
  });

  test('Validación: rechaza sessionId vacío', async () => {
    const response = await request(app)
      .post('/confirmar-pago')
      .send({
        sessionId: '',
        token: '123456'
      });

    expect(response.status).toBe(400);
    expect(response.body.success).toBe(false);
    expect(response.body.cod_error).toBe('VAL_001');
  });

  test('Manejo de error: sesión expirada', async () => {
    const mockError = {
      success: false,
      cod_error: '504',
      message_error: 'Sesión expirada',
      data: null
    };

    soapClient.confirmarPago.mockResolvedValue(mockError);

    const response = await request(app)
      .post('/confirmar-pago')
      .send({
        sessionId: 'uuid-session-123',
        token: '123456'
      });

    expect(response.status).toBe(200);
    expect(response.body.success).toBe(false);
    expect(response.body.message_error).toBe('Sesión expirada');
  });
});
