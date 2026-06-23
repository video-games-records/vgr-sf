#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONF_FILE="$SCRIPT_DIR/backup.conf"

if [[ ! -f "$CONF_FILE" ]]; then
    echo "Erreur : $CONF_FILE introuvable."
    echo "Copier scripts/backup.conf.dist en scripts/backup.conf et renseigner les valeurs."
    exit 1
fi

# shellcheck source=backup.conf.dist
source "$CONF_FILE"

: "${PROD_SSH_USER:?Variable PROD_SSH_USER non définie dans backup.conf}"
: "${PROD_SSH_HOST:?Variable PROD_SSH_HOST non définie dans backup.conf}"
: "${PROD_SSH_PORT:=22}"
: "${PROD_BACKUP_REMOTE_DIR:?Variable PROD_BACKUP_REMOTE_DIR non définie dans backup.conf}"
: "${PROD_BACKUP_PREFIX:=day-vgr}"
: "${PROD_BACKUP_LOCAL_DIR:=/tmp}"

# Le dump de la veille
YESTERDAY=$(date -d "yesterday" +%Y%m%d)
FILENAME="${YESTERDAY}-${PROD_BACKUP_PREFIX}.sql.gz"
REMOTE_PATH="${PROD_BACKUP_REMOTE_DIR}/${FILENAME}"
LOCAL_PATH="${PROD_BACKUP_LOCAL_DIR}/${FILENAME}"

echo "Téléchargement de $FILENAME depuis $PROD_SSH_HOST..."

scp -P "$PROD_SSH_PORT" \
    "${PROD_SSH_USER}@${PROD_SSH_HOST}:${REMOTE_PATH}" \
    "$LOCAL_PATH"

echo "Fichier téléchargé : $LOCAL_PATH"
