# 📮 Guía de Colección Postman - ePayco Wallet

## 📋 Descripción General

Esta colección de Postman contiene todos los endpoints SOAP de la Billetera Virtual ePayco. Está diseñada para realizar pruebas manuales de todos los 5 servicios principales.

**Archivo:** `docs/Epayco-Wallet.postman_collection.json`

---

## 🚀 Inicio Rápido

### 1. Importar Colección en Postman

1. Abrir **Postman**
2. Click en **Import** (o `Ctrl+O`)
3. Seleccionar el archivo: `docs/Epayco-Wallet.postman_collection.json`
4. La colección se importará automáticamente con todos los endpoints

### 2. Configurar Variables de Entorno

Las siguientes variables se llenan automáticamente al ejecutar los requests:

| Variable | Descripción | Se llena automáticamente |
|----------|------------|------------------------|
| `soap_url` | URL del servicio SOAP | No - Se prefija con `http://localhost:8000/soap` |
| `mailhog_url` | URL de MailHog para ver emails | No - Se prefija con `http://localhost:8025` |
| `client_id` | ID del cliente registrado | ✅ Sí - Después de registrar cliente |
| `session_id` | ID de sesión de pago | ✅ Sí - Después de iniciar pago |
| `token` | Token de confirmación | 🔴 Manual - Copiar desde email |

### 3. Verificar Servicios Activos

Antes de ejecutar requests, asegúrate de que los servicios estén corriendo:

```bash
docker-compose ps
```

**Todos deben estar en estado `healthy` ✅**

---

## 📑 Estructura de la Colección

```
ePayco Wallet API
├── 1. REGISTRO DE CLIENTE
│   ├── Registrar Cliente - Happy Path
│   ├── Registrar Cliente - Email Duplicado
│   └── Registrar Cliente - Documento Duplicado
│
├── 2. RECARGA DE BILLETERA
│   ├── Recargar Billetera - Happy Path
│   ├── Recargar Billetera - Cliente No Encontrado
│   └── Recargar Billetera - Múltiples Recargas
│
├── 3. INICIAR PAGO
│   ├── Pagar - Happy Path
│   └── Pagar - Saldo Insuficiente
│
├── 4. CONFIRMAR PAGO
│   ├── Confirmar Pago - Happy Path
│   ├── Confirmar Pago - Token Incorrecto
│   └── Confirmar Pago - Sesión No Encontrada
│
├── 5. CONSULTAR SALDO
│   ├── Consultar Saldo - Happy Path
│   └── Consultar Saldo - Cliente No Encontrado
│
└── 6. SERVICIOS AUXILIARES
    ├── Ver Emails en MailHog (UI)
    └── API MailHog - Ver Emails (JSON)
```

---

## 🧪 Flujo de Prueba Completo

### Escenario: Crear cliente, recargar, pagar y confirmar

#### **Paso 1: Registrar Cliente**

1. Ir a: `1. REGISTRO DE CLIENTE` → `Registrar Cliente - Happy Path`
2. Cambiar valores en el body XML (documento, email, celular)
3. Click en **Send**
4. ✅ Verifica que:
   - Status sea 200
   - `success` = `true`
   - `cod_error` = `00`
   - Se haya guardado automáticamente `client_id`

**Output esperado:**
```json
{
  "success": true,
  "cod_error": "00",
  "message_error": "Cliente registrado exitosamente",
  "data": {
    "id": 1,
    "tipoDocumento": "CC",
    "numeroDocumento": "1234567890",
    "nombres": "Juan",
    "apellidos": "Pérez García",
    "email": "juan.perez@example.com",
    "celular": "3001234567",
    "billetera": {
      "id": 1,
      "saldo": "0.00"
    }
  }
}
```

---

#### **Paso 2: Recargar Billetera**

1. Ir a: `2. RECARGA DE BILLETERA` → `Recargar Billetera - Happy Path`
2. Click en **Send**
3. ✅ Verifica que:
   - `success` = `true`
   - `nuevoSaldo` = `50000.00`
   - Se haya creado una transacción

**Output esperado:**
```json
{
  "success": true,
  "cod_error": "00",
  "message_error": "Recarga realizada exitosamente",
  "data": {
    "transaccionId": 1,
    "nuevoSaldo": "50000.00",
    "monto": "50000.00",
    "referencia": "RECARGA-001",
    "fecha": "2025-10-21 14:30:00"
  }
}
```

---

#### **Paso 3: Iniciar Pago**

