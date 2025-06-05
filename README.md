# Proyecto Laravel con Docker

Este proyecto utiliza Docker para facilitar el desarrollo y despliegue de una aplicación Laravel.

## Requisitos previos
- [Docker](https://www.docker.com/get-started)
- [Docker Compose](https://docs.docker.com/compose/)

## Estructura de carpetas
- `src/` : Código fuente de Laravel
- `dockerfiles/` : Dockerfiles personalizados para PHP, Nginx y Composer
- `mysql/` : Archivos de configuración y entorno de MySQL
- `mysql_data/` : Volumen persistente de datos de MySQL
- `nginx/` : Configuración de Nginx

## Primeros pasos

1. **Clona el repositorio**

2. **Configura las variables de entorno**
   - Copia el archivo de ejemplo:
     ```bash
     cp src/.env.example src/.env
     ```
   - Ajusta los valores según tu entorno si es necesario.
   - Las variables de MySQL para Docker Compose están en el archivo `.env` en la raíz y en `mysql/.env`.

3. **Asigna permisos a las carpetas necesarias**
   ```bash
   chmod -R 775 src/storage src/bootstrap/cache
   # Si tienes problemas de permisos, puedes usar 777 solo en desarrollo:
   # chmod -R 777 src/storage src/bootstrap/cache
   ```

4. **Levanta los contenedores**
   ```bash
   docker-compose up -d
   ```

5. **Instala dependencias de Composer**
   ```bash
   docker-compose run --rm composer install
   ```

6. **Genera la clave de la aplicación**
   ```bash
   docker-compose run --rm artisan key:generate
   ```

7. **Accede a la aplicación**
   - Laravel: [http://localhost](http://localhost)
   - phpMyAdmin: [http://localhost:8090](http://localhost:8090)
     - Usuario y contraseña según tu archivo `.env` y `mysql/.env`

## Comandos útiles
- Ejecutar migraciones:
  ```bash
  docker-compose run --rm artisan migrate
  ```
- Ejecutar tests:
  ```bash
  docker-compose run --rm artisan test
  ```
- Instalar paquetes de Composer:
  ```bash
  docker-compose run --rm composer require <paquete>
  ```

## Notas
- No subas archivos `.env` reales ni la carpeta `mysql_data/` al repositorio.
- El entorno está pensado para desarrollo. No uses estas configuraciones en producción.

## Créditos
- Basado en Laravel + Docker Compose
