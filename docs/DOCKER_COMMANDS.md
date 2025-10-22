# 🐳 Comandos Docker Útiles - BilleteraVirtual

Guía rápida de comandos Docker para gestionar y monitorear el sistema de Billetera Virtual.

---

## 🚀 Gestión de Servicios

### Iniciar todos los servicios

```bash
docker-compose up -d
```

### Ver estado de servicios

```bash
docker-compose ps
```

Todos los servicios deben estar en estado **healthy** ✅

### Ver salida de servicios en vivo

```bash
docker-compose up
```

### Detener servicios (sin eliminar datos)

```bash
docker-compose down
```

### Detener servicios y eliminar todo (⚠️ borra BD)

```bash
docker-compose down -v
```

### Reconstruir imágenes

```bash
docker-compose up -d --build
```

### Reiniciar un servicio específico

```bash
docker-compose restart epayco-soap
docker-compose restart epayco-rest
docker-compose restart epayco-db
docker-compose restart mailhog
```

---

## 📋 Ver Logs

### Logs de todos los servicios (en vivo)

```bash
docker-compose logs -f
```

### Logs de un servicio específico

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

### Últimas N líneas de logs

```bash
# Últimas 50 líneas
docker-compose logs --tail=50 epayco-soap

# Últimas 100 líneas
docker-compose logs --tail=100 epayco-rest
```

### Logs desde los últimos N minutos

```bash
# Desde los últimos 5 minutos
docker-compose logs --since 5m epayco-soap

# Desde los últimos 1 minuto
docker-compose logs --since 1m epayco-db
```

---

## 🛠️ Ejecutar Comandos en Contenedores

### SOAP Service (PHP/Symfony)

```bash
# Ejecutar migraciones
docker exec -it epayco-soap php bin/console doctrine:migrations:migrate

# Ver estado de migraciones
docker exec -it epayco-soap php bin/console doctrine:migrations:status

# Generar nueva migración (después de cambiar entities)
docker exec -it epayco-soap php bin/console doctrine:migrations:diff

# Ver información del kernel
docker exec -it epayco-soap php bin/console about

# Ejecutar tests
docker exec -it epayco-soap php bin/phpunit

# Ejecutar test específico
docker exec -it epayco-soap php bin/phpunit tests/Integration/Service/RegistroClienteTest.php
```

### REST Service (Node.js)

```bash
# Ver versión de Node
docker exec -it epayco-rest node --version

# Ver versión de npm
docker exec -it epayco-rest npm --version

# Listar dependencias instaladas
docker exec -it epayco-rest npm list
```

### MySQL

```bash
# Conectar a MySQL CLI
docker exec -it epayco-db mysql -uepayco -pepayco123 epayco_wallet

# Ver clientes registrados
docker exec -it epayco-db mysql -uepayco -pepayco123 epayco_wallet -e "SELECT id, numeroDocumento, nombres, email, celular FROM clientes;"

# Ver billetes y saldos
docker exec -it epayco-db mysql -uepayco -pepayco123 epayco_wallet -e "SELECT b.id, b.cliente_id, b.saldo FROM billetes b;"

# Ver transacciones
docker exec -it epayco-db mysql -uepayco -pepayco123 epayco_wallet -e "SELECT id, billetera_id, tipo, monto, estado, fecha FROM transacciones ORDER BY fecha DESC;"

# Ver pagos pendientes
docker exec -it epayco-db mysql -uepayco -pepayco123 epayco_wallet -e "SELECT id, session_id, monto, estado, fecha_creacion FROM pago_pendiente;"

# Contar registros
docker exec -it epayco-db mysql -uepayco -pepayco123 epayco_wallet -e "SELECT COUNT(*) as 'Total Clientes' FROM clientes; SELECT COUNT(*) as 'Total Transacciones' FROM transacciones;"
```

### MailHog

```bash
# Ver todos los emails (JSON)
docker exec -it mailhog curl http://localhost:8025/api/v2/messages

# Limpiar emails
docker exec -it mailhog curl -X DELETE http://localhost:8025/api/v1/messages
```

---

## 🔍 Monitoreo y Debugging

### Ver uso de recursos en tiempo real

```bash
docker stats
```

Muestra CPU, memoria, I/O en vivo para cada contenedor.

### Ver uso de recursos de un contenedor específico

```bash
docker stats epayco-soap
docker stats epayco-db
```

### Verificar health status de un servicio

```bash
# Status general
docker inspect --format='{{json .State.Health}}' epayco-soap

# Más detallado (con colores)
docker inspect epayco-soap | grep -A 10 '"Health"'
```

### Verificar conectividad entre servicios

