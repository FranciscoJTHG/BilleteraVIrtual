# 💰 BilleteraVirtual - ePayco

Sistema de billetera virtual con arquitectura de microservicios. Prueba técnica para el cargo de Desarrollador BackEnd en ePayco.

## 🎯 Descripción del Proyecto

Sistema de billetera virtual que permite a los usuarios:
- 📋 Registrarse y crear una billetera digital
- 💳 Recargar saldo en su billetera
- 💸 Realizar pagos con confirmación por token (enviado por email)
- 📊 Consultar saldo disponible
- 📝 Historial de transacciones

### 🚦 Guía Rápida de Inicio (5 minutos)

```bash
# 1. Clonar y posicionarse
git clone <URL-del-repositorio>
cd BilleteraVirtual

# 2. Levantar servicios
docker-compose up -d

# 3. Esperar health checks (30-60 segundos)
sleep 30
docker-compose ps

# 4. Ejecutar migraciones
docker exec -it epayco-soap php bin/console doctrine:migrations:migrate --no-interaction

# 5. Verificar logs
docker-compose logs -f epayco-soap

# 6. Listo para usar ✅
# - REST API: http://localhost:3000
# - SOAP WSDL: http://localhost:8000/soap/wsdl
# - MailHog: http://localhost:8025
# - MySQL: localhost:3306 (usuario: epayco / contraseña: epayco123)
```

---

## 🏗️ Arquitectura del Sistema

```
┌─────────────────────────────────────────────────┐
│              Docker Compose                      │
├─────────────────────────────────────────────────┤
│                                                  │
│  ┌──────────────┐      ┌──────────────┐        │
│  │ REST Service │─────▶│ SOAP Service │        │
│  │  (Express.js)│      │  (Symfony +  │        │
│  │              │      │   Doctrine)  │        │
│  │  Puerto 3000 │      │  Puerto 8000 │        │
│  └──────────────┘      └──────┬───────┘        │
│                               │                 │
│                               ▼                 │
│                        ┌──────────────┐        │
│                        │    MySQL     │        │
│                        │  8.0         │        │
│                        │  Puerto 3306 │        │
│                        └──────────────┘        │
│                                                  │
│                        ┌──────────────┐        │
│                        │   MailHog    │        │
│                        │ (Test Email) │        │
│                        │  Puerto 8025 │        │
│                        └──────────────┘        │
└─────────────────────────────────────────────────┘
```

### Componentes

| Componente | Tecnología | Función |
|-----------|-----------|---------|
| **Servicio REST** | Express.js (Node.js 18) | Puente entre cliente y servicio SOAP |
| **Servicio SOAP** | Symfony 6 + Doctrine ORM | Única conexión a base de datos |
| **Base de Datos** | MySQL 8.0 | Almacenamiento de datos |
| **Email Testing** | MailHog | Captura de emails en desarrollo |
| **Orquestación** | Docker Compose | Gestión de contenedores |

---

## 📋 Requisitos Previos

- 🐳 Docker 20.10+
- 🐳 Docker Compose 2.0+
- 📦 Git
- 📝 Postman (opcional, para pruebas)

---

## 🚀 Instalación y Ejecución

### 1. Clonar el Repositorio

```bash
git clone <URL-del-repositorio>
cd BilleteraVirtual
```

### 2. Levantar los Servicios

```bash
docker-compose up -d
```

**Esperar 30-60 segundos** para que todos los servicios estén listos (health checks).

### 3. Ejecutar Migraciones (Primera Vez)

```bash
docker exec -it epayco-soap php bin/console doctrine:migrations:migrate --no-interaction
```

### 4. Verificar Servicios

```bash
docker-compose ps
```

Todos los servicios deben estar en estado **healthy** ✅

---

## 🔍 Revisión de Logs y Monitoreo

### Ver logs de todos los servicios en tiempo real

```bash
docker-compose logs -f
```

### Ver logs de servicios específicos

```bash
# Logs del servicio SOAP
docker-compose logs -f epayco-soap

# Logs del servicio REST
docker-compose logs -f epayco-rest

# Logs de MySQL
docker-compose logs -f epayco-db

# Logs de MailHog
docker-compose logs -f mailhog
```

### Ver últimas N líneas de logs

