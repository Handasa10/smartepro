#!/bin/sh
# Writes the msmtp config (used by PHP mail()) from environment variables,
# then starts Apache. Set SMTP_PASSWORD in Dokploy -> Environment.
set -e

if [ -n "$SMTP_PASSWORD" ]; then
    umask 027
    # Quote the password for msmtp (escape backslashes and double quotes)
    SMTP_PASSWORD_QUOTED=$(printf "%s" "$SMTP_PASSWORD" | sed 's/[\\"]/\\&/g')
    cat > /etc/msmtprc <<CONF
defaults
auth           on
tls            on
tls_starttls   off
tls_trust_file /etc/ssl/certs/ca-certificates.crt
syslog         off
logfile        /proc/self/fd/2

account        default
host           ${SMTP_HOST:-smtp.hostinger.com}
port           ${SMTP_PORT:-465}
from           ${SMTP_USER:-admin@smartepro.com}
user           ${SMTP_USER:-admin@smartepro.com}
password       "${SMTP_PASSWORD_QUOTED}"
CONF
    chown root:www-data /etc/msmtprc
    chmod 640 /etc/msmtprc
else
    echo "mail-entrypoint: SMTP_PASSWORD is not set, contact form emails will fail" >&2
fi

exec docker-php-entrypoint "$@"
