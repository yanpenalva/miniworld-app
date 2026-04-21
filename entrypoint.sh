#!/usr/bin/env bash
set -e

GROUP="www-data"

DIRECTORIES=(
    "bootstrap/cache"
    "storage"
    "storage/logs"
    "storage/framework/cache"
    "storage/framework/cache/data"
    "storage/framework/sessions"
    "storage/framework/views"
)

install_acl_if_missing() {
    if ! command -v setfacl >/dev/null 2>&1; then
        echo "Instalando suporte a ACL..."
        if command -v apk >/dev/null 2>&1; then
            apk add --no-cache acl
        elif command -v apt-get >/dev/null 2>&1; then
            apt-get update && apt-get install -y acl && rm -rf /var/lib/apt/lists/*
        else
            echo "Gerenciador de pacotes não suportado."
            exit 1
        fi
    fi
}

change_group_and_permissions() {
    local dir="$1"

    if [[ ! -d "$dir" ]]; then
        echo "Diretório $dir não encontrado. Pulando."
        return
    fi

    echo "Aplicando permissões em $dir"
    chgrp -R "$GROUP" "$dir" || true
    chmod -R u+rwX,g+rwX "$dir" || true
    find "$dir" -type d -print0 | xargs -0 chmod g+s || true
    setfacl -R -m u::rwX,g::rwX,o::rX "$dir" || true
    setfacl -dR -m u::rwX,g::rwX,o::rX "$dir" || true
}

echo ">> Criando diretórios necessários..."
mkdir -p \
    /var/www/html/bootstrap/cache \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/log/supervisor

echo ">> Limpando arquivos de hot reload..."
rm -f /var/www/html/public/hot

install_acl_if_missing

cd /var/www/html

for dir in "${DIRECTORIES[@]}"; do
    change_group_and_permissions "$dir"
done

echo ">> Limpando caches antigos..."
php artisan optimize:clear || true

echo ">> Rodando package:discover..."
php artisan package:discover --ansi

if [[ "$APP_ENV" == "production" || "$APP_ENV" == "staging" ]]; then
    echo ">> Cacheando configurações..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi


echo ">> Iniciando container..."
exec "$@"
