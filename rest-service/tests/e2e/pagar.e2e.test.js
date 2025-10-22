require('./setup');

describe('Pagar E2E', () => {
  const BASE_URL = global.BASE_URL;

  test('Caso exitoso: realiza pago', async () => {
    const response = await fetch(`${BASE_URL}/wallet/pagar`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        clienteId: 1,
        monto: 50.00,
        descripcion: 'Pago de servicios'
      })
    });

    expect(response.status).toBe(200);
    const data = await response.json();
    expect(data.cod_error).toBeDefined();
  });

  test('Validación: rechaza monto negativo (400 - validación Joi)', async () => {
    const response = await fetch(`${BASE_URL}/wallet/pagar`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        clienteId: 1,
        monto: -50.00,
        descripcion: 'Pago de servicios'
      })
    });

    expect(response.status).toBe(400);
    const data = await response.json();
    expect(data.success).toBe(false);
  });

  test('Validación: rechaza cuando faltan campos requeridos (400)', async () => {
    const response = await fetch(`${BASE_URL}/wallet/pagar`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        monto: 50.00
      })
    });

    expect(response.status).toBe(400);
    const data = await response.json();
    expect(data.success).toBe(false);
  });

  test('Validación: rechaza descripción con menos de 5 caracteres (200 con error SOAP)', async () => {
    const response = await fetch(`${BASE_URL}/wallet/pagar`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        clienteId: 1,
        monto: 50.00,
        descripcion: 'Pago'
      })
    });

    expect(response.status).toBe(200);
    const data = await response.json();
    expect(data.success).toBe(false);
  });
});