```bash
# Últimas 50 líneas
docker-compose logs --tail=50 epayco-soap

# Últimas 100 líneas
docker-compose logs --tail=100 epayco-rest
```

### Monitoreo en tiempo real

```bash
# Ver uso de recursos de contenedores
docker stats

# Ver estado de un contenedor específico
docker inspect --format='{{json .State.Health}}' epayco-soap | jq

# Verificar conectividad SOAP desde REST
docker exec -it epayco-rest curl http://epayco-soap:8000/soap/wsdl

# Verificar conectividad MySQL
docker exec -it epayco-soap php bin/console doctrine:query:dql "SELECT COUNT(c) FROM App\\Entity\\Cliente c"
```

---

## 🌐 Acceso a los Servicios

| Servicio | URL | Descripción |
|---------|-----|-------------|
| **REST API** | http://localhost:3000 | Punto de entrada para clientes |
| **SOAP WSDL** | http://localhost:8000/soap/wsdl | Definición del servicio SOAP |
| **MailHog UI** | http://localhost:8025 | Ver emails capturados |
| **MySQL** | localhost:3306 | usuario: `epayco` / contraseña: `epayco123` |

---

## 📚 API REST - Endpoints

### Base URL: `http://localhost:3000/api`

### 1️⃣ Registro de Cliente

**POST** `/registro-cliente`

Registrar un nuevo cliente y crear su billetera.

```json
{
  "documento": "123456789",
  "nombres": "Juan Pérez García",
  "email": "juan@example.com",
  "celular": "3001234567"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "cod_error": "00",
  "message_error": "Cliente registrado exitosamente",
  "data": {
    "cliente_id": 1,
    "documento": "123456789",
    "nombres": "Juan Pérez García",
    "email": "juan@example.com"
  }
}
```

---

### 2️⃣ Recarga de Billetera

**POST** `/recarga-billetera`

Agregar saldo a la billetera de un cliente.

```json
{
  "documento": "123456789",
  "celular": "3001234567",
  "valor": 50000
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "cod_error": "00",
  "message_error": "Billetera recargada exitosamente",
  "data": {
    "nuevo_saldo": 50000,
    "monto_recargado": 50000
  }
}
```

---

### 3️⃣ Iniciar Pago

**POST** `/pagar`

Iniciar un proceso de pago. Genera un token que se envía por email.

```json
{
  "documento": "123456789",
  "celular": "3001234567",
  "monto": 25000
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "cod_error": "00",
  "message_error": "Token enviado al correo electrónico",
  "data": {
    "session_id": "550e8400-e29b-41d4-a716-446655440000",
    "monto": 25000,
    "mensaje": "Token de 6 dígitos enviado a juan@example.com"
  }
}
```

**⚠️ Importante:** El token es enviado por email. Revisar en http://localhost:8025

---

### 4️⃣ Confirmar Pago

**POST** `/confirmar-pago`

Confirmar el pago con el token recibido por email.

```json
{
  "session_id": "550e8400-e29b-41d4-a716-446655440000",
  "token": "123456"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "cod_error": "00",
  "message_error": "Pago realizado exitosamente",
  "data": {
    "monto_debitado": 25000,
    "nuevo_saldo": 25000,
    "transaccion_id": 1
  }
}
```

---

### 5️⃣ Consultar Saldo

**GET** `/consultar-saldo?documento=123456789&celular=3001234567`

