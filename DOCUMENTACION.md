# 📚 Documentación Completa - BilleteraVirtual

## Tabla de Contenidos

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Arquitectura Técnica](#arquitectura-técnica)
3. [Guía de Instalación](#guía-de-instalación)
4. [API Reference](#api-reference)
5. [Estructura de Base de Datos](#estructura-de-base-de-datos)
6. [Guía de Desarrollo](#guía-de-desarrollo)
7. [Mejores Prácticas](#mejores-prácticas)

---

## Resumen Ejecutivo

**BilleteraVirtual** es un sistema de billetera digital que permite a los usuarios:

- 🔐 Registrarse de forma segura
- 💳 Recargar saldo
- 💸 Realizar pagos con confirmación por token
- 📊 Consultar su saldo

### Características Clave

✅ **Arquitectura de Microservicios**
- REST API (Express.js) - Acceso público
- SOAP Service (Symfony + Doctrine) - Lógica de negocio

✅ **Seguridad Financiera**
- Transacciones ACID con MySQL InnoDB
- Validación en ambos extremos
- Tokens de confirmación por email

✅ **DevOps Profesional**
- Dockerización multi-stage
- Health checks robustos
- Orquestación con Docker Compose

✅ **Código de Calidad**
- Patrón Repository de Doctrine
- Validación con Joi y Symfony Validator
- Manejo de errores centralizado

---

## Arquitectura Técnica

### Diagrama de Capas

```
┌─────────────────────────────────────────────┐
│         Cliente (Browser/App)               │
└────────────────┬────────────────────────────┘
                 │ HTTP/JSON
┌────────────────▼────────────────────────────┐
│    REST API (Express.js)                    │
│  ├─ Validación Joi                          │
│  ├─ Middleware de Errores                   │
│  └─ CORS y Seguridad                        │
└────────────────┬────────────────────────────┘
                 │ SOAP Protocol
┌────────────────▼────────────────────────────┐
│  SOAP Service (Symfony + Doctrine)          │
│  ├─ WalletService (Lógica)                  │
│  ├─ Validación Symfony                      │
│  └─ Transacciones ACID                      │
└────────────────┬────────────────────────────┘
                 │ SQL/ORM
┌────────────────▼────────────────────────────┐
│   MySQL Database (InnoDB)                   │
│  ├─ Cliente                                 │
│  ├─ Billetera                               │
│  ├─ Transaccion                             │
│  └─ PagoPendiente                           │
└─────────────────────────────────────────────┘
```

### Flujo de Datos

#### Registro de Cliente
```
REST API
  ├─ POST /api/registro-cliente
  ├─ Validar con Joi schema
  ├─ Llamar a SOAP service
  │
  └─→ SOAP Service
      ├─ Validar con Symfony Validator
      ├─ Verificar duplicados (QueryBuilder)
      ├─ Crear Cliente (Doctrine Entity)
      ├─ Crear Billetera (OneToOne)
      ├─ EntityManager->flush()
      │
      └─→ MySQL Database
          ├─ INSERT INTO clientes
          ├─ INSERT INTO billeteras
          └─ COMMIT
```

#### Confirmación de Pago
```
REST API
  ├─ POST /api/confirmar-pago
  ├─ Validar token/session_id
  └─→ SOAP Service
      ├─ Buscar PagoPendiente (Repository)
      ├─ Validar expiración
      ├─ EntityManager->beginTransaction()
      ├─ Decrementar saldo (Billetera)
      ├─ Crear Transaccion (registro)
      ├─ Marcar PagoPendiente como usado
      ├─ EntityManager->flush()
      ├─ EntityManager->commit()
      │
      └─→ MySQL Database
          ├─ UPDATE billeteras SET saldo = saldo - monto
          ├─ INSERT INTO transacciones
          ├─ UPDATE pagos_pendientes SET usado = true
          └─ COMMIT
```

---

## Guía de Instalación

### Prerrequisitos del Sistema

```bash
# Verificar Docker
docker --version
# Docker version 20.10.0 o superior

# Verificar Docker Compose
docker-compose --version
# Docker Compose version 2.0.0 o superior
```

### Instalación Paso a Paso

#### 1. Clonar Repositorio
```bash
git clone https://github.com/tu-usuario/BilleteraVirtual.git
cd BilleteraVirtual
```

#### 2. Crear Archivo .env
```bash
cp .env.example .env
```

**Contenido de .env:**
```env
COMPOSE_PROJECT_NAME=epayco
MYSQL_ROOT_PASSWORD=root_secure_pass
MYSQL_DATABASE=epayco_wallet
MYSQL_USER=epayco
MYSQL_PASSWORD=epayco123
MYSQL_PORT=3306
SOAP_PORT=8000
REST_PORT=3000
MAILHOG_SMTP_PORT=1025
MAILHOG_UI_PORT=8025
```

#### 3. Construir Imágenes
```bash
docker-compose build --parallel
```

#### 4. Iniciar Servicios
```bash
docker-compose up -d
```

#### 5. Ejecutar Migraciones
```bash
docker exec -it epayco-soap php bin/console doctrine:migrations:migrate --no-interaction
```

#### 6. Verificar Servicios
```bash
docker-compose ps

# Esperado: Todos en estado "healthy"
# NAME          STATUS           PORTS
# epayco-db     Up (healthy)     3306/tcp
# epayco-soap   Up (healthy)     8000/tcp
# epayco-rest   Up (healthy)     3000/tcp
# epayco-mailhog Up              1025/tcp, 8025/tcp
```

#### 7. Verificar Conectividad

```bash
# REST API
curl http://localhost:3000/health
# Respuesta: {"status":"OK"}

# SOAP WSDL
curl http://localhost:8000/soap/wsdl | head -5

# Base de datos
docker exec -it epayco-db mysql -uepayco -pepayco123 epayco_wallet -e "SHOW TABLES;"
# Respuesta: 4 tablas creadas
```

---

## API Reference

### Convenciones

- **Base URL:** `http://localhost:3000/api`
- **Content-Type:** `application/json`
- **Método HTTP:** POST (escritura), GET (lectura)

### Estructura de Respuesta

**Exitosa (HTTP 200):**
```json
{
  "success": true,
  "cod_error": "00",
  "message_error": "Operación exitosa",
  "data": {
    // Datos específicos del endpoint
  }
}
```

**Error (HTTP 400-500):**
```json
{
  "success": false,
  "cod_error": "05",
  "message_error": "Saldo insuficiente",
  "data": []
}
```

### Endpoint: POST /registro-cliente

**Propósito:** Registrar nuevo cliente y crear billetera

**Request:**
```json
{
  "documento": "123456789",
  "nombres": "Juan Pérez García",
  "email": "juan@example.com",
  "celular": "3001234567"
}
```

**Validación:**
- `documento`: Requerido, máx 20 caracteres
- `nombres`: Requerido, máx 255 caracteres
- `email`: Requerido, formato email válido
- `celular`: Requerido, máx 20 caracteres

**Response (200):**
```json
{
  "success": true,
  "cod_error": "00",
  "message_error": "Cliente registrado exitosamente",
  "data": {
    "cliente_id": 1,
    "documento": "123456789",
    "nombres": "Juan Pérez García"
  }
}
```

**Errores Posibles:**
- `02` - Cliente ya existe
- `09` - Error de base de datos

---

### Endpoint: POST /recarga-billetera

**Propósito:** Agregar saldo a la billetera

**Request:**
```json
{
  "documento": "123456789",
  "celular": "3001234567",
  "valor": 50000
}
```

**Validación:**
- `documento` y `celular` deben coincidir con cliente existente
- `valor`: Número positivo

**Response (200):**
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

**Errores Posibles:**
- `03` - Cliente no encontrado
- `04` - Documento/celular no coinciden
- `09` - Error de base de datos

---

### Endpoint: POST /pagar

**Propósito:** Iniciar proceso de pago con confirmación por token

**Request:**
```json
{
  "documento": "123456789",
  "celular": "3001234567",
  "monto": 25000
}
```

**Validación:**
- Documento y celular deben existir
- Monto no puede exceder saldo

**Proceso:**
1. Validar saldo disponible
2. Generar token de 6 dígitos
3. Crear PagoPendiente con UUID
4. Enviar email con token
5. Retornar session_id

**Response (200):**
```json
{
  "success": true,
  "cod_error": "00",
  "message_error": "Token enviado al correo electrónico",
  "data": {
    "session_id": "550e8400-e29b-41d4-a716-446655440000",
    "monto": 25000
  }
}
```

**Errores Posibles:**
- `03` - Cliente no encontrado
- `05` - Saldo insuficiente
- `10` - Error al enviar email

---

### Endpoint: POST /confirmar-pago

**Propósito:** Confirmar pago con token recibido por email

**Request:**
```json
{
  "session_id": "550e8400-e29b-41d4-a716-446655440000",
  "token": "123456"
}
```

**Validación:**
- `session_id` debe existir
- `token` debe ser exacto (6 dígitos)
- Sesión no puede estar expirada (30 min)
- Sesión no puede haber sido usada

**Transacción de BD:**
```sql
BEGIN TRANSACTION
  UPDATE billeteras SET saldo = saldo - monto
  INSERT INTO transacciones VALUES (...)
  UPDATE pagos_pendientes SET usado = true
COMMIT
```

**Response (200):**
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

**Errores Posibles:**
- `06` - Sesión no encontrada
- `07` - Token incorrecto
- `08` - Sesión expirada
- `09` - Error de base de datos

---

### Endpoint: GET /consultar-saldo

**Propósito:** Consultar saldo disponible

**Request:**
```
GET /consultar-saldo?documento=123456789&celular=3001234567
```

**Parámetros Query:**
- `documento`: Requerido
- `celular`: Requerido

**Response (200):**
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

**Errores Posibles:**
- `03` - Cliente no encontrado
- `04` - Documento/celular no coinciden

---

## Estructura de Base de Datos

### Tabla: clientes

```sql
CREATE TABLE clientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  documento VARCHAR(20) NOT NULL UNIQUE,
  nombres VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  celular VARCHAR(20) NOT NULL,
  createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_documento (documento),
  INDEX idx_email (email)
) ENGINE=InnoDB CHARSET=utf8mb4;
```

### Tabla: billeteras

```sql
CREATE TABLE billeteras (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL UNIQUE,
  saldo DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  updatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
  INDEX idx_saldo (saldo)
) ENGINE=InnoDB CHARSET=utf8mb4;
```

### Tabla: transacciones

```sql
CREATE TABLE transacciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  billetera_id INT NOT NULL,
  tipo VARCHAR(20) NOT NULL,
  monto DECIMAL(10, 2) NOT NULL,
  descripcion TEXT,
  createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (billetera_id) REFERENCES billeteras(id) ON DELETE CASCADE,
  INDEX idx_billetera (billetera_id),
  INDEX idx_tipo (tipo),
  INDEX idx_fecha (createdAt)
) ENGINE=InnoDB CHARSET=utf8mb4;
```

### Tabla: pagos_pendientes

```sql
CREATE TABLE pagos_pendientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sessionId VARCHAR(36) NOT NULL UNIQUE,
  billetera_id INT NOT NULL,
  monto DECIMAL(10, 2) NOT NULL,
  token VARCHAR(6) NOT NULL,
  usado BOOLEAN NOT NULL DEFAULT false,
  expiraEn DATETIME NOT NULL,
  createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (billetera_id) REFERENCES billeteras(id) ON DELETE CASCADE,
  INDEX idx_session (sessionId),
  INDEX idx_expiracion (expiraEn)
) ENGINE=InnoDB CHARSET=utf8mb4;
```

---

## Guía de Desarrollo

### Estructura de Directorios

#### SOAP Service (Symfony)
```
soap-service/
├── src/
│   ├── Entity/
│   │   ├── Cliente.php          # Entidad Doctrine
│   │   ├── Billetera.php
│   │   ├── Transaccion.php
│   │   └── PagoPendiente.php
│   ├── Repository/
│   │   ├── ClienteRepository.php # Métodos de consulta
│   │   ├── BilleteraRepository.php
│   │   └── ...
│   ├── Service/
│   │   └── WalletService.php    # Lógica de negocio
│   └── Controller/
│       └── SoapController.php    # Endpoint SOAP
├── migrations/                   # Migraciones Doctrine
├── public/
│   └── wallet.wsdl              # Definición SOAP
└── config/
    └── packages/
        └── doctrine.yaml        # Configuración BD
```

#### REST Service (Express)
```
rest-service/
├── src/
│   ├── controllers/
│   │   └── walletController.js  # Lógica de endpoints
│   ├── services/
│   │   └── soapClient.js        # Cliente SOAP
│   ├── middlewares/
│   │   ├── validator.js         # Validación Joi
│   │   └── errorHandler.js      # Manejo errores
│   ├── routes/
│   │   └── wallet.js            # Definición de rutas
│   ├── validators/
│   │   └── schemas.js           # Esquemas Joi
│   ├── app.js                   # Configuración Express
│   └── server.js                # Punto de entrada
└── .env                         # Variables entorno
```

### Desarrollo Local

#### 1. Modificar Código SOAP

```bash
# Los cambios se reflejan automáticamente (volumen montado)
# Editar: soap-service/src/Service/WalletService.php

# Ejecutar comando Symfony
docker exec -it epayco-soap php bin/console doctrine:query:sql "SELECT 1"
```

#### 2. Modificar Código REST

```bash
# Los cambios se reflejan automáticamente con nodemon
# Editar: rest-service/src/controllers/walletController.js

# Los logs aparecen en:
docker-compose logs -f rest-service
```

#### 3. Agregar Nueva Entidad Doctrine

```bash
# Crear entidad
docker exec -it epayco-soap php bin/console make:entity NuevaEntidad

# Generar migración
docker exec -it epayco-soap php bin/console make:migration

# Ejecutar migración
docker exec -it epayco-soap php bin/console doctrine:migrations:migrate
```

#### 4. Acceder a MySQL

```bash
# Cliente MySQL interactivo
docker exec -it epayco-db mysql -uepayco -pepayco123 epayco_wallet

# Queries:
mysql> SELECT * FROM clientes;
mysql> SELECT * FROM transacciones;
```

---

## Mejores Prácticas

### 1. Validación en Capas

```
Cliente
  ↓ (HTML5)
REST API (Joi)
  ↓ Validar formato
SOAP Service (Symfony Validator)
  ↓ Validar reglas de negocio
Base de Datos (Constraints)
  ↓ Validar restricciones SQL
```

### 2. Transacciones Financieras

**Siempre usar transacciones ACID:**
```php
try {
    $entityManager->beginTransaction();
    
    // Operaciones
    $billetera->restar($monto);
    $this->crearTransaccion($data);
    
    $entityManager->flush();
    $entityManager->commit();
} catch (\Exception $e) {
    $entityManager->rollback();
    throw $e;
}
```

### 3. Errores y Logging

```php
// ❌ Evitar
throw new Exception("Error");

// ✅ Usar
throw new \InvalidArgumentException("Saldo insuficiente");
throw new \RuntimeException("Error al enviar email");
```

### 4. Consultas Optimizadas

```php
// ❌ N+1 Query Problem
$clientes = $repo->findAll();
foreach ($clientes as $cliente) {
    $billetera = $cliente->getBilletera(); // Query extra!
}

// ✅ Usar JOIN
$qb = $repo->createQueryBuilder('c')
    ->leftJoin('c.billetera', 'b')
    ->addSelect('b')
    ->getQuery();
```

### 5. Seguridad

```php
// ❌ Evitar
$documento = $_POST['documento'];
$query = "SELECT * FROM clientes WHERE documento = '$documento'";

// ✅ Usar Doctrine
$cliente = $repo->findByDocumento($documento);
```

### 6. Testing

```bash
# Crear pruebas unitarias
docker exec -it epayco-soap php bin/console make:test

# Ejecutar tests
docker exec -it epayco-soap php bin/phpunit
```

---

## Troubleshooting

### Problema: "Connection refused"

```bash
# Verificar servicios
docker-compose ps

# Ver logs
docker-compose logs soap-service

# Reiniciar servicio
docker-compose restart soap-service
```

### Problema: Migraciones fallidas

```bash
# Ver estado de migraciones
docker exec -it epayco-soap php bin/console doctrine:migrations:status

# Revertir última migración
docker exec -it epayco-soap php bin/console doctrine:migrations:migrate prev

# Ejecutar nuevamente
docker exec -it epayco-soap php bin/console doctrine:migrations:migrate
```

### Problema: Emails no llegan

```bash
# Acceder a MailHog UI
http://localhost:8025

# Verificar configuración MAILER_DSN
docker-compose logs mailhog
```

---

## Recursos Adicionales

- [Symfony Documentation](https://symfony.com/doc)
- [Doctrine ORM](https://www.doctrine-project.org/)
- [Express.js Guide](https://expressjs.com/)
- [Docker Best Practices](https://docs.docker.com/develop/dev-best-practices/)
- [SOAP Protocol](https://www.w3.org/TR/soap12/)

---

**Versión:** 1.0.0  
**Última actualización:** Octubre 2025  
**Autor:** Equipo de Desarrollo ePayco