1. Ir a: `3. INICIAR PAGO` → `Pagar - Happy Path`
2. Click en **Send**
3. ✅ Verifica que:
   - Retorna un `sessionId` válido
   - Se haya guardado automáticamente `session_id`
   - Se haya enviado un email con el token

**Output esperado:**
```json
{
  "sessionId": "550e8400-e29b-41d4-a716-446655440000",
  "monto": 25000,
  "expiresAt": "2025-10-21 14:45:00"
}
```

---

#### **Paso 4: Obtener Token de Email**

1. Abrir: `http://localhost:8025` en el navegador
2. Buscar el email más reciente de `noreply@epayco.local`
3. Copiar el token de 6 dígitos (o UUID según configuración)
4. En Postman, establecer la variable `token` con este valor

**O usar API de MailHog:**
1. Ir a: `6. SERVICIOS AUXILIARES` → `API MailHog - Ver Emails`
2. Click en **Send**
3. Buscar el email más reciente y copiar el token

---

#### **Paso 5: Confirmar Pago**

1. Ir a: `4. CONFIRMAR PAGO` → `Confirmar Pago - Happy Path`
2. ⚠️ **IMPORTANTE:** Establecer variable `token` con el valor del email
3. Click en **Send**
4. ✅ Verifica que:
   - `success` = `true`
   - `cod_error` = `00`
   - `nuevoSaldo` = `25000.00` (50000 - 25000)

**Output esperado:**
```json
{
  "success": true,
  "cod_error": "00",
  "message_error": "Pago confirmado exitosamente",
  "data": {
    "transaccionId": 2,
    "monto": "25000.00",
    "nuevoSaldo": "25000.00",
    "fecha": "2025-10-21 14:35:00"
  }
}
```

---

#### **Paso 6: Consultar Saldo Final**

1. Ir a: `5. CONSULTAR SALDO` → `Consultar Saldo - Happy Path`
2. Click en **Send**
3. ✅ Verifica que el saldo sea `25000.00` (después de la recarga y el pago)

**Output esperado:**
```json
{
  "success": true,
  "cod_error": "00",
  "message_error": "Consulta realizada exitosamente",
  "data": {
    "saldo": "25000.00",
    "totalTransacciones": 2,
    "cliente": {
      "id": 1,
      "nombres": "Juan",
      "apellidos": "Pérez García",
      "email": "juan.perez@example.com"
    }
  }
}
```

---

## 🔍 Casos de Error

### Código de Error 02 - Cliente Duplicado

**Request:** `1. REGISTRO DE CLIENTE` → `Registrar Cliente - Email Duplicado`

```json
{
  "success": false,
  "cod_error": "02",
  "message_error": "El correo electrónico ya está registrado en el sistema"
}
```

---

### Código de Error 03 - Cliente No Encontrado

**Request:** `2. RECARGA DE BILLETERA` → `Recargar Billetera - Cliente No Encontrado`

```json
{
  "success": false,
  "cod_error": "03",
  "message_error": "Billetera no encontrada para el cliente"
}
```

---

### Código de Error 05 - Saldo Insuficiente

**Request:** `3. INICIAR PAGO` → `Pagar - Saldo Insuficiente`

**Nota:** Este request lanzará una excepción porque el monto supera el saldo. Es un caso de prueba.

---

### Código de Error 06 - Sesión No Encontrada

**Request:** `4. CONFIRMAR PAGO` → `Confirmar Pago - Sesión No Encontrada`

```json
{
  "success": false,
  "cod_error": "06",
  "message_error": "Sesión de pago no encontrada"
}
```

---

### Código de Error 07 - Token Incorrecto

**Request:** `4. CONFIRMAR PAGO` → `Confirmar Pago - Token Incorrecto`

```json
{
  "success": false,
  "cod_error": "07",
  "message_error": "Token incorrecto"
}
```

---

## 📧 Trabajar con Emails

### Ver Emails en UI de MailHog

1. Ir a: `6. SERVICIOS AUXILIARES` → `Ver Emails en MailHog`
2. Click en **Send** (o simplemente abrir en navegador: `http://localhost:8025`)
3. Se abre la interfaz web de MailHog

### Obtener Emails vía API

1. Ir a: `6. SERVICIOS AUXILIARES` → `API MailHog - Ver Emails`
2. Click en **Send**
3. Verás un JSON con todos los emails