Consultar el saldo disponible en la billetera.

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "cod_error": "00",
  "message_error": "Saldo consultado exitosamente",
  "data": {
    "saldo": 25000,
    "documento": "123456789",
    "nombres": "Juan Pérez García"
  }
}
```

---

## 🔧 Estructura de Respuesta Estándar

Todas las respuestas siguen el siguiente formato:

```json
{
  "success": true | false,
  "cod_error": "00",
  "message_error": "Descripción del resultado",
  "data": {}
}
```

---

## ⚠️ Códigos de Error

| Código | Descripción |
|--------|-------------|
| **00** | Éxito ✅ |
| **01** | Campos requeridos faltantes |
| **02** | Cliente ya existe |
| **03** | Cliente no encontrado |
| **04** | Datos incorrectos (documento/celular no coinciden) |
| **05** | Saldo insuficiente |
| **06** | Sesión de pago no encontrada |
| **07** | Token incorrecto |
| **08** | Sesión expirada |
| **09** | Error de base de datos |
| **10** | Error al enviar email |

---

## 🗄️ Estructura de la Base de Datos

### Entidades Doctrine

#### 1. Cliente
```
┌─────────────────┐
│     Cliente     │
├─────────────────┤
│ id (PK)         │
│ documento (UK)  │
│ nombres         │
│ email (UK)      │
│ celular         │
│ createdAt       │
└─────────────────┘
```

#### 2. Billetera
```
┌──────────────────┐
│    Billetera     │
├──────────────────┤
│ id (PK)          │
│ cliente_id (FK)  │
│ saldo            │
│ updatedAt        │
└──────────────────┘
```

#### 3. Transaccion
```
┌─────────────────────┐
│    Transaccion      │
├─────────────────────┤
│ id (PK)             │
│ billetera_id (FK)   │
│ tipo                │
│ monto               │
│ descripcion         │
│ createdAt           │
└─────────────────────┘
```

#### 4. PagoPendiente
```
┌──────────────────────┐
│   PagoPendiente      │
├──────────────────────┤
│ id (PK)              │
│ sessionId (UK)       │
│ billetera_id (FK)    │
│ monto                │
│ token                │
│ usado                │
│ expiraEn             │
│ createdAt            │
└──────────────────────┘
```

---

## 🧬 Testing con Insomnia (SOAP)

### Configurar request SOAP en Insomnia

#### Pasos para consultar saldo:

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
   - `documento`: Documento del cliente
   - `celular`: Celular del cliente (debe coincidir con documento)

5. **Enviar** (Ctrl+Enter)

#### Respuesta exitosa:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tns="http://epayco.com/wallet">
    <soap:Body>
        <tns:consultarSaldoResponse>
            <tns:response>
                <tns:success>true</tns:success>
                <tns:cod_error>00</tns:cod_error>
                <tns:message_error>Consulta realizada exitosamente</tns:message_error>
                <tns:data>
                    <saldo>50000.00</saldo>
                    <fechaUltimaActualizacion>2025-10-22 16:30:00</fechaUltimaActualizacion>
                    <totalTransacciones>2</totalTransacciones>
                    <cliente>
                        <id>1</id>
                        <nombres>Juan Pérez</nombres>
                        <apellidos>García</apellidos>
                        <email>juan@example.com</email>
                    </cliente>
                </tns:data>
            </tns:response>
        </tns:consultarSaldoResponse>
    </soap:Body>
</soap:Envelope>
```

#### Respuesta con error (documento/celular no coinciden):
```xml
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tns="http://epayco.com/wallet">
    <soap:Body>
        <tns:consultarSaldoResponse>
            <tns:response>
                <tns:success>false</tns:success>
                <tns:cod_error>04</tns:cod_error>
                <tns:message_error>Los datos de documento y celular no coinciden con el cliente</tns:message_error>
                <tns:data/>
            </tns:response>
        </tns:consultarSaldoResponse>
    </soap:Body>
</soap:Envelope>
```

**⚠️ Importante:** Los valores de `documento` y `celular` deben coincidir exactamente con los registrados en la base de datos.

---

## 🧪 Testing con Postman

### ✅ Colección Postman Disponible

La colección completa con todos los 5 servicios está disponible en:  
**`docs/Epayco-Wallet.postman_collection.json`**

📖 **Guía Completa:** Ver `docs/POSTMAN_COLLECTION.md` para instrucciones detalladas.

### Importar Colección

1. Abrir **Postman**
2. Click en **Import** (o `Ctrl+O`)
3. Seleccionar: `docs/Epayco-Wallet.postman_collection.json`
4. Listo para hacer pruebas ✅

### Características de la Colección

- ✅ **5 Servicios SOAP** con múltiples casos de prueba
- ✅ **Tests Automatizados** en cada request
- ✅ **Variables de Entorno** preconfiguras
- ✅ **Casos de Error** documentados
- ✅ **Ejemplos de Respuesta** para cada endpoint
- ✅ **Integración MailHog** para ver tokens de email

### Flujo de Prueba Recomendado

