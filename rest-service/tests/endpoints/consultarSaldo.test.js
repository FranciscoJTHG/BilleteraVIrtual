const request = require('supertest');
const express = require('express');
const validate = require('../../src/middlewares/validator');
const { consultarSaldoSchema } = require('../../src/validators/schemas');
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

app.get('/consultar-saldo', validate(consultarSaldoSchema), walletController.consultarSaldo);
app.use(errorHandler);

describe('ConsultarSaldo Endpoint', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  test('Caso exitoso: consulta saldo del cliente', async () => {
    const mockResponse = {
      success: true,
      cod_error: '000',
      message_error: 'Consulta realizada exitosamente',
      data: {
        saldo: '500.00',
        fechaUltimaActualizacion: '2025-10-22 12:00:00',
        totalTransacciones: 3,
        cliente: {
          id: 1,
          nombres: 'Juan',
          apellidos: 'Pérez',
          email: 'juan@example.com'
        }
      }
    };

    soapClient.consultarSaldo.mockResolvedValue(mockResponse);

    const response = await request(app)
      .get('/consultar-saldo')
      .query({
        clienteId: 1,
        documento: '1234567890',
        celular: '3001234567'
      });

    expect(response.status).toBe(200);
    expect(response.body.success).toBe(true);
    expect(response.body.data.saldo).toBe('500.00');
    expect(soapClient.consultarSaldo).toHaveBeenCalledWith(1, '1234567890', '3001234567');
  });

  test('Validación: rechaza documento vacío', async () => {
    const response = await request(app)
      .get('/consultar-saldo')
      .query({
        clienteId: 1,
        documento: '',
        celular: '3001234567'
      });

    expect(response.status).toBe(400);
    expect(response.body.success).toBe(false);
    expect(response.body.cod_error).toBe('VAL_001');
  });

  test('Manejo de error: cliente no encontrado', async () => {
    const mockError = {
      success: false,
      cod_error: '502',
      message_error: 'Cliente no encontrado',
      data: null
    };

    soapClient.consultarSaldo.mockResolvedValue(mockError);

    const response = await request(app)
      .get('/consultar-saldo')
      .query({
        clienteId: 999,
        documento: '9999999999',
        celular: '3001234567'
      });

    expect(response.status).toBe(200);
    expect(response.body.success).toBe(false);
    expect(response.body.message_error).toBe('Cliente no encontrado');
  });
});