**Respuesta típica:**
```json
{
  "messages": [
    {
      "ID": "1-...",
      "From": {
        "Relays": null,
        "Mailbox": "noreply",
        "Domain": "epayco.local"
      },
      "To": [
        {
          "Relays": null,
          "Mailbox": "juan",
          "Domain": "example.com"
        }
      ],
      "Content": {
        "Headers": {
          "Subject": ["Confirmación de Pago - ePayco Wallet"]
        },
        "Body": "<h2>Confirmación de Pago</h2><p>Token de confirmación: <strong>550e8400-e29b-41d4-a716-446655440000</strong></p>..."
      },
      "Created": "2025-10-21T14:30:00Z"
    }
  ]
}
```

---

## 🛠️ Modificar Requests

### Cambiar Datos de Prueba

Cada request contiene datos de ejemplo en el body XML. Puedes modificarlos:

**Ejemplo - Registrar Cliente:**
```xml
<tipoDocumento>CC</tipoDocumento>
<numeroDocumento>1234567890</numeroDocumento>  <!-- Cambiar este valor -->
<nombres>Juan</nombres>
<apellidos>Pérez García</apellidos>
<email>juan.perez@example.com</email>  <!-- Cambiar este valor -->
<celular>3001234567</celular>
```

### Usar Variables en Bodies

Las variables se usan entre `{{}}`:

```xml
<clienteId>{{client_id}}</clienteId>
<sessionId>{{session_id}}</sessionId>
<token>{{token}}</token>
```

---

## ⚙️ Tests Automatizados

Cada request incluye tests en la pestaña **Tests** que validan automáticamente la respuesta:

- ✅ Verifica que el status sea 200
- ✅ Verifica la estructura de respuesta
- ✅ Valida códigos de error
- ✅ Guarda variables de entorno automáticamente

Para ver los resultados de los tests, revisa la pestaña **Test Results** después de ejecutar un request.

---

## 🔐 Consideraciones de Seguridad

- ⚠️ Esta colección usa **HTTP** en local. En producción, usar **HTTPS**
- ⚠️ Los datos de prueba contienen información ficticia
- ⚠️ No compartir esta colección con credenciales reales
- ⚠️ Los tokens de pago expiran en **15 minutos**

---

## 🐛 Solución de Problemas

### "Connection refused"
```
❌ Error: connect ECONNREFUSED 127.0.0.1:8000
✅ Solución: Verificar que Docker Compose esté ejecutándose
docker-compose ps
```

### "response is not a valid SOAP message"
```
❌ Error: No es SOAP válido
✅ Solución: 
- Revisar que Content-Type sea 'text/xml'
- Revisar que SOAPAction esté presente en headers
- Verificar que el XML sea válido
```

### No aparecen emails
```
❌ Error: MailHog no captura emails
✅ Solución:
- Verificar que MailHog esté en puerto 8025
- Revisar logs: docker-compose logs mailhog
- Hacer un ping desde REST a MailHog
```

### Variables no se llenan automáticamente
```
❌ Error: {{client_id}} sigue vacío
✅ Solución:
- Revisar que el test pase correctamente
- Manually set: Postman > Environments > seleccionar entorno
- Click en ojo para ver variables
```

---

## 📚 Recursos Adicionales

- **WSDL:** `http://localhost:8000/soap/wsdl`
- **MailHog UI:** `http://localhost:8025`
- **MailHog API:** `http://localhost:8025/api/v2/messages`
- **README Principal:** `README.md`
- **Tests Unitarios:** `soap-service/tests/`

---

## 🔄 Flujos Alternativos

### Múltiples Clientes

Repite el flujo 1-6 varias veces, cambiando el email y documento en cada iteración:

```xml
<!-- Iteración 1 -->
<numeroDocumento>1111111111</numeroDocumento>
<email>cliente1@example.com</email>

<!-- Iteración 2 -->
<numeroDocumento>2222222222</numeroDocumento>
<email>cliente2@example.com</email>
```

### Múltiples Recargas

Usa `2. RECARGA DE BILLETERA` → `Recargar Billetera - Múltiples Recargas` para agregar más saldo:

```xml
<monto>100000</monto>
<referencia>RECARGA-ADICIONAL</referencia>
```

### Pagos Sucesivos

Repite pasos 3-5 para hacer múltiples pagos con el mismo cliente.

---

## 📝 Notas

- Los cambios en variables son **persistentes** durante la sesión de Postman
- Los datos se guardan en MySQL (volumen Docker `mysql_data`)
- Cada transacción es **atómica** (ACID compliant)
- Los tokens expiran en **15 minutos** después de generarse
- Se puede hacer **reset** con `docker-compose down -v` (borra todos los datos)

---

**Última actualización:** Octubre 2025  
**Versión:** 1.0.0  
**Compatibilidad:** Postman v9.0+