1. **Registro Cliente** → Crear nueva cuenta
2. **Recarga Billetera** → Agregar $50,000
3. **Pagar** → Iniciar transacción de $25,000
4. **Ver Email en MailHog** → http://localhost:8025 (copiar token)
5. **Confirmar Pago** → Usar token del email
6. **Consultar Saldo** → Verificar $25,000

**Tiempo estimado:** 5-10 minutos para flujo completo

---

## 📮 Ver Emails Capturados

Los tokens de confirmación se envían por email. Para verlos:

1. Acceder a http://localhost:8025
2. Ver bandeja de entrada
3. Buscar token de 6 dígitos

---

## 🐳 Comandos Docker Útiles

### Gestión de Servicios

```bash
# Iniciar servicios
docker-compose up -d

# Ver estado de todos los servicios
docker-compose ps

# Iniciar servicios con salida en consola
docker-compose up

# Detener servicios
docker-compose down

# Detener y eliminar volúmenes (⚠️ borra BD)
docker-compose down -v

# Reconstruir imágenes
docker-compose up -d --build

# Ver eventos en tiempo real
docker-compose events
```

### Ejecutar Comandos en Contenedores

```bash
# Migraciones de Doctrine
docker exec -it epayco-soap php bin/console doctrine:migrations:migrate

# Estado de migraciones
docker exec -it epayco-soap php bin/console doctrine:migrations:status

# Generar nueva migración (si cambias entities)
docker exec -it epayco-soap php bin/console doctrine:migrations:diff

# Acceder a MySQL CLI
docker exec -it epayco-db mysql -uepayco -pepayco123 epayco_wallet

# Ver clientes registrados
docker exec -it epayco-db mysql -uepayco -pepayco123 epayco_wallet -e "SELECT id, numeroDocumento, nombres, email, celular FROM clientes;"

# Ver billetes y saldos
docker exec -it epayco-db mysql -uepayco -pepayco123 epayco_wallet -e "SELECT b.id, b.cliente_id, b.saldo FROM billetes b;"

# Ver transacciones
docker exec -it epayco-db mysql -uepayco -pepayco123 epayco_wallet -e "SELECT * FROM transacciones ORDER BY fecha DESC LIMIT 10;"

# Reiniciar un servicio específico
docker-compose restart epayco-soap
docker-compose restart epayco-rest
docker-compose restart epayco-db
```

### Monitoreo y Debugging

```bash
# Ver uso de recursos en tiempo real
docker stats

# Verificar health status de un servicio
docker inspect --format='{{json .State.Health}}' epayco-soap | jq

# Ver logs del último reinicio
docker-compose logs --since 1m epayco-soap

# Ejecutar prueba de conectividad
docker exec -it epayco-rest curl -v http://epayco-soap:8000/soap/wsdl

# Obtener IP del contenedor
docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' epayco-soap

# Ver variables de entorno del contenedor
docker exec epayco-soap env | grep DATABASE

# Verificar espacio en disco usado por Docker
docker system df
```

### Limpiar Recursos

```bash
# Eliminar contenedores detenidos
docker container prune

# Eliminar imágenes sin usar
docker image prune

# Eliminar volúmenes sin usar
docker volume prune

# Limpiar todo (⚠️ elimina contenedores, imágenes, redes, volúmenes)
docker system prune -a --volumes
```

---

## 📁 Estructura del Proyecto

