# Makefile para Condy API
# Facilita comandos Docker e desenvolvimento

.PHONY: help build up down restart logs shell test deploy clean

# Configurações
COMPOSE_FILE = docker-compose.yml
COMPOSE_PROD_FILE = docker-compose.prod.yml
API_CONTAINER = condy-api
NGINX_CONTAINER = condy-nginx

# Cores para output
GREEN = \033[32m
YELLOW = \033[33m
RED = \033[31m
BLUE = \033[34m
NC = \033[0m

help: ## Mostra esta ajuda
	@echo "$(BLUE)Condy API - Comandos disponíveis:$(NC)"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(GREEN)%-20s$(NC) %s\n", $$1, $$2}'

# Desenvolvimento
build: ## Build dos containers de desenvolvimento
	@echo "$(YELLOW)🔨 Fazendo build dos containers...$(NC)"
	docker compose -f $(COMPOSE_FILE) build

up: ## Iniciar containers de desenvolvimento
	@echo "$(YELLOW)🚀 Iniciando containers de desenvolvimento...$(NC)"
	docker compose -f $(COMPOSE_FILE) up -d
	@echo "$(GREEN)✅ API disponível em: http://localhost:8000$(NC)"

down: ## Parar containers
	@echo "$(YELLOW)🛑 Parando containers...$(NC)"
	docker compose -f $(COMPOSE_FILE) down

restart: down up ## Reiniciar containers

logs: ## Ver logs dos containers
	@echo "$(YELLOW)📋 Logs dos containers:$(NC)"
	docker compose -f $(COMPOSE_FILE) logs -f

logs-api: ## Ver logs apenas da API
	@echo "$(YELLOW)📋 Logs da API:$(NC)"
	docker compose -f $(COMPOSE_FILE) logs -f $(API_CONTAINER)

logs-nginx: ## Ver logs apenas do Nginx
	@echo "$(YELLOW)📋 Logs do Nginx:$(NC)"
	docker compose -f $(COMPOSE_FILE) logs -f $(NGINX_CONTAINER)

shell: ## Entrar no container da API
	@echo "$(YELLOW)🐚 Abrindo shell no container da API...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec $(API_CONTAINER) bash

shell-nginx: ## Entrar no container do Nginx
	@echo "$(YELLOW)🐚 Abrindo shell no container do Nginx...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec $(NGINX_CONTAINER) sh

# Testes
test: ## Executar testes
	@echo "$(YELLOW)🧪 Executando testes...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec $(API_CONTAINER) php artisan test

test-coverage: ## Executar testes com coverage
	@echo "$(YELLOW)🧪 Executando testes com coverage...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec $(API_CONTAINER) ./vendor/bin/phpunit --coverage-html=coverage

pint: ## Executar Laravel Pint (code style)
	@echo "$(YELLOW)🎨 Executando Laravel Pint...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec $(API_CONTAINER) ./vendor/bin/pint

stan: ## Executar PHPStan (static analysis)
	@echo "$(YELLOW)🔍 Executando PHPStan...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec $(API_CONTAINER) ./vendor/bin/phpstan analyse

audit: ## Executar security audit
	@echo "$(YELLOW)🔒 Executando security audit...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec $(API_CONTAINER) composer audit

# Laravel Artisan
migrate: ## Executar migrações
	@echo "$(YELLOW)📊 Executando migrações...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec $(API_CONTAINER) php artisan migrate

migrate-fresh: ## Recriar banco e executar migrações
	@echo "$(YELLOW)🗄️ Recriando banco e executando migrações...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec $(API_CONTAINER) php artisan migrate:fresh

seed: ## Executar seeders
	@echo "$(YELLOW)🌱 Executando seeders...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec $(API_CONTAINER) php artisan db:seed

fresh-seed: migrate-fresh seed ## Recriar banco, migrar e popular

tinker: ## Abrir Laravel Tinker
	@echo "$(YELLOW)🔧 Abrindo Laravel Tinker...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec $(API_CONTAINER) php artisan tinker

cache-clear: ## Limpar caches
	@echo "$(YELLOW)🧹 Limpando caches...$(NC)"
	docker compose -f $(COMPOSE_FILE) exec $(API_CONTAINER) php artisan cache:clear
	docker compose -f $(COMPOSE_FILE) exec $(API_CONTAINER) php artisan config:clear
	docker compose -f $(COMPOSE_FILE) exec $(API_CONTAINER) php artisan route:clear
	docker compose -f $(COMPOSE_FILE) exec $(API_CONTAINER) php artisan view:clear

# Produção
prod-build: ## Build para produção
	@echo "$(YELLOW)🔨 Fazendo build para produção...$(NC)"
	docker compose -f $(COMPOSE_PROD_FILE) build

prod-up: ## Iniciar produção
	@echo "$(YELLOW)🚀 Iniciando containers de produção...$(NC)"
	docker compose -f $(COMPOSE_PROD_FILE) up -d
	@echo "$(GREEN)✅ Produção iniciada!$(NC)"

prod-down: ## Parar produção
	@echo "$(YELLOW)🛑 Parando containers de produção...$(NC)"
	docker compose -f $(COMPOSE_PROD_FILE) down

prod-logs: ## Ver logs de produção
	@echo "$(YELLOW)📋 Logs de produção:$(NC)"
	docker compose -f $(COMPOSE_PROD_FILE) logs -f

prod-status: ## Status dos containers de produção
	@echo "$(YELLOW)📊 Status dos containers de produção:$(NC)"
	docker compose -f $(COMPOSE_PROD_FILE) ps

prod-backup: ## Executar backup manual
	@echo "$(YELLOW)💾 Executando backup manual...$(NC)"
	docker compose -f $(COMPOSE_PROD_FILE) exec database /scripts/backup.sh

# Deploy
deploy: ## Deploy manual (emergência)
	@echo "$(RED)🚨 DEPLOY MANUAL - Use apenas em emergências!$(NC)"
	@read -p "Tem certeza? Digite 'sim' para continuar: " confirm && [ "$$confirm" = "sim" ] || exit 1
	@read -p "Branch/tag para deploy: " ref && ./scripts/deploy.sh $$ref

# Limpeza
clean: ## Limpar containers, volumes e imagens não utilizadas
	@echo "$(YELLOW)🧹 Limpando Docker...$(NC)"
	docker compose -f $(COMPOSE_FILE) down -v
	docker system prune -af
	docker volume prune -f

clean-all: ## Limpeza completa (CUIDADO!)
	@echo "$(RED)⚠️  LIMPEZA COMPLETA - Removerá TODOS os dados!$(NC)"
	@read -p "Tem certeza? Digite 'LIMPAR TUDO' para continuar: " confirm && [ "$$confirm" = "LIMPAR TUDO" ] || exit 1
	docker compose -f $(COMPOSE_FILE) down -v
	docker compose -f $(COMPOSE_PROD_FILE) down -v
	docker system prune -af --volumes
	docker volume prune -f

# Instalação
install: ## Primeira instalação
	@echo "$(YELLOW)📦 Primeira instalação do projeto...$(NC)"
	cp .env.example .env
	docker compose -f $(COMPOSE_FILE) build
	docker compose -f $(COMPOSE_FILE) up -d
	sleep 10
	docker compose -f $(COMPOSE_FILE) exec $(API_CONTAINER) php artisan key:generate
	docker compose -f $(COMPOSE_FILE) exec $(API_CONTAINER) php artisan migrate
	@echo "$(GREEN)✅ Instalação concluída!$(NC)"
	@echo "$(GREEN)🌐 API disponível em: http://localhost:8000$(NC)"

# Status
status: ## Status dos containers
	@echo "$(YELLOW)📊 Status dos containers:$(NC)"
	docker compose -f $(COMPOSE_FILE) ps

health: ## Health check
	@echo "$(YELLOW)🏥 Verificando saúde da aplicação...$(NC)"
	@curl -f http://localhost:8000/up && echo "$(GREEN)✅ API está saudável$(NC)" || echo "$(RED)❌ API com problemas$(NC)"

# Frontend (quando implementado)
frontend-up: ## Iniciar frontend
	@echo "$(YELLOW)🖥️  Iniciando frontend...$(NC)"
	docker compose -f $(COMPOSE_FILE) --profile frontend up -d

frontend-down: ## Parar frontend
	@echo "$(YELLOW)🛑 Parando frontend...$(NC)"
	docker compose -f $(COMPOSE_FILE) --profile frontend down

# Informações
info: ## Informações do projeto
	@echo "$(BLUE)📋 Informações do Projeto Condy API$(NC)"
	@echo ""
	@echo "$(GREEN)URLs de Desenvolvimento:$(NC)"
	@echo "  API: http://localhost:8000"
	@echo "  Frontend: http://localhost:3000 (quando implementado)"
	@echo ""
	@echo "$(GREEN)Comandos Úteis:$(NC)"
	@echo "  make install    - Primeira instalação"
	@echo "  make up         - Iniciar desenvolvimento"
	@echo "  make test       - Executar testes"
	@echo "  make logs       - Ver logs"
	@echo "  make help       - Todos os comandos"
	@echo ""
	@echo "$(GREEN)Documentação:$(NC)"
	@echo "  CI/CD: ./CI-CD-README.md"
	@echo "  API: Postman Collection disponível" 