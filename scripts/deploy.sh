#!/bin/bash

# Script de deploy manual para Condy API
# Use apenas para emergências ou deploy inicial

set -e

# Configurações
DEPLOY_DIR="/opt/condy"
BACKUP_DIR="/opt/condy/backups"
COMPOSE_FILE="docker-compose.prod.yml"

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}"
}

warn() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING: $1${NC}"
}

info() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] INFO: $1${NC}"
}

# Verificar se está rodando como root ou com sudo
if [ "$EUID" -ne 0 ]; then
    error "Este script deve ser executado como root ou com sudo"
    exit 1
fi

# Verificar se Docker está instalado
if ! command -v docker &> /dev/null; then
    error "Docker não está instalado"
    exit 1
fi

# Verificar se Docker Compose está instalado
if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    error "Docker Compose não está instalado"
    exit 1
fi

# Função para rollback
rollback() {
    warn "Iniciando rollback..."
    
    if [ -f "$DEPLOY_DIR/.env.backup.previous" ]; then
        cp "$DEPLOY_DIR/.env.backup.previous" "$DEPLOY_DIR/.env"
        log "Arquivo .env restaurado"
    fi
    
    cd "$DEPLOY_DIR"
    docker compose -f "$COMPOSE_FILE" down || true
    docker compose -f "$COMPOSE_FILE" up -d || true
    
    warn "Rollback concluído"
}

# Trap para rollback em caso de erro
trap rollback ERR

log "🚀 Iniciando deploy manual da Condy API"

# Verificar argumentos
if [ $# -eq 0 ]; then
    error "Use: $0 <git-tag-or-branch>"
    error "Exemplo: $0 v1.0.0 ou $0 main"
    exit 1
fi

GIT_REF="$1"
info "Fazendo deploy da referência: $GIT_REF"

# Criar diretórios necessários
mkdir -p "$DEPLOY_DIR"
mkdir -p "$BACKUP_DIR"

cd "$DEPLOY_DIR"

# Fazer backup do .env atual
if [ -f ".env" ]; then
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    cp .env .env.backup.previous
    log "Backup do .env criado"
fi

# Baixar código fonte
if [ -d ".git" ]; then
    log "Atualizando repositório existente..."
    git fetch --all
    git checkout "$GIT_REF"
    git pull origin "$GIT_REF" || git reset --hard "$GIT_REF"
else
    log "Clonando repositório..."
    git clone https://github.com/seu-usuario/condy-backend.git .
    git checkout "$GIT_REF"
fi

# Verificar se arquivo de produção existe
if [ ! -f "$COMPOSE_FILE" ]; then
    error "Arquivo $COMPOSE_FILE não encontrado"
    exit 1
fi

# Parar containers atuais
log "Parando containers atuais..."
docker compose -f "$COMPOSE_FILE" down || true

# Fazer backup do banco de dados antes do deploy
if docker compose -f "$COMPOSE_FILE" ps | grep -q database; then
    log "Fazendo backup do banco de dados..."
    docker compose -f "$COMPOSE_FILE" exec -T database mysqldump \
        -u root -p"$MYSQL_ROOT_PASSWORD" \
        --single-transaction \
        --routines \
        --triggers \
        "$MYSQL_DATABASE" > "$BACKUP_DIR/pre_deploy_backup_$(date +%Y%m%d_%H%M%S).sql" || warn "Falha no backup do banco"
fi

# Build das novas imagens
log "Fazendo build das imagens..."
docker compose -f "$COMPOSE_FILE" build --no-cache

# Verificar se .env existe
if [ ! -f ".env" ]; then
    warn "Arquivo .env não encontrado, criando a partir do .env.example"
    cp .env.example .env
    
    echo ""
    warn "IMPORTANTE: Configure as variáveis de ambiente no arquivo .env antes de continuar"
    read -p "Pressione Enter para continuar após configurar o .env..."
fi

# Iniciar containers
log "Iniciando containers..."
docker compose -f "$COMPOSE_FILE" up -d

# Aguardar containers ficarem prontos
log "Aguardando containers ficarem prontos..."
sleep 30

# Verificar saúde dos containers
log "Verificando saúde dos containers..."
docker compose -f "$COMPOSE_FILE" ps

# Testar API
log "Testando API..."
if curl -f http://localhost/api/auth/check-email >/dev/null 2>&1; then
    log "✅ API respondendo corretamente"
else
    error "❌ API não está respondendo"
    rollback
    exit 1
fi

# Cleanup de imagens antigas
log "Limpando imagens Docker antigas..."
docker image prune -af

# Limpar trap
trap - ERR

log "✅ Deploy concluído com sucesso!"
info "API disponível em: http://$(hostname -I | awk '{print $1}')"
info "Logs: docker compose -f $COMPOSE_FILE logs -f"
info "Status: docker compose -f $COMPOSE_FILE ps"

# Mostrar informações finais
echo ""
info "📊 Status dos containers:"
docker compose -f "$COMPOSE_FILE" ps

echo ""
info "💾 Backups disponíveis:"
ls -lht "$BACKUP_DIR"/*.sql 2>/dev/null | head -5 || info "Nenhum backup encontrado" 