# 📮 Guía de Colección Postman - ePayco Wallet

## 📋 Descripción General

Esta colección de Postman contiene todos los endpoints SOAP de la Billetera Virtual ePayco. Está diseñada para realizar pruebas manuales de todos los 5 servicios principales con casos de éxito y error documentados.

**Archivo:** `docs/Epayco-Wallet.postman_collection.json`

---

## 🚀 Inicio Rápido

### Prerequisitos

Asegúrate de que los servicios estén corriendo:

```bash
# Verificar estado
docker-compose ps

# Levantar servicios si no están activos
docker-compose up -d

# Ejecutar migraciones (primera vez)
docker exec -it epayco-soap php bin/console doctrine:migrations:migrate --no-interaction

# Verificar WSDL disponible
curl -v http://localhost:8000/soap/wsdl
```

### 1. Importar Colección en Postman

1. Abrir **Postman**
2. Click en **Import** (o `Ctrl+O`)
3. Seleccionar el archivo: `docs/Epayco-Wallet.postman_collection.json`
4. La colección se importará automáticamente con todos los endpoints

### 2. Variables Automáticas

Las siguientes variables se llenan automáticamente al ejecutar los requests:

| Variable | Descripción | Se llena automáticamente |
|----------|------------|------------------------|
| `soap_url` | URL del servicio SOAP | `http://localhost:8000/soap` |
| `mailhog_url` | URL de MailHog | `http://localhost:8025` |
| `client_id` | ID del cliente registrado | ✅ Sí - Después de registrar cliente |
| `documento` | Documento del cliente | ✅ Sí - Después de registrar cliente |
| `celular` | Celular del cliente | ✅ Sí - Después de registrar cliente |
| `session_id` | ID de sesión de pago | ✅ Sí - Después de iniciar pago |
| `token` | Token de confirmación | 🔴 Manual - Copiar desde email |
| `nuevo_saldo` | Saldo después de pago | ✅ Sí - Después de recarga/pago |

---

## 📑 Estructura de la Colección

```
ePayco Wallet API - SOAP
├── 1. REGISTRO DE CLIENTE
│   ├── Registrar Cliente - Happy Path ✅
│   ├── Registrar Cliente - Email Duplicado ❌
│   └── Registrar Cliente - Documento Duplicado ❌
│
├── 2. RECARGA DE BILLETERA
│   ├── Recargar Billetera - Happy Path ✅
│   ├── Recargar Billetera - Cliente No Encontrado ❌
│   ├── Recargar Billetera - Datos Incorrectos ❌
│   └── Recargar Billetera - Múltiples Recargas ✅
│
├── 3. INICIAR PAGO
│   ├── Pagar - Happy Path ✅
│   ├── Pagar - Saldo Insuficiente ❌
│   └── Pagar - Cliente No Encontrado ❌
│
├── 4. CONFIRMAR PAGO
│   ├── Confirmar Pago - Happy Path ✅
│   ├── Confirmar Pago - Token Incorrecto ❌
│   ├── Confirmar Pago - Sesión No Encontrada ❌
│   └── Confirmar Pago - Sesión Expirada ❌
│
├── 5. CONSULTAR SALDO
│   ├── Consultar Saldo - Happy Path ✅
│   ├── Consultar Saldo - Cliente No Encontrado ❌
│   └── Consultar Saldo - Datos Incorrectos ❌
│
└── 6. SERVICIOS AUXILIARES
    ├── Ver Emails en MailHog (UI)
    └── API MailHog - Ver Emails (JSON)
```

---

## 🧪 Flujo de Prueba Completo

### Escenario: Crear cliente, recargar, pagar y confirmar

**Tiempo estimado:** 5-10 minutos

#### **Paso 1: Registrar Cliente**

