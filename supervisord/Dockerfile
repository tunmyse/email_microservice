FROM tunmyse/email_service

USER root

RUN apk update && apk add --no-cache supervisor

COPY supervisord.conf /etc/supervisord.conf

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]