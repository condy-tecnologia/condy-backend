# Dockerfile para Frontend Vue.js
# Estágio de build
FROM node:18-alpine as build-stage

WORKDIR /app

# Copiar package.json e package-lock.json (quando existirem)
COPY frontend/package*.json ./

# Instalar dependências
RUN npm ci --only=production

# Copiar código fonte
COPY frontend/ .

# Build da aplicação
RUN npm run build

# Estágio de produção
FROM nginx:alpine as production-stage

# Copiar arquivos buildados
COPY --from=build-stage /app/dist /usr/share/nginx/html

# Copiar configuração customizada do Nginx para SPA
COPY docker/frontend/nginx.conf /etc/nginx/conf.d/default.conf

# Expor porta 80
EXPOSE 80

# Comando padrão
CMD ["nginx", "-g", "daemon off;"] 