```
BilleteraVirtual/
├── soap-service/                 # Servicio SOAP (Symfony + Doctrine)
│   ├── src/
│   │   ├── Entity/              # Entidades Doctrine
│   │   │   ├── Cliente.php
│   │   │   ├── Billetera.php
│   │   │   ├── Transaccion.php
│   │   │   └── PagoPendiente.php
│   │   ├── Repository/          # Repositorios personalizados
│   │   ├── Service/
│   │   │   └── WalletService.php # Lógica de negocio
│   │   └── Controller/
│   │       └── SoapController.php
│   ├── migrations/              # Migraciones Doctrine
│   ├── public/
│   │   └── wallet.wsdl         # Definición WSDL
│   ├── .env                     # Variables de entorno
│   ├── .dockerignore
│   ├── composer.json
│   ├── composer.lock
│   └── Dockerfile              # Multi-stage build
│
├── rest-service/                # Servicio REST (Express.js)
│   ├── src/
│   │   ├── controllers/
│   │   │   └── walletController.js
│   │   ├── services/
│   │   │   └── soapClient.js    # Cliente SOAP
│   │   ├── middlewares/
│   │   │   ├── validator.js
│   │   │   └── errorHandler.js
│   │   ├── routes/
│   │   │   └── wallet.js
│   │   ├── validators/
│   │   │   └── schemas.js       # Esquemas Joi
│   │   ├── app.js
│   │   └── server.js
│   ├── .env                     # Variables de entorno
│   ├── .dockerignore
│   ├── package.json
│   ├── package-lock.json
│   └── Dockerfile              # Multi-stage build
│
├── docker/
│   └── mysql/
│       └── my.cnf              # Configuración MySQL
│
├── docs/
│   ├── detalles.md
│   ├── PLAN_IMPLEMENTACION.md
│   └── Epayco-Wallet.postman_collection.json
│
├── .env                         # Variables docker-compose
├── .env.example
├── .gitignore
├── docker-compose.yml          # Orquestación Docker
├── README.md                    # Este archivo
└── PLAN_FASE1.md
```

---

## 🧪 Testing Automatizado

### Unit Tests & Integration Tests

```bash
# Ejecutar todos los tests
docker exec -it epayco-soap php bin/phpunit

# Tests incluidos:
# - ClienteConstraintsTest (12 tests)
#   ✅ Validación de documento (requerido, mínimo 6 caracteres)
#   ✅ Validación de nombres (requerido, 5-100 caracteres)
#   ✅ Validación de email (formato válido, requerido)
#   ✅ Validación de celular (formato válido)
# - RegistroClienteTest (integration)
#   ✅ Registro exitoso de cliente
#   ✅ Creación automática de billetera
# - RecargaBilleteraTest (6 integration tests) - NUEVA
#   ✅ Happy Path - Recarga Exitosa
#   ✅ Actualización de Saldo
#   ✅ Creación de Transacción
#   ✅ Persistencia en Base de Datos
#   ✅ Cliente No Encontrado
#   ✅ Múltiples Recargas
# - PagarTest (3 integration tests)
#   ✅ Happy Path - Pago Exitoso
#   ✅ Saldo Insuficiente
#   ✅ Creación de PagoPendiente en BD
# - ConfirmarPagoTest (6 integration tests) - NUEVA
#   ✅ Happy Path - Confirmación Exitosa
#   ✅ Actualización de Saldo Después de Confirmación
#   ✅ Creación de Transacción de Pago
#   ✅ Sesión de Pago No Encontrada
#   ✅ Token Incorrecto
#   ✅ Sesión Expirada
# - ConsultarSaldoTest (5 integration tests) - NUEVA
#   ✅ Happy Path - Consulta Exitosa
#   ✅ Saldo Cero Inicial
#   ✅ Historial de Transacciones
#   ✅ Cliente No Encontrado
#   ✅ Información del Cliente Correcta
```

**Estado Actual:** ✅ 44/44 tests pasando (FASE 2 completada)

---

## 🔐 Seguridad

### Implementado

- ✅ Usuario **no-root** en contenedores
- ✅ Secretos en variables de entorno (`.env`)
- ✅ Validación de entrada con **Joi** (REST) y **Symfony Validator** (SOAP)
  - ✅ **Symfony ValidatorInterface** con Constraints en Entities
  - ✅ Autowiring automático de ValidatorInterface en servicios
- ✅ Transacciones de base de datos para operaciones financieras
- ✅ Health checks para confiabilidad
- ✅ Filesystem read-only donde es posible
- ✅ Comunicación interna entre servicios
- ✅ Permisos configurables en contenedores Docker

### Consideraciones

⚠️ **En Producción:**
- Usar HTTPS/TLS
- Implementar autenticación (JWT, OAuth2)
- Usar vault para secretos
- Configurar CORS restringido
- Implementar rate limiting
- Usar base de datos con backup automático

---

## 📊 Stack Tecnológico

### Backend SOAP
- **PHP 8.2** - Lenguaje
- **Symfony 6** - Framework web full-featured
- **Doctrine ORM** - Mapeo objeto-relacional con Constraints
- **Symfony Validator** - Validación de datos con Constraints
- **SOAP** - Protocolo de comunicación
- **Doctrine Migrations** - Versionado de schema

