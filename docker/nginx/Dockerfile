FROM alpine:3.22

RUN apk add --no-cache nginx \
    && mkdir -p /run/nginx \
    && chown -R nginx:nginx /var/lib/nginx /run/nginx

COPY nginx.conf /etc/nginx/http.d/default.conf

WORKDIR /var/www/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]