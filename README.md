# 💰 BilleteraVirtual - ePayco

Sistema de billetera virtual con arquitectura de microservicios. Prueba técnica para el cargo de Desarrollador BackEnd en ePayco.

## 🎯 Descripción del Proyecto

Sistema de billetera virtual que permite a los usuarios:
- 📋 Registrarse y crear una billetera digital
- 💳 Recargar saldo en su billetera
- 💸 Realizar pagos con confirmación por token (enviado por email)
- 📊 Consultar saldo disponible
- 📝 Historial de transacciones

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

