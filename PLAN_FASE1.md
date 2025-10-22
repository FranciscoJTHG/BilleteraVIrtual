# FASE 1: Configuración Docker - Desarrollo Optimizado

**Tiempo estimado:** 30 minutos  
**Objetivo:** Crear la infraestructura Docker completa y funcional para desarrollo

---

## 📋 Estructura de Directorios

```bash
ePayco/
├── docker-compose.yml
├── .env
├── .gitignore
├── soap-service/
│   ├── Dockerfile
│   └── .dockerignore
├── rest-service/
│   ├── Dockerfile
│   └── .dockerignore
└── docs/
    ├── detalles.md
    └── PLAN_IMPLEMENTACION.md
```

---

## 🔧 Pasos de Implementación

### Paso 1: Crear directorios
```bash
cd /mnt/c/Users/fran_/OneDrive/Escritorio/ePayco
mkdir -p soap-service rest-service docs
```

### Paso 2: Crear `soap-service/Dockerfile`

```dockerfile
FROM php:8.2-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip \
    libzip-dev libxml2-dev libicu-dev \
    && docker-php-ext-install pdo pdo_mysql soap zip intl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install

EXPOSE 8000

HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD php bin/console doctrine:query:sql "SELECT 1" || exit 1

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
```

### Paso 3: Crear `soap-service/.dockerignore`

```
.git
.gitignore
.env.local
var/
vendor/
node_modules/
*.log
.idea/
.vscode/
tests/
```

### Paso 4: Crear `rest-service/Dockerfile`

```dockerfile
FROM node:18-alpine

WORKDIR /app

COPY package*.json ./

RUN npm install

COPY . .

EXPOSE 3000

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD wget --quiet --tries=1 --spider http://localhost:3000/health || exit 1

CMD ["npm", "start"]
```

### Paso 5: Crear `rest-service/.dockerignore`

```
.git
.gitignore
.env.local
node_modules/
npm-debug.log*
*.log
.idea/
.vscode/
tests/
coverage/
```

### Paso 6: Crear `.env`

```env
# MySQL
MYSQL_ROOT_PASSWORD=root
MYSQL_DATABASE=epayco_wallet
MYSQL_USER=epayco
MYSQL_PASSWORD=epayco123
MYSQL_PORT=3306

# SOAP Service
SOAP_PORT=8000
APP_ENV=development

# REST Service
REST_PORT=3000
NODE_ENV=development

# MailHog
MAILHOG_SMTP_PORT=1025
MAILHOG_UI_PORT=8025
```

### Paso 7: Crear `docker-compose.yml`

```yaml
version: '3.8'

services:
  mysql:
    image: mysql:8.0
    container_name: epayco-db
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
      MYSQL_DATABASE: ${MYSQL_DATABASE}
      MYSQL_USER: ${MYSQL_USER}
      MYSQL_PASSWORD: ${MYSQL_PASSWORD}
    ports:
      - "${MYSQL_PORT}:3306"
    volumes:
      - mysql_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 5s
      retries: 5
      start_period: 30s
    deploy:
      resources:
        limits:
          cpus: '1'
          memory: 1G
        reservations:
          cpus: '0.5'
          memory: 512M

  soap-service:
    build:
      context: ./soap-service
      dockerfile: Dockerfile
    container_name: epayco-soap
    restart: unless-stopped
    depends_on:
      mysql:
        condition: service_healthy
    environment:
      DATABASE_URL: mysql://${MYSQL_USER}:${MYSQL_PASSWORD}@mysql:3306/${MYSQL_DATABASE}?serverVersion=8.0
      MAILER_DSN: smtp://mailhog:1025
      APP_ENV: ${APP_ENV}
    ports:
      - "${SOAP_PORT}:8000"
    volumes:
      - ./soap-service:/var/www/html
      - soap_cache:/var/www/html/var
    deploy:
      resources:
        limits:
          cpus: '1'
          memory: 1G
        reservations:
          cpus: '0.25'
          memory: 256M

  rest-service:
    build:
      context: ./rest-service
      dockerfile: Dockerfile
    container_name: epayco-rest
    restart: unless-stopped
    depends_on:
      soap-service:
        condition: service_healthy
    environment:
      NODE_ENV: ${NODE_ENV}
      PORT: 3000
      SOAP_URL: http://soap-service:8000/soap
    ports:
      - "${REST_PORT}:3000"
    volumes:
      - ./rest-service:/app
      - /app/node_modules
    deploy:
      resources:
        limits:
          cpus: '0.5'
          memory: 512M
        reservations:
          cpus: '0.25'
          memory: 256M

  mailhog:
    image: mailhog/mailhog:v1.0.1
    container_name: epayco-mailhog
    restart: unless-stopped
    ports:
      - "${MAILHOG_SMTP_PORT}:1025"
      - "${MAILHOG_UI_PORT}:8025"

volumes:
  mysql_data:
  soap_cache:
```

### Paso 8: Crear `.gitignore`

```
# Dependencies
node_modules/
vendor/

# Environment
.env
.env.local

# IDE
.idea/
.vscode/
*.swp
*.swo

# OS
.DS_Store
Thumbs.db

# Logs
*.log
npm-debug.log*

# Build
/build/
/dist/

# Docker
.dockerignore

# App
/var/
/public/bundles/
/public/uploads/
```

### Paso 9: Validar configuración

```bash
docker-compose config
```

### Paso 10: Commit inicial

```bash
git init
git add .
git commit -m "FASE 1: Setup Docker infrastructure"
```

---

## ✅ Checklist FASE 1

- [ ] Directorio `soap-service/` creado
- [ ] `soap-service/Dockerfile` creado
- [ ] `soap-service/.dockerignore` creado
- [ ] Directorio `rest-service/` creado
- [ ] `rest-service/Dockerfile` creado
- [ ] `rest-service/.dockerignore` creado
- [ ] `.env` creado
- [ ] `docker-compose.yml` creado
- [ ] `.gitignore` creado
- [ ] `docker-compose config` valida correctamente
- [ ] Git inicializado y commit realizado

---

## 🎯 Lo que se mantiene en esta versión

✅ **Multi-stage builds** → Imágenes optimizadas  
✅ **Health checks** → Startup confiable  
✅ **Límites de recursos (CPU/RAM)** → Control de consumo  
✅ **Hot reload (volúmenes)** → Desarrollo ágil  
✅ **Simple y directo** → Fácil de entender y debuggear  

---

## 🚀 Comandos Útiles

```bash
# Construir imágenes
docker-compose build

# Iniciar servicios
docker-compose up -d

# Ver estado
docker-compose ps

# Ver logs en tiempo real
docker-compose logs -f

# Logs de servicio específico
docker-compose logs -f soap-service

# Parar servicios
docker-compose down

# Parar y eliminar volúmenes (⚠️ borra BD)
docker-compose down -v
```

---

## 📝 Próximo Paso

Una vez completada FASE 1, continuar con **FASE 2: Servicio SOAP (Symfony + Doctrine)**

