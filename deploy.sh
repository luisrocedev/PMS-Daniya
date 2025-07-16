#!/bin/bash

# Script de despliegue para PMS-Daniya
# Uso: ./deploy.sh [produccion|desarrollo]

set -e

ENVIRONMENT=${1:-desarrollo}
PROJECT_DIR="/var/www/pms-daniya"
BACKUP_DIR="/var/backups/pms-daniya"
DATE=$(date +%Y%m%d_%H%M%S)

echo "🚀 Iniciando despliegue de PMS-Daniya en modo: $ENVIRONMENT"

# Crear directorio de backup si no existe
mkdir -p "$BACKUP_DIR"

# Función para hacer backup de la base de datos
backup_database() {
    echo "📦 Creando backup de base de datos..."
    if [ -f .env ]; then
        DB_NAME=$(grep DB_NAME .env | cut -d '=' -f2)
        DB_USER=$(grep DB_USER .env | cut -d '=' -f2)
        DB_PASS=$(grep DB_PASS .env | cut -d '=' -f2)
        
        mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DIR/db_backup_$DATE.sql"
        echo "✅ Backup guardado en: $BACKUP_DIR/db_backup_$DATE.sql"
    else
        echo "⚠️  No se encontró .env, saltando backup de BD"
    fi
}

# Función para actualizar código
update_code() {
    echo "🔄 Actualizando código..."
    git pull origin main
    
    if [ $? -eq 0 ]; then
        echo "✅ Código actualizado correctamente"
    else
        echo "❌ Error al actualizar código"
        exit 1
    fi
}

# Función para instalar dependencias
install_dependencies() {
    echo "📦 Instalando dependencias..."
    if [ "$ENVIRONMENT" = "produccion" ]; then
        composer install --no-dev --optimize-autoloader
    else
        composer install
    fi
    
    if [ $? -eq 0 ]; then
        echo "✅ Dependencias instaladas correctamente"
    else
        echo "❌ Error al instalar dependencias"
        exit 1
    fi
}

# Función para configurar permisos
set_permissions() {
    echo "🔐 Configurando permisos..."
    chown -R www-data:www-data .
    chmod -R 755 .
    chmod -R 777 public/uploads
    chmod 600 .env
    echo "✅ Permisos configurados"
}

# Función para limpiar caché
clear_cache() {
    echo "🧹 Limpiando caché..."
    # Aquí puedes agregar comandos específicos para limpiar caché
    echo "✅ Caché limpiado"
}

# Ejecutar despliegue
cd "$PROJECT_DIR"

if [ "$ENVIRONMENT" = "produccion" ]; then
    backup_database
fi

update_code
install_dependencies
set_permissions
clear_cache

echo "🎉 Despliegue completado exitosamente en modo: $ENVIRONMENT"
echo "🕐 Fecha: $(date)"

# Mostrar estado
echo "📊 Estado del sistema:"
echo "- Espacio en disco: $(df -h . | tail -1 | awk '{print $4}') disponible"
echo "- Última actualización: $(git log -1 --pretty=format:'%h - %s (%an, %ar)')"
