#!/bin/bash
# Ejecuta migraciones dentro del contenedor

docker compose run --rm artisan migrate
