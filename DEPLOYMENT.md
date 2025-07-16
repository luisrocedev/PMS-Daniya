# Instrucciones de despliegue para PMS-Daniya

## Requisitos del servidor

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache/Nginx
- Composer
- SSL/TLS certificado

## Pasos de instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/luisrocedev/PMS-Daniya.git
cd PMS-Daniya
```

### 2. Instalar dependencias

```bash
composer install --no-dev --optimize-autoloader
```

### 3. Configurar variables de entorno

```bash
cp .env.example .env
# Editar .env con los datos reales de producción
```

### 4. Configurar base de datos

```bash
mysql -u usuario -p nombre_db < pms_daniya_denia.sql
```

### 5. Configurar permisos

```bash
chown -R www-data:www-data .
chmod -R 755 .
chmod -R 777 public/uploads
```

### 6. Configurar Apache/Nginx

- DocumentRoot debe apuntar a la carpeta `public/`
- Configurar SSL
- Configurar rewrite rules

## Configuración de Apache (.htaccess)

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Seguridad
<Files ".env">
    Order allow,deny
    Deny from all
</Files>
```

## Configuración de Nginx

```nginx
server {
    listen 443 ssl;
    server_name pms.tudominio.com;
    root /var/www/pms-daniya/public;
    index index.php;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.env {
        deny all;
    }
}
```

## Backup y mantenimiento

```bash
# Backup de base de datos
mysqldump -u usuario -p nombre_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Actualizar código
git pull origin main
composer install --no-dev --optimize-autoloader
```
