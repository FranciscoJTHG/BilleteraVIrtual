require('./setup');

describe('ConsultarSaldo E2E', () => {
  const BASE_URL = global.BASE_URL;

  test('Caso exitoso: consulta saldo', async () => {
    const response = await fetch(`${BASE_URL}/wallet/consultar-saldo?clienteId=1&documento=1234567890&celular=3001234567`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    });

    expect(response.status).toBe(200);
    const data = await response.json();
    expect(data.cod_error).toBeDefined();
  });

  test('Validación: rechaza cuando falta clienteId (400 - validación Joi)', async () => {
    const response = await fetch(`${BASE_URL}/wallet/consultar-saldo?documento=1234567890&celular=3001234567`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    });

    expect(response.status).toBe(400);
    const data = await response.json();
    expect(data.success).toBe(false);
  });

  test('Validación: rechaza celular con menos de 10 dígitos (200 con error SOAP)', async () => {
    const response = await fetch(`${BASE_URL}/wallet/consultar-saldo?clienteId=1&documento=1234567890&celular=30012345`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    });

    expect(response.status).toBe(200);
    const data = await response.json();
    expect(data.success).toBe(false);
  });

  test('Validación: rechaza cuando falta documento (400)', async () => {
    const response = await fetch(`${BASE_URL}/wallet/consultar-saldo?clienteId=1&celular=3001234567`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    });

    expect(response.status).toBe(400);
    const data = await response.json();
    expect(data.success).toBe(false);
  });
});