```bash
# REST → SOAP
docker exec -it epayco-rest curl -v http://epayco-soap:8000/soap/wsdl

# REST → MySQL
docker exec -it epayco-rest mysql -h epayco-db -uepayco -pepayco123 -e "SELECT 1"

# SOAP → MySQL (con Doctrine)
docker exec -it epayco-soap php bin/console doctrine:query:dql "SELECT COUNT(c) FROM App\\Entity\\Cliente c"
```

### Ver IP de un contenedor

```bash
docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' epayco-soap
```

### Ver variables de entorno

```bash
docker exec epayco-soap env | grep DATABASE
docker exec epayco-soap env | grep MAIL
```

### Ver configuración de un contenedor

```bash
docker inspect epayco-soap | jq '.[] | {Name, State, Config}'
```

---

## 📊 Información de Red y Volúmenes

### Ver redes de Docker

```bash
docker network ls
docker network inspect billeteravirtual_default
```

### Ver volúmenes

```bash
docker volume ls
docker volume inspect billeteravirtual_mysql_data
```

### Ver espacio usado por Docker

```bash
docker system df
```

---

## 🧹 Limpiar Recursos

### Eliminar contenedores detenidos

```bash
docker container prune
```

### Eliminar imágenes sin usar

```bash
docker image prune
```

### Eliminar volúmenes sin usar

```bash
docker volume prune
```

### Limpiar todo (⚠️ elimina contenedores, imágenes, redes, volúmenes)

```bash
docker system prune -a --volumes
```

### Eliminar base de datos específicamente

```bash
docker volume rm billeteravirtual_mysql_data
```

---

## 🆘 Debugging Avanzado

### Ver logs de un contenedor que se reinicia

```bash
docker logs epayco-soap 2>&1 | tail -100
```

### Ver solo errores en logs

```bash
docker-compose logs epayco-soap | grep -i error
```

### Ejecutar un contenedor en modo interactivo

```bash
docker exec -it epayco-soap bash
# Ahora estás dentro del contenedor
cd /var/www/html
ls -la
```

### Ver cambios en archivos dentro del contenedor

```bash
docker diff epayco-soap
```

### Copiar archivos del contenedor a la máquina

```bash
docker cp epayco-soap:/var/www/html/public/wallet.wsdl ./wallet.wsdl
```

### Copiar archivos de la máquina al contenedor

```bash
docker cp ./wallet.wsdl epayco-soap:/var/www/html/public/wallet.wsdl
```

---

## 🔄 Workflows Comunes

### Reiniciar servicios después de cambios en código

```bash
# 1. Detener servicios
docker-compose down

# 2. Realizar cambios en el código
# ... editar archivos ...

# 3. Reconstruir imágenes
docker-compose up -d --build

# 4. Ejecutar migraciones si es necesario
docker exec -it epayco-soap php bin/console doctrine:migrations:migrate
```

### Reset completo (borra todo y empieza de cero)

```bash
# 1. Detener y eliminar todo
docker-compose down -v

# 2. Levantar servicios nuevamente
docker-compose up -d

# 3. Esperar health checks
sleep 30
docker-compose ps

# 4. Ejecutar migraciones
docker exec -it epayco-soap php bin/console doctrine:migrations:migrate --no-interaction
```

### Verificar que todo esté funcionando

```bash
#!/bin/bash
echo "🔍 Verificando estado de servicios..."
docker-compose ps

echo -e "\n🌐 Verificando conectividad SOAP..."
curl -s http://localhost:8000/soap/wsdl > /dev/null && echo "✅ SOAP OK" || echo "❌ SOAP FAIL"

echo -e "\n🌐 Verificando conectividad REST..."
curl -s http://localhost:3000/health > /dev/null && echo "✅ REST OK" || echo "❌ REST FAIL"

echo -e "\n💾 Verificando MySQL..."
docker exec -it epayco-db mysql -uepayco -pepayco123 -e "SELECT 1" > /dev/null 2>&1 && echo "✅ MySQL OK" || echo "❌ MySQL FAIL"

echo -e "\n📧 Verificando MailHog..."
curl -s http://localhost:8025/api/v2/messages > /dev/null && echo "✅ MailHog OK" || echo "❌ MailHog FAIL"
```

---

## 📚 Referencias Útiles

- **Docker Compose Docs:** https://docs.docker.com/compose/compose-file/
- **Docker CLI Docs:** https://docs.docker.com/engine/reference/run/
- **MySQL CLI:** https://dev.mysql.com/doc/refman/8.0/en/mysql-command-options.html
- **Symfony CLI:** https://symfony.com/doc/current/reference/commands/index.html

---

**Última actualización:** Octubre 2025
