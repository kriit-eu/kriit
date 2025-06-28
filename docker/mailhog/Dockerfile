FROM golang:1.23-alpine AS builder
RUN apk add --no-cache git
RUN git clone https://github.com/mailhog/MailHog.git /go/src/github.com/mailhog/MailHog
WORKDIR /go/src/github.com/mailhog/MailHog
RUN rm -rf vendor && go mod init github.com/mailhog/MailHog && go mod tidy && CGO_ENABLED=0 go build -mod=mod -o /mailhog .

FROM alpine:3.22
RUN apk add --no-cache ca-certificates
COPY --from=builder /mailhog /usr/local/bin/mailhog
EXPOSE 1025 8025
ENTRYPOINT ["mailhog"]
