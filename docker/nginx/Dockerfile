FROM nginx:1.27-alpine

# Копируем конфигурационный файл Nginx
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Копируем публичную статику Laravel (включая собранные ассеты)
COPY public /var/www/html/public

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