1. Ir a: `1. REGISTRO DE CLIENTE` → `Registrar Cliente - Happy Path`
2. **Cambiar valores en el body XML:**
   - `tipoDocumento`: CC, TI, etc.
   - `numeroDocumento`: 10-20 dígitos (ej: 1234567890)
   - `nombres`: Tu nombre
   - `apellidos`: Tu apellido
   - `email`: tu.email@example.com (debe ser único)
   - `celular`: 10 dígitos (ej: 3001234567)
3. Click en **Send**
4. ✅ Verifica que:
   - Status sea **200**
   - `success` = `true`
   - `cod_error` = `00`
   - Se haya guardado automáticamente `client_id`, `documento`, `celular`

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
2. Los valores `{{documento}}` y `{{celular}}` se usan automáticamente
3. Click en **Send**
4. ✅ Verifica que:
   - `success` = `true`
   - `nuevoSaldo` = `50000.00`
   - Se haya creado una transacción con `transaccionId`

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
    "fecha": "2025-10-22 14:30:00"
  }
}
```

---

#### **Paso 3: Iniciar Pago**

1. Ir a: `3. INICIAR PAGO` → `Pagar - Happy Path`
2. Los valores `{{client_id}}` se usan automáticamente
3. Click en **Send**
4. ✅ Verifica que:
   - `success` = `true`
   - Retorna un `sessionId` válido
   - Se haya guardado automáticamente `session_id`
   - **Un email ha sido enviado a MailHog**

**Output esperado (SOAP):**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tns="http://epayco.com/wallet">
    <soap:Body>
        <tns:pagarResponse>
            <tns:response>
                <tns:success>true</tns:success>
                <tns:cod_error>00</tns:cod_error>
                <tns:message_error>Pago iniciado. Token enviado al email.</tns:message_error>
                <tns:data>
                    <sessionId>550e8400-e29b-41d4-a716-446655440000</sessionId>
                    <monto>25000</monto>
                    <tiempoExpiracion>15 minutos</tiempoExpiracion>
                </tns:data>
            </tns:response>
        </tns:pagarResponse>
    </soap:Body>
</soap:Envelope>
```

---

#### **Paso 4: Obtener Token de Email**

Opción A - **UI de MailHog:**
1. Abrir navegador: `http://localhost:8025`
2. Ver el email más reciente de `noreply@epayco.local`
3. Copiar el **token de 6 dígitos** (o UUID según configuración)
4. En Postman, Ir a **Environments** → establecer variable `token` con este valor

Opción B - **API de MailHog:**
1. Ir a: `6. SERVICIOS AUXILIARES` → `API MailHog - Ver Emails`
2. Click en **Send**
3. En la respuesta JSON, buscar `Content.Body` y copiar el token

---

#### **Paso 5: Confirmar Pago**

