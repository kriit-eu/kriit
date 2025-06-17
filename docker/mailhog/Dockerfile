FROM alpine:3.22
RUN apk add --no-cache ca-certificates curl \
 && curl -L -o /usr/local/bin/mailhog \
      https://github.com/mailhog/MailHog/releases/download/v1.0.1/MailHog_linux_amd64 \
 && chmod +x /usr/local/bin/mailhog
EXPOSE 1025 8025
ENTRYPOINT ["mailhog"]
