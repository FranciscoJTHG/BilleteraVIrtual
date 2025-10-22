require('./setup');

describe('RecargaBilletera E2E', () => {
  const BASE_URL = global.BASE_URL;

  test('Caso exitoso: recarga billetera', async () => {
    const response = await fetch(`${BASE_URL}/wallet/recarga-billetera`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        clienteId: 1,
        documento: '1234567890',
        celular: '3001234567',
        monto: 100.00,
        referencia: 'REF_001'
      })
    });

    expect(response.status).toBe(200);
    const data = await response.json();
    expect(data.cod_error).toBeDefined();
  });

  test('Validación: rechaza monto negativo (400 - validación Joi)', async () => {
    const response = await fetch(`${BASE_URL}/wallet/recarga-billetera`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        clienteId: 1,
        documento: '1234567890',
        celular: '3001234567',
        monto: -100.00,
        referencia: 'REF_001'
      })
    });

    expect(response.status).toBe(400);
    const data = await response.json();
    expect(data.success).toBe(false);
  });

  test('Validación: rechaza cuando faltan campos requeridos (400)', async () => {
    const response = await fetch(`${BASE_URL}/wallet/recarga-billetera`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        monto: 100.00
      })
    });

    expect(response.status).toBe(400);
    const data = await response.json();
    expect(data.success).toBe(false);
  });

  test('Validación: rechaza celular con formato inválido (200 con error SOAP)', async () => {
    const response = await fetch(`${BASE_URL}/wallet/recarga-billetera`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        clienteId: 1,
        documento: '1234567890',
        celular: '30012345',
        monto: 100.00,
        referencia: 'REF_001'
      })
    });

    expect(response.status).toBe(200);
    const data = await response.json();
    expect(data.success).toBe(false);
  });
});
