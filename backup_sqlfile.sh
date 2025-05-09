#!/bin/bash
# Script para copiar el archivo pms_daniya_denia.sql a la carpeta backups con fecha

SRC="$(dirname "$0")/pms_daniya_denia.sql"
DEST="$(dirname "$0")/backups/pms_daniya_denia_$(date +%Y%m%d_%H%M%S).sql"

cp "$SRC" "$DEST"
echo "Backup creado en: $DEST"