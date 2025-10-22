require('./setup');

describe('ConfirmarPago E2E', () => {
  const BASE_URL = global.BASE_URL;

  test('Caso exitoso: confirma pago', async () => {
    const response = await fetch(`${BASE_URL}/wallet/confirmar-pago`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        sessionId: '550e8400-e29b-41d4-a716-446655440000',
        token: '123456'
      })
    });

    expect(response.status).toBe(200);
    const data = await response.json();
    expect(data.cod_error).toBeDefined();
  });

  test('Validación: rechaza sessionId en formato inválido (200 con error SOAP)', async () => {
    const response = await fetch(`${BASE_URL}/wallet/confirmar-pago`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        sessionId: 'invalid-uuid',
        token: '123456'
      })
    });

    expect(response.status).toBe(200);
    const data = await response.json();
    expect(data.success).toBe(false);
  });

  test('Validación: rechaza token con formato inválido (200 con error SOAP)', async () => {
    const response = await fetch(`${BASE_URL}/wallet/confirmar-pago`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        sessionId: '550e8400-e29b-41d4-a716-446655440000',
        token: '12345'
      })
    });

    expect(response.status).toBe(200);
    const data = await response.json();
    expect(data.success).toBe(false);
  });

  test('Validación: rechaza cuando falta sessionId (400)', async () => {
    const response = await fetch(`${BASE_URL}/wallet/confirmar-pago`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        token: '123456'
      })
    });

    expect(response.status).toBe(400);
    const data = await response.json();
    expect(data.success).toBe(false);
  });
});
