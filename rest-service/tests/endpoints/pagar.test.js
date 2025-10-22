const request = require('supertest');
const express = require('express');
const validate = require('../../src/middlewares/validator');
const { pagarSchema } = require('../../src/validators/schemas');
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

app.post('/pagar', validate(pagarSchema), walletController.pagar);
app.use(errorHandler);

describe('Pagar Endpoint', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  test('Caso exitoso: inicia pago con token', async () => {
    const mockResponse = {
      success: true,
      cod_error: '000',
      message_error: 'Pago iniciado. Token enviado al email.',
      data: {
        sessionId: 'uuid-session-123',
        monto: 50.00,
        tiempoExpiracion: '15 minutos'
      }
    };

    soapClient.pagar.mockResolvedValue(mockResponse);

    const response = await request(app)
      .post('/pagar')
      .send({
        clienteId: 1,
        monto: 50.00,
        descripcion: 'Pago de servicios'
      });

    expect(response.status).toBe(200);
    expect(response.body.success).toBe(true);
    expect(response.body.data.sessionId).toBeDefined();
    expect(soapClient.pagar).toHaveBeenCalledWith(1, 50.00, 'Pago de servicios');
  });

  test('Validación: rechaza descripción vacía', async () => {
    const response = await request(app)
      .post('/pagar')
      .send({
        clienteId: 1,
        monto: 50.00,
        descripcion: ''
      });

    expect(response.status).toBe(400);
    expect(response.body.success).toBe(false);
    expect(response.body.cod_error).toBe('VAL_001');
  });

  test('Manejo de error SOAP: propaga error del servicio', async () => {
    const mockError = {
      success: false,
      cod_error: '503',
      message_error: 'Saldo insuficiente',
      data: null
    };

    soapClient.pagar.mockResolvedValue(mockError);

    const response = await request(app)
      .post('/pagar')
      .send({
        clienteId: 1,
        monto: 50.00,
        descripcion: 'Pago de servicios'
      });

    expect(response.status).toBe(200);
    expect(response.body.success).toBe(false);
    expect(response.body.cod_error).toBe('503');
  });
});
