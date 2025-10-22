const request = require('supertest');
const express = require('express');
const validate = require('../../src/middlewares/validator');
const { registroClienteSchema } = require('../../src/validators/schemas');
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

app.post('/registro-cliente', validate(registroClienteSchema), walletController.registroCliente);
app.use(errorHandler);

describe('RegistroCliente Endpoint', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  test('Caso exitoso: registra cliente con datos válidos', async () => {
    const mockResponse = {
      success: true,
      cod_error: '000',
      message_error: 'Cliente registrado exitosamente',
      data: {
        id: 1,
        tipoDocumento: 'CC',
        numeroDocumento: '1234567890',
        nombres: 'Juan',
        apellidos: 'Pérez',
        email: 'juan@example.com',
        celular: '3001234567',
        billetera: { id: 1, saldo: '0.00' }
      }
    };

    soapClient.registroCliente.mockResolvedValue(mockResponse);

    const response = await request(app)
      .post('/registro-cliente')
      .send({
        tipoDocumento: 'CC',
        numeroDocumento: '1234567890',
        nombres: 'Juan',
        apellidos: 'Pérez',
        email: 'juan@example.com',
        celular: '3001234567'
      });

    expect(response.status).toBe(200);
    expect(response.body.success).toBe(true);
    expect(response.body.data.id).toBe(1);
    expect(soapClient.registroCliente).toHaveBeenCalledWith(
      'CC',
      '1234567890',
      'Juan',
      'Pérez',
      'juan@example.com',
      '3001234567'
    );
  });

  test('Validación: rechaza email inválido', async () => {
    const response = await request(app)
      .post('/registro-cliente')
      .send({
        tipoDocumento: 'CC',
        numeroDocumento: '1234567890',
        nombres: 'Juan',
        apellidos: 'Pérez',
        email: 'email_invalido',
        celular: '3001234567'
      });

    expect(response.status).toBe(400);
    expect(response.body.success).toBe(false);
    expect(response.body.cod_error).toBe('VAL_001');
    expect(response.body.details).toBeDefined();
  });

  test('Validación: rechaza cuando faltan campos requeridos', async () => {
    const response = await request(app)
      .post('/registro-cliente')
      .send({
        tipoDocumento: 'CC',
        numeroDocumento: '1234567890'
      });

    expect(response.status).toBe(400);
    expect(response.body.success).toBe(false);
    expect(response.body.cod_error).toBe('VAL_001');
    expect(response.body.details.length).toBeGreaterThan(0);
  });
});
