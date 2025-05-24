# Dockerfile para Nginx
FROM nginx:alpine

# Copiar configuração customizada
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Criar diretório para logs
RUN mkdir -p /var/log/nginx

# Expor porta 80
EXPOSE 80

# Comando padrão
CMD ["nginx", "-g", "daemon off;"] 