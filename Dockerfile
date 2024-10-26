# Usar a imagem oficial do PHP com Apache
FROM php:8.0-apache

# Copiar o código da aplicação para o diretório padrão do Apache
COPY . /var/www/html/

# Expor a porta 80
EXPOSE 80