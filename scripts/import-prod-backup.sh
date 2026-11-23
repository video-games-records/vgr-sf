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

: "${PROD_BACKUP_LOCAL_DIR:=$HOME/backups-vps/vgr}"
: "${PROD_BACKUP_PREFIX:=day-vgr}"
: "${LOCAL_DB_HOST:=127.0.0.1}"
: "${LOCAL_DB_PORT:=3306}"
: "${LOCAL_DB_USER:?Variable LOCAL_DB_USER non définie dans backup.conf}"
: "${LOCAL_DB_PASSWORD:?Variable LOCAL_DB_PASSWORD non définie dans backup.conf}"
: "${LOCAL_DB_NAME:?Variable LOCAL_DB_NAME non définie dans backup.conf}"

# Fichier à importer : argument, sinon le plus récent trouvé dans le répertoire
# des backups rapatriés par le cron nocturne (rsync depuis le VPS, chiffrés GPG).
if [[ $# -ge 1 ]]; then
    DUMP_FILE="$1"
else
    DUMP_FILE="$(ls -t "${PROD_BACKUP_LOCAL_DIR}/"*"-${PROD_BACKUP_PREFIX}.sql."* 2>/dev/null | head -1 || true)"
fi

if [[ -z "${DUMP_FILE:-}" || ! -f "$DUMP_FILE" ]]; then
    echo "Erreur : aucun fichier de backup trouvé dans ${PROD_BACKUP_LOCAL_DIR}"
    echo "Passer le chemin du dump en argument, ou vérifier que le pull nocturne (cron) a bien tourné."
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

read_source() {
    if command -v pv &>/dev/null; then
        pv "$DUMP_FILE"
    else
        cat "$DUMP_FILE"
    fi
}

decompress() {
    case "$DUMP_FILE" in
        *.gpg)
            gpg --quiet --decrypt
            ;;
        *.gz)
            gunzip -c
            ;;
        *.sql)
            cat
            ;;
        *)
            echo "Erreur : extension non reconnue pour $DUMP_FILE (attendu .gpg, .gz ou .sql)" >&2
            exit 1
            ;;
    esac
}

echo "Import en cours..."

read_source | decompress | mysql \
    -h "$LOCAL_DB_HOST" \
    -P "$LOCAL_DB_PORT" \
    -u "$LOCAL_DB_USER" \
    -p"$LOCAL_DB_PASSWORD" \
    "$LOCAL_DB_NAME"

echo "Import terminé."