1. Ir a: `4. CONFIRMAR PAGO` → `Confirmar Pago - Happy Path`
2. **IMPORTANTE:** Asegúrate de que la variable `token` esté establecida (Paso 4)
3. Los valores `{{session_id}}` y `{{token}}` se usan automáticamente
4. Click en **Send**
5. ✅ Verifica que:
   - `success` = `true`
   - `cod_error` = `00`
   - `nuevoSaldo` = `25000.00` (50000 - 25000)
   - Se haya creado una transacción con tipo `pago`

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
    "fecha": "2025-10-22 14:35:00"
  }
}
```

---

#### **Paso 6: Consultar Saldo Final**

1. Ir a: `5. CONSULTAR SALDO` → `Consultar Saldo - Happy Path`
2. Los valores `{{document}}` y `{{celular}}` se usan automáticamente
3. Click en **Send**
4. ✅ Verifica que el saldo sea `25000.00` (resultado de: 50000 recargados - 25000 pagados)

**Output esperado:**
```json
{
  "success": true,
  "cod_error": "00",
  "message_error": "Consulta realizada exitosamente",
  "data": {
    "saldo": "25000.00",
    "fechaUltimaActualizacion": "2025-10-22 14:35:00",
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

## 🧬 Alternativa: Testing con Insomnia (SOAP)

Para usuarios que prefieren Insomnia en lugar de Postman, aquí está la guía:

### Pasos para consultar saldo en Insomnia:

1. **Crear nueva request**
   - Método: POST
   - URL: `http://localhost:8000/soap`

2. **Headers**
   ```
   Content-Type: text/xml
   ```

3. **Body (XML)**
   ```xml
   <?xml version="1.0" encoding="UTF-8"?>
   <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://epayco.com/wallet">
       <soap:Body>
           <web:consultarSaldo>
               <web:clienteId>1</web:clienteId>
               <web:documento>1234567890</web:documento>
               <web:celular>3001234567</web:celular>
           </web:consultarSaldo>
       </soap:Body>
   </soap:Envelope>
   ```

4. **Reemplazar valores**
   - `clienteId`: ID del cliente registrado
   - `documento`: Documento del cliente (debe coincidir)
   - `celular`: Celular del cliente (debe coincidir)

5. **Enviar** (Ctrl+Enter)

**⚠️ Importante:** Los valores de `documento` y `celular` DEBEN coincidir exactamente con los registrados en la base de datos.

---

## 🔍 Casos de Error

### Código de Error 01 - Campos Requeridos Faltantes

```json
{
  "success": false,
  "cod_error": "01",
  "message_error": "Campos requeridos inválidos: El documento debe tener al menos 5 caracteres"
}
```

**Solución:** Verificar que todos los campos sean válidos y del tipo correcto.

---

### Código de Error 02 - Cliente Duplicado

**Request:** `1. REGISTRO DE CLIENTE` → `Registrar Cliente - Email Duplicado`

```json
{
  "success": false,
  "cod_error": "02",
  "message_error": "El correo electrónico ya está registrado en el sistema"
}
```

**Solución:** Usar un email o documento diferente.

---

### Código de Error 03 - Cliente No Encontrado

**Request:** `2. RECARGA DE BILLETERA` → `Recargar Billetera - Cliente No Encontrado`

```json
{
  "success": false,
  "cod_error": "03",
  "message_error": "Cliente no encontrado"
}
```

**Solución:** Registrar un cliente primero o usar un `client_id` válido.

---

### Código de Error 04 - Datos Incorrectos

**Request:** `5. CONSULTAR SALDO` → `Consultar Saldo - Datos Incorrectos`

```json
{
  "success": false,
  "cod_error": "04",
  "message_error": "Los datos de documento y celular no coinciden con el cliente"
}
```

**Solución:** Verificar que `documento` y `celular` coincidan exactamente con los registrados.

---

### Código de Error 05 - Saldo Insuficiente

**Request:** `3. INICIAR PAGO` → `Pagar - Saldo Insuficiente`

```json
{
  "success": false,
  "cod_error": "05",
  "message_error": "Saldo insuficiente"
}
```

**Solución:** Recargar billetera con más saldo.

---

### Código de Error 06 - Sesión de Pago No Encontrada

**Request:** `4. CONFIRMAR PAGO` → `Confirmar Pago - Sesión No Encontrada`

```json
{
  "success": false,
  "cod_error": "06",
  "message_error": "Sesión de pago no encontrada"
}
```

**Solución:** Iniciar un pago primero para generar una sesión válida.

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

**Solución:** Copiar el token correcto del email (MailHog).

---

### Código de Error 08 - Sesión Expirada

**Request:** `4. CONFIRMAR PAGO` → `Confirmar Pago - Sesión Expirada`

```json
{
  "success": false,
  "cod_error": "08",
  "message_error": "Sesión expirada"
}
```

**Solución:** Las sesiones expiran después de 15 minutos. Iniciar un nuevo pago.

---

### Código de Error 09 - Error de Base de Datos

```json
{
  "success": false,
  "cod_error": "09",
  "message_error": "Error de base de datos al consultar saldo: [error message]"
}
```

**Solución:** Verificar que MySQL esté corriendo:
```bash
docker-compose ps
docker-compose logs -f epayco-db
```

---

### Código de Error 10 - Error al Enviar Email

```json
{
  "success": false,
  "cod_error": "10",
  "message_error": "Error al enviar el email con el token: [error message]"
}
```

**Solución:** Verificar que MailHog esté corriendo:
```bash
docker-compose ps
docker-compose logs -f mailhog
```

---

## 📧 Trabajar con Emails en MailHog

### Ver Emails en UI de MailHog

1. Abrir navegador: `http://localhost:8025`
2. Se muestra la bandeja de entrada con todos los emails
3. Click en un email para ver detalles

### Obtener Emails vía API

```bash
# Comando curl
curl http://localhost:8025/api/v2/messages

# O usar el request en Postman
# 6. SERVICIOS AUXILIARES → API MailHog - Ver Emails
```

**Respuesta típica:**
```json
{
  "messages": [
    {
      "ID": "1.1729613400123.000000001",
      "From": {
        "Mailbox": "noreply",
        "Domain": "epayco.local"
      },
      "To": [
        {
          "Mailbox": "juan",
          "Domain": "example.com"
        }
      ],
      "Content": {
        "Headers": {
          "Subject": ["Token de Confirmación de Pago - ePayco Wallet"]
        },
        "Body": "<h2>Confirmación de Pago</h2><p>Token de confirmación: <strong style=\"font-size: 24px; color: #007bff;\">550e8400-e29b-41d4-a716-446655440000</strong></p>"
      },
      "Created": "2025-10-22T14:30:00Z"
    }
  ]
}
```

---

## 🛠️ Modificar y Personalizar Requests

### Cambiar Datos de Prueba

Cada request contiene datos de ejemplo en el body XML. Puedes modificarlos directamente:

**Ejemplo - Registrar Cliente:**
```xml
<tipoDocumento>CC</tipoDocumento>
<numeroDocumento>9876543210</numeroDocumento>  <!-- Cambiar este valor -->
<nombres>María</nombres>
<apellidos>García López</apellidos>
<email>maria.garcia@example.com</email>  <!-- Cambiar este valor -->
<celular>3109876543</celular>
```

### Usar Variables en Bodies

Las variables de Postman se usan entre `{{}}`:

```xml
<clienteId>{{client_id}}</clienteId>
<documento>{{documento}}</documento>
<celular>{{celular}}</celular>
<sessionId>{{session_id}}</sessionId>
<token>{{token}}</token>
```

### Crear Nuevos Requests

1. Click derecho en la carpeta → **Add Request**
2. Configurar método, URL y headers
3. En body, usar formato XML SOAP:
   ```xml
   <?xml version="1.0" encoding="UTF-8"?>
   <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://epayco.com/wallet">
       <soap:Body>
           <web:nombreDelMetodo>
               <web:parametro>valor</web:parametro>
           </web:nombreDelMetodo>
       </soap:Body>
   </soap:Envelope>
   ```

---

## ⚙️ Tests Automatizados

Cada request incluye **Tests** que validan automáticamente la respuesta:

- ✅ Verifica que el status sea 200
- ✅ Valida la estructura SOAP de respuesta
- ✅ Verifica que `success` sea true/false según se espere
- ✅ Valida códigos de error
- ✅ Guarda variables de entorno automáticamente

**Ver resultados:**
1. Ejecutar un request
2. Click en pestaña **Test Results**
3. Se muestran todos los tests pasados/fallidos

---

## 🐛 Solución de Problemas

### "Connection refused" en Postman/Insomnia

```bash
# Error: connect ECONNREFUSED 127.0.0.1:8000
# Solución:
docker-compose ps
docker-compose up -d

# Verificar WSDL
curl -v http://localhost:8000/soap/wsdl
```

### "Response is not a valid SOAP message"

```bash
# Verificar headers
# Content-Type debe ser: text/xml

# Verificar XML es válido
# Usar un validador: https://www.freeformatter.com/xml-validator-xsd.html

# Ver logs SOAP
docker-compose logs -f epayco-soap
```

### No aparecen emails en MailHog

```bash
# Verificar MailHog está corriendo
docker-compose ps | grep mailhog

# Ver logs MailHog
docker-compose logs -f mailhog

# Hacer ping desde REST a MailHog
docker exec -it epayco-rest curl http://mailhog:1025
```

### Variables no se llenan automáticamente

```bash
# Soluciones:
# 1. Verificar que el test pase correctamente
# 2. Abrir Environment: Postman > Environments
# 3. Click en ojo para ver variables activas
# 4. Establecer manualmente si es necesario

# Ver contenido de variable
# En Postman: {{variable}} mostrará el valor
```

### Status 500 en respuesta SOAP

```bash
# Verificar logs SOAP
docker-compose logs -f epayco-soap

# Verificar logs MySQL
docker-compose logs -f epayco-db

# Reiniciar SOAP
docker-compose restart epayco-soap

# Ver estado de migraciones
docker exec -it epayco-soap php bin/console doctrine:migrations:status
```

---

## 📚 Recursos Adicionales

- **WSDL:** `http://localhost:8000/soap/wsdl`
- **MailHog UI:** `http://localhost:8025`
- **MailHog API:** `http://localhost:8025/api/v2/messages`
- **README Principal:** `/README.md`
- **Documentación Insomnia:** `/README.md` (sección "🧬 Testing con Insomnia")
- **Tests Unitarios:** `/soap-service/tests/`

---

## 🔄 Flujos Alternativos

### Múltiples Clientes

Repite el flujo completo (Pasos 1-6) varias veces, cambiando el email y documento en cada iteración:

```xml
<!-- Cliente 1 -->
<numeroDocumento>1111111111</numeroDocumento>
<email>cliente1@example.com</email>

<!-- Cliente 2 -->
<numeroDocumento>2222222222</numeroDocumento>
<email>cliente2@example.com</email>
```

### Múltiples Recargas

Usa `2. RECARGA DE BILLETERA` → `Recargar Billetera - Múltiples Recargas`:

```xml
<monto>100000</monto>
<referencia>RECARGA-ADICIONAL</referencia>
```

Repite varias veces para agregar más saldo.

### Pagos Sucesivos

1. Ejecutar Paso 3: Iniciar Pago
2. Obtener token (Paso 4)
3. Confirmar Pago (Paso 5)
4. Repetir desde Paso 3 con diferentes montos

---

## 📊 Monitoreo y Debugging

### Ver todos los logs en tiempo real

```bash
docker-compose logs -f
```

### Ver logs específicos

```bash
# SOAP
docker-compose logs -f epayco-soap

# REST
docker-compose logs -f epayco-rest

# MySQL
docker-compose logs -f epayco-db

# MailHog
docker-compose logs -f mailhog
```

### Consultar base de datos

```bash
# Conectar a MySQL
docker exec -it epayco-db mysql -uepayco -pepayco123 epayco_wallet

# Ver clientes
SELECT id, numeroDocumento, nombres, email FROM clientes;

# Ver transacciones
SELECT * FROM transacciones ORDER BY fecha DESC;

# Ver pagos pendientes
SELECT * FROM pago_pendiente;
```

---

## 📝 Notas Importantes

- 📌 Los tokens de pago expiran en **15 minutos**
- 📌 Los tokens se envían por **email** (ver en MailHog en `http://localhost:8025`)
- 📌 La BD se persiste en un **volumen Docker** (`mysql_data`)
- 📌 Los servicios se comunican por una **red Docker interna**
- 📌 El `session_id` es un **UUID único** para cada pago
- 📌 Las variables de Postman son **persistentes** durante la sesión
- 📌 Se puede hacer **reset** con `docker-compose down -v` (borra todos los datos)

---

**Última actualización:** Octubre 2025  
**Versión:** 2.0.0  
**Compatibilidad:** Postman v9.0+ / Insomnia v2022+
