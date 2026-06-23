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

: "${PROD_BACKUP_LOCAL_DIR:=/tmp}"
: "${PROD_BACKUP_PREFIX:=day-vgr}"
: "${LOCAL_DB_HOST:=127.0.0.1}"
: "${LOCAL_DB_PORT:=3306}"
: "${LOCAL_DB_USER:?Variable LOCAL_DB_USER non définie dans backup.conf}"
: "${LOCAL_DB_PASSWORD:?Variable LOCAL_DB_PASSWORD non définie dans backup.conf}"
: "${LOCAL_DB_NAME:?Variable LOCAL_DB_NAME non définie dans backup.conf}"

# Fichier à importer : argument ou dernier téléchargé (hier)
if [[ $# -ge 1 ]]; then
    DUMP_FILE="$1"
else
    YESTERDAY=$(date -d "yesterday" +%Y%m%d)
    DUMP_FILE="${PROD_BACKUP_LOCAL_DIR}/${YESTERDAY}-${PROD_BACKUP_PREFIX}.sql.gz"
fi

if [[ ! -f "$DUMP_FILE" ]]; then
    echo "Erreur : fichier introuvable : $DUMP_FILE"
    echo "Lancer d'abord scripts/download-prod-backup.sh ou passer le chemin en argument."
    exit 1
fi

echo "Fichier : $DUMP_FILE"
echo "Cible   : $LOCAL_DB_NAME @ $LOCAL_DB_HOST:$LOCAL_DB_PORT"
echo ""
read -r -p "Cela va écraser la base locale '$LOCAL_DB_NAME'. Continuer ? [o/N] " CONFIRM
if [[ "$CONFIRM" != "o" && "$CONFIRM" != "O" ]]; then
    echo "Import annulé."
    exit 0
fi

echo "Import en cours..."

if command -v pv &>/dev/null; then
    pv "$DUMP_FILE" | gunzip | mysql \
        -h "$LOCAL_DB_HOST" \
        -P "$LOCAL_DB_PORT" \
        -u "$LOCAL_DB_USER" \
        -p"$LOCAL_DB_PASSWORD" \
        "$LOCAL_DB_NAME"
else
    gunzip -c "$DUMP_FILE" | mysql \
        -h "$LOCAL_DB_HOST" \
        -P "$LOCAL_DB_PORT" \
        -u "$LOCAL_DB_USER" \
        -p"$LOCAL_DB_PASSWORD" \
        "$LOCAL_DB_NAME"
fi

echo "Import terminé."
