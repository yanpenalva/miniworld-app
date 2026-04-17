#!/usr/bin/env bash

DB_NAME="miniworld-app_test"

docker exec -i miniworld-app-db psql -U postgres -tc \
  "SELECT 1 FROM pg_database WHERE datname = '${DB_NAME}';" | grep -q 1 && {
    echo "Banco ${DB_NAME} já existe."
    exit 0
}

docker exec -i miniworld-app-db psql -U postgres \
  -c "CREATE DATABASE \"${DB_NAME}\";"

echo "Banco ${DB_NAME} criado."
