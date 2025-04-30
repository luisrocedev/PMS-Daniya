#!/bin/bash
# Script de copia de seguridad para la base de datos PMS-Daniya
# Lee credenciales desde .env y ejecuta backup solo si han pasado 3 días desde el último backup

ENV_FILE=".env"
DESTINO="backups"

# Leer variables del archivo .env
if [ -f "$ENV_FILE" ]; then
  export $(grep -E 'DB_USER|DB_PASS|DB_NAME' "$ENV_FILE" | sed 's/\r$//')
else
  echo "No se encontró el archivo .env. Abortando."
  exit 1
fi

# Comprobar si existe la carpeta de backups
mkdir -p $DESTINO

# Buscar el último backup
ULTIMO_BACKUP=$(ls -t $DESTINO/pms_daniya_backup_*.sql 2>/dev/null | head -n 1)

EJECUTAR_BACKUP=true
if [ -n "$ULTIMO_BACKUP" ]; then
  FECHA_ULTIMO=$(stat -f %m "$ULTIMO_BACKUP")
  FECHA_ACTUAL=$(date +%s)
  DIAS=$(( (FECHA_ACTUAL - FECHA_ULTIMO) / 86400 ))
  if [ $DIAS -lt 3 ]; then
    echo "La última copia de seguridad fue hace $DIAS días. No es necesario crear una nueva."
    EJECUTAR_BACKUP=false
  fi
fi

if [ "$EJECUTAR_BACKUP" = true ]; then
  FECHA=$(date +"%Y%m%d_%H%M%S")
  ARCHIVO="$DESTINO/pms_daniya_backup_$FECHA.sql"
  mysqldump -u$DB_USER -p$DB_PASS $DB_NAME > $ARCHIVO
  if [ $? -eq 0 ]; then
    echo "Copia de seguridad realizada: $ARCHIVO"
  else
    echo "Error al realizar la copia de seguridad."
  fi
fi
