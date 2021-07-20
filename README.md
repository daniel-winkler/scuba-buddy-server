# Scuba Buddy, tu portal de búsqueda de centros de buceo alrededor del mundo

Este proyecto ha sido creado con [Symfony](https://symfony.com/download).

## Instrucciones de instalación

Paso 1: Asegurarse que está instalado XAMPP/LAMP/MAMP, Symfony y el gestor de paquetes [composer](https://getcomposer.org/download/)

Paso 2: En tu consola git clonar el proyecto con el comando `git clone https://github.com/daniel-winkler/scuba-buddy-server.git`

Paso 3: En la carpeta raíz del proyecto ejecutar `composer update` y despues `composer install`

Paso 4: Dentro de la carpeta raíz crear un archivo .env.local y añadir las siguientes lineas de código 
```
DATABASE_URL="mysql://USUARIO:CONTRASEÑA@127.0.0.1:3306/scuba_buddy?serverVersion=5.7"
MAILER_DSN=gmail://USUARIO:CONTRASEÑA@default
```
En caso de error, eliminar `?serverVersion=5.7` de la primera linea

Paso 5: Ejecutar comando `symfony console doctrine:database:create`

Paso 6 : Ejecutar comando `symfony console doctrine:migrations:migrate`

Paso 7: En la carpeta resources, abrir los archivos sql y ejecutar los comandos en la base de datos para poblarla.

Paso 8: Descargar [archivo zip](https://app.box.com/s/hijvzj82kbbunv3yytcc8bz5e2d45lm0) y meter la carpeta images en la carpeta public del proyecto.

Paso 9: Ejecutar comando `symfony console lexik:jwt:generate-keypair`. En caso de error descargar el instalador [Win64 OpenSSL v1.1.1k Light](https://slproweb.com/products/Win32OpenSSL.html)

Paso 10: Ejecutar comando `symfony serve -d`
