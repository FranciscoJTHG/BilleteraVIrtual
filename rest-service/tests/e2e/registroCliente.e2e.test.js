require('./setup');

describe('RegistroCliente E2E', () => {
  const BASE_URL = global.BASE_URL;

  test('Caso exitoso: registra cliente con datos válidos', async () => {
    const uniqueEmail = `user${Date.now()}@example.com`;
    const uniqueDoc = `${Math.floor(Math.random() * 9000000000) + 1000000000}`;
    const response = await fetch(`${BASE_URL}/wallet/registro-cliente`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        tipoDocumento: 'CC',
        numeroDocumento: uniqueDoc,
        nombres: 'Juan',
        apellidos: 'Pérez',
        email: uniqueEmail,
        celular: '3001234567'
      })
    });

    expect(response.status).toBe(200);
    const data = await response.json();
    expect(data.cod_error).toBeDefined();
  });

  test('Validación: rechaza email inválido (400)', async () => {
    const response = await fetch(`${BASE_URL}/wallet/registro-cliente`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        tipoDocumento: 'CC',
        numeroDocumento: '1234567890',
        nombres: 'Juan',
        apellidos: 'Pérez',
        email: 'email_invalido',
        celular: '3001234567'
      })
    });

    expect(response.status).toBe(400);
    const data = await response.json();
    expect(data.success).toBe(false);
  });

  test('Validación: rechaza cuando faltan campos requeridos (400)', async () => {
    const response = await fetch(`${BASE_URL}/wallet/registro-cliente`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        tipoDocumento: 'CC',
        numeroDocumento: '1234567890'
      })
    });

    expect(response.status).toBe(400);
    const data = await response.json();
    expect(data.success).toBe(false);
  });

  test('Validación: rechaza celular con menos de 10 dígitos (200 con error SOAP)', async () => {
    const uniqueEmail = `user${Date.now() + 1}@example.com`;
    const response = await fetch(`${BASE_URL}/wallet/registro-cliente`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        tipoDocumento: 'CC',
        numeroDocumento: '1234567891',
        nombres: 'Juan',
        apellidos: 'Pérez',
        email: uniqueEmail,
        celular: '30012345'
      })
    });

    expect(response.status).toBe(200);
    const data = await response.json();
    expect(data.success).toBe(false);
  });
});