### Backend REST
- **Node.js 18** - Runtime JavaScript
- **Express.js 4** - Framework web
- **soap** - Cliente SOAP para Node
- **Joi** - Validación de esquemas
- **Nodemailer** - Integración con MailHog

### Base de Datos
- **MySQL 8.0** - Base de datos relacional
- **InnoDB** - Motor de almacenamiento con soporte a transacciones ACID

### Testing
- **PHPUnit** - Framework de testing para PHP
- **Fixtures** - Datos de prueba

### DevOps
- **Docker** - Contenedorización de servicios
- **Docker Compose** - Orquestación local
- **MailHog** - SMTP fake para testing de emails
- **Health Checks** - Verificación de estado de servicios

---

## 🎯 Flujo de Transacción Completo

```
1. Usuario se registra
   ├─ REST API recibe POST /registro-cliente
   ├─ Valida datos con Joi
   ├─ Llama SOAP service (registroCliente)
   ├─ SOAP crea Cliente y Billetera con Doctrine
   └─ Retorna confirmación

2. Usuario recarga billetera
   ├─ REST API recibe POST /recarga-billetera
   ├─ Valida documento + celular
   ├─ SOAP actualiza saldo
   ├─ Crea registro de Transaccion (tipo: recarga)
   └─ Confirma con nuevo saldo

3. Usuario quiere pagar ($25,000)
   ├─ REST API recibe POST /pagar
   ├─ SOAP verifica saldo suficiente
   ├─ Genera token de 6 dígitos
   ├─ Crea PagoPendiente con UUID (session_id)
   ├─ Envía email con token por MailHog
   └─ Retorna session_id al cliente

4. Usuario confirma pago con token
   ├─ REST API recibe POST /confirmar-pago
   ├─ SOAP valida session_id y token
   ├─ Verifica expiración (30 minutos)
   ├─ Inicia Transacción de BD
   ├─ Decrementa saldo de Billetera
   ├─ Crea Transaccion (tipo: pago)
   ├─ Marca PagoPendiente como usado
   ├─ Commit de transacción
   └─ Confirma con nuevo saldo

5. Usuario consulta saldo
   ├─ REST API recibe GET /consultar-saldo
   ├─ SOAP busca Cliente + Billetera
   └─ Retorna saldo actual
```

---

## 🧪 Ejemplo de Test Completo

```bash
# 1. Iniciar servicios
docker-compose up -d

# 2. Esperar health checks
docker-compose ps

# 3. Ejecutar migraciones
docker exec -it epayco-soap php bin/console doctrine:migrations:migrate --no-interaction

# 4. Registrar cliente con curl
curl -X POST http://localhost:3000/api/registro-cliente \
  -H "Content-Type: application/json" \
  -d '{
    "documento": "123456789",
    "nombres": "Juan Pérez",
    "email": "juan@example.com",
    "celular": "3001234567"
  }'

# 5. Recargar billetera
curl -X POST http://localhost:3000/api/recarga-billetera \
  -H "Content-Type: application/json" \
  -d '{
    "documento": "123456789",
    "celular": "3001234567",
    "valor": 50000
  }'

# 6. Consultar saldo
curl -X GET "http://localhost:3000/api/consultar-saldo?documento=123456789&celular=3001234567"

# 7. Iniciar pago
curl -X POST http://localhost:3000/api/pagar \
  -H "Content-Type: application/json" \
  -d '{
    "documento": "123456789",
    "celular": "3001234567",
    "monto": 25000
  }'

# 8. Ver email en MailHog: http://localhost:8025
# (Copiar session_id y token del email)

# 9. Confirmar pago (reemplazar valores)
curl -X POST http://localhost:3000/api/confirmar-pago \
  -H "Content-Type: application/json" \
  -d '{
    "session_id": "550e8400-e29b-41d4-a716-446655440000",
    "token": "123456"
  }'
```

---

## 📝 Notas Importantes

- 📌 Los tokens de pago expiran en **30 minutos**
- 📌 Los tokens se envían por **email** (ver en MailHog)
- 📌 La BD se persiste en un **volumen Docker** (`mysql_data`)
- 📌 Los servicios se comunican por una **red Docker interna**
- 📌 Health checks garantizan que los servicios estén **listos** antes de iniciar
- 📌 El `session_id` es un **UUID único** para cada pago

