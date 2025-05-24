#!/bin/bash

# Script de backup automático para Condy API
# Executa backup diário do banco de dados MySQL

set -e

# Configurações
BACKUP_DIR="/backups"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=7

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
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

# Verificar se as variáveis de ambiente estão definidas
if [ -z "$MYSQL_ROOT_PASSWORD" ] || [ -z "$MYSQL_DATABASE" ]; then
    error "Variáveis de ambiente MYSQL_ROOT_PASSWORD e MYSQL_DATABASE são obrigatórias"
    exit 1
fi

# Criar diretório de backup se não existir
mkdir -p "$BACKUP_DIR"

# Aguardar MySQL estar disponível
log "Aguardando MySQL estar disponível..."
while ! mysqladmin ping -h database -u root -p"$MYSQL_ROOT_PASSWORD" --silent; do
    sleep 1
done

log "Iniciando backup do banco de dados: $MYSQL_DATABASE"

# Realizar backup
BACKUP_FILE="$BACKUP_DIR/condy_backup_$DATE.sql"

if mysqldump -h database \
             -u root \
             -p"$MYSQL_ROOT_PASSWORD" \
             --single-transaction \
             --routines \
             --triggers \
             --events \
             --add-drop-database \
             --databases "$MYSQL_DATABASE" > "$BACKUP_FILE"; then
    
    log "Backup criado com sucesso: $BACKUP_FILE"
    
    # Comprimir backup
    if gzip "$BACKUP_FILE"; then
        log "Backup comprimido: ${BACKUP_FILE}.gz"
        BACKUP_FILE="${BACKUP_FILE}.gz"
    else
        warn "Falha ao comprimir backup"
    fi
    
    # Verificar tamanho do arquivo
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    log "Tamanho do backup: $BACKUP_SIZE"
    
else
    error "Falha ao criar backup"
    exit 1
fi

# Cleanup de backups antigos
log "Removendo backups antigos (mais de $RETENTION_DAYS dias)..."
find "$BACKUP_DIR" -name "condy_backup_*.sql.gz" -type f -mtime +$RETENTION_DAYS -delete

# Listar backups existentes
log "Backups disponíveis:"
ls -lh "$BACKUP_DIR"/condy_backup_*.sql.gz 2>/dev/null || log "Nenhum backup encontrado"

log "Backup concluído com sucesso!"

# Aguardar próximo backup (24 horas)
sleep 86400 