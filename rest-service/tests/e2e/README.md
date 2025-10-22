# E2E Testing Guide - REST Service

## Overview

Este directorio contiene pruebas end-to-end (E2E) que validan la integración completa entre el servicio REST y el servicio SOAP.

## Requisitos

- Docker y Docker Compose instalados
- Node.js 18+
- Las imágenes Docker compiladas

## Estructura de Pruebas

```
tests/e2e/
├── setup.js                          # Health checks y configuración global
├── registroCliente.e2e.test.js       # Pruebas: RegistroCliente endpoint
├── recargaBilletera.e2e.test.js      # Pruebas: RecargaBilletera endpoint
├── pagar.e2e.test.js                 # Pruebas: Pagar endpoint
├── confirmarPago.e2e.test.js         # Pruebas: ConfirmarPago endpoint
└── consultarSaldo.e2e.test.js        # Pruebas: ConsultarSaldo endpoint
```

## Casos de Prueba por Endpoint

### RegistroCliente (4 tests)
- ✅ Registro exitoso con datos válidos
- ❌ Fallo con email inválido
- ❌ Fallo con campos requeridos faltantes
- ⚠️ Manejo de email duplicado

### RecargaBilletera (4 tests)
- ✅ Recarga exitosa con datos válidos
- ❌ Fallo con monto negativo
- ❌ Fallo con campos requeridos faltantes
- ⚠️ Manejo de monto cero

### Pagar (4 tests)
- ✅ Pago exitoso con datos válidos
- ❌ Fallo con descripción vacía
- ❌ Fallo con campos requeridos faltantes
- ❌ Fallo con monto inválido

### ConfirmarPago (4 tests)
- ✅ Confirmación exitosa con sessionId válido
- ❌ Fallo con sessionId vacío
- ❌ Fallo con campos requeridos faltantes
- ⚠️ Manejo de sesión expirada

### ConsultarSaldo (4 tests)
- ✅ Consulta exitosa con documento válido
- ❌ Fallo con documento vacío
- ❌ Fallo con campos requeridos faltantes
- ⚠️ Manejo de cliente inexistente

**Total: 20 pruebas E2E**

## Ejecutar las Pruebas

### Paso 1: Iniciar los servicios con Docker Compose

```bash
docker-compose up -d
```

Esperar a que todos los servicios estén healthy:
```bash
docker-compose ps
```

Estado esperado:
- epayco-db: healthy ✓
- epayco-soap: healthy ✓
- epayco-rest: healthy ✓

### Paso 2: Ejecutar las pruebas E2E

```bash
cd rest-service
npm run test:e2e
```

### Opciones adicionales

**Modo watch (reinicia pruebas cuando hay cambios):**
```bash
npm run test:e2e:watch
```

**Ejecutar todas las pruebas (unitarias + E2E):**
```bash
npm run test:all
```

**Pruebas unitarias solamente:**
```bash
npm run test
```

## Variables de Entorno

Por defecto, las pruebas E2E se conectan a:
- REST Service: `http://localhost:3000`
- SOAP Service: `http://localhost:8000/soap`

Para personalizar, establece las variables de entorno:

```bash
REST_SERVICE_URL=http://custom-host:3000 SOAP_URL=http://custom-host:8000/soap npm run test:e2e
```

## Estrategia de Pruebas

### Health Checks (setup.js)
- Valida que REST service esté disponible (`GET /health`)
- Valida que SOAP service esté disponible
- Reintenta cada segundo por hasta 30 segundos
- Timeout de beforeAll: 120 segundos

### Happy Path Tests
- Verifican que endpoints respondan correctamente con datos válidos
- Validan estructura de respuesta (cod_respuesta, mensaje_respuesta)
- Status esperado: 200

### Validation Tests
- Verifican que las validaciones de entrada funcionan
- Pruebas: campos vacíos, formatos inválidos, valores negativos
- Status esperado: 400

### Error Handling Tests
- Verifican manejo robusto de errores del SOAP
- Pruebas: cliente inexistente, sesión expirada
- Status esperado: variable (200, 400, 500 aceptable)

## Interpretar Resultados

```
PASS tests/e2e/registroCliente.e2e.test.js (5.234 s)
  E2E: RegistroCliente Endpoint
    ✓ should register a new client successfully (1234 ms)
    ✓ should fail with invalid email format (890 ms)
    ✓ should fail when missing required fields (756 ms)
    ✓ should handle duplicate email gracefully (1354 ms)

Test Suites: 5 passed, 5 total
Tests: 20 passed, 20 total
Time: 45.234 s
```

## Solución de Problemas

### "Services not healthy after 30 attempts"
- Verifica que Docker Compose esté corriendo: `docker-compose ps`
- Chequea logs del servicio: `docker-compose logs rest`
- Espera más tiempo e intenta nuevamente

### "ECONNREFUSED"
- Verifica que los puertos estén disponibles (3000, 8000)
- Chequea: `docker-compose ps` y `netstat -an | grep 3000`

### Tests timeout
- Incrementa el valor de timeout en `jest.e2e.config.js`
- Verifica que los servicios no estén sobrecargados

### Pruebas que pasan localmente pero fallan en CI/CD
- Verifica variables de entorno (REST_SERVICE_URL, SOAP_URL)
- Asegura que Docker Compose esté en la ruta correcta
- Verifica permisos de red

## Monitoreo en Tiempo Real

Mientras se ejecutan las pruebas, puedes monitorear:

```bash
# En otra terminal, ver logs de REST
docker-compose logs -f rest

# O logs de SOAP
docker-compose logs -f soap

# O logs de BD
docker-compose logs -f mysql
```

## Próximos Pasos

Enhancements futuros:
- [ ] Tests de timeout/conexión
- [ ] Tests de condiciones de carrera
- [ ] Tests de rendimiento/carga
- [ ] Tests de seguridad (inyección, XSS)
- [ ] Pruebas de autenticación