---

## 🆘 Solución de Problemas

### "Connection refused" en SOAP
```bash
# Verificar que MySQL esté sano
docker-compose ps

# Ver logs de SOAP
docker-compose logs soap-service

# Reintentar migraciones
docker exec -it epayco-soap php bin/console doctrine:migrations:migrate
```

### REST no puede conectar a SOAP
```bash
# Verificar red Docker
docker network ls

# Verificar conectividad
docker exec -it epayco-rest curl http://soap-service:8000/soap/wsdl
```

### Emails no aparecen
```bash
# Verificar MailHog está corriendo
docker-compose ps | grep mailhog

# Acceder a http://localhost:8025
```

### Reset completo (⚠️ Borra todo)
```bash
docker-compose down -v
docker-compose up -d
docker exec -it epayco-soap php bin/console doctrine:migrations:migrate --no-interaction
```

---

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el repositorio
2. Crear rama feature (`git checkout -b feature/AmazingFeature`)
3. Commit cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request

---

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo `LICENSE` para detalles.

---

## 📞 Soporte

Para reportar bugs o sugerir mejoras, por favor abre un issue en el repositorio.

---

---

## ✅ Estado Actual del Proyecto

### Implementación Completada

| Feature | Estado | Descripción |
|---------|--------|-------------|
| Registro de Cliente | ✅ COMPLETADO | Validación con Constraints + ValidatorInterface |
| Recarga de Billetera | ✅ COMPLETADO | Transacciones atómicas |
| Flujo de Pago | ✅ COMPLETADO | Con token por email + confirmación |
| Consulta de Saldo | ✅ COMPLETADO | En tiempo real |
| ValidatorInterface | ✅ COMPLETADO | Autowiring en WalletService |
| Tests Unitarios | ✅ COMPLETADO | 44/44 pasando (FASE 2 completada) |
| Constraints en Entities | ✅ COMPLETADO | Validación de Cliente, Transaccion, PagoPendiente |
| Docker Health Checks | ✅ COMPLETADO | Todos los servicios saludables |
| Migraciones Doctrine | ✅ COMPLETADO | 5 versiones con todas las entidades |
| **Colección Postman** | ✅ **NUEVO** | 5 servicios con casos de prueba + documentación |

### Servicios Activos

```
✅ REST Service (Express.js)    - Puerto 3000
✅ SOAP Service (Symfony)        - Puerto 8000
✅ MySQL Database                - Puerto 3306
✅ MailHog (Email Testing)       - Puerto 8025
```

### Últimas Mejoras (Sesión Actual - FASE 2 + Postman)

1. **Creación de Colección Postman Completa** ✅
   - `docs/Epayco-Wallet.postman_collection.json` (Colección con 5 servicios)
   - Tests automatizados en cada request
   - Variables de entorno preconfiguras
   - Casos de éxito y error documentados

2. **Documentación Postman** ✅
   - `docs/POSTMAN_COLLECTION.md` - Guía completa de uso
   - Flujos de prueba paso a paso
   - Ejemplos de respuesta para cada endpoint
   - Solución de problemas

3. **Actualización README** ✅
   - Referencia a colección Postman
   - Instrucciones de importación
   - Características de la colección

### Archivos Agregados en Sesión Actual

| Archivo | Descripción | Estado |
|---------|-------------|--------|
| `docs/Epayco-Wallet.postman_collection.json` | Colección Postman completa | ✅ NUEVO |
| `docs/POSTMAN_COLLECTION.md` | Guía de uso de Postman | ✅ NUEVO |

### Flujos Testeados

**Flujo Completo (End-to-End):**
```
1. Registrar Cliente → 2. Recargar Billetera → 3. Iniciar Pago 
→ 4. Obtener Token (Email) → 5. Confirmar Pago → 6. Consultar Saldo
```

**Casos de Error:**
- ✅ Cliente duplicado (email/documento)
- ✅ Cliente no encontrado
- ✅ Saldo insuficiente
- ✅ Sesión no encontrada
- ✅ Token incorrecto
- ✅ Sesión expirada
