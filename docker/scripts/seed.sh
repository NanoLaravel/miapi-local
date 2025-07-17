#!/bin/bash
# Ejecuta seeders dentro del contenedor

docker compose run --rm artisan db:seed
