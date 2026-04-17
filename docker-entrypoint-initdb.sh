#!/usr/bin/env bash

docker exec -i miniworld-app-db psql -U postgres -tc "SELECT 1 FROM pg_database WHERE datname = 'miniworld-app_test';" | grep -q 1 && {
    echo "Banco miniworld-app_test já existe."
    exit 0
}

docker exec -i miniworld-app-db psql -U postgres -c "CREATE DATABASE miniworld-app_test;"
echo "Banco miniworld-app_test criado."
