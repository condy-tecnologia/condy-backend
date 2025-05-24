#!/bin/bash

# Script de inicialização para API Laravel
set -e

echo "🚀 Iniciando API Condy..."

# Aguardar banco de dados se necessário
echo "⏳ Aguardando banco de dados..."
sleep 5

# Verificar se .env existe
if [ ! -f .env ]; then
    echo "📄 Copiando .env.example para .env"
    cp .env.example .env
fi

# Gerar chave da aplicação se não existir
if grep -q "APP_KEY=$" .env; then
    echo "🔑 Gerando chave da aplicação..."
    php artisan key:generate --force
fi

# Rodar migrações
echo "📊 Executando migrações..."
php artisan migrate --force

# Limpar e cachear configurações
echo "🧹 Limpando caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "💾 Criando caches otimizados..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Criar link simbólico para storage (se não existir)
if [ ! -L public/storage ]; then
    echo "🔗 Criando link simbólico para storage..."
    php artisan storage:link
fi

echo "✅ API Condy iniciada com sucesso!"

# Iniciar PHP-FPM
exec php-fpm 