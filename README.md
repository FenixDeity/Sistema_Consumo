# Sistema Consumo (Laravel 12 · MVC)

Aplicación web y móvil-friendly para controlar y analizar el consumo de energía
eléctrica de una casa. Estructura MVC clásica de Laravel, tema oscuro elegante y
título **Sistema** (verde) **Consumo** (naranja-ámbar).

## Requisitos

- PHP 8.2 o superior (extensiones: `pdo_sqlite` o `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`)
- Composer 2

## Instalación local

### Windows (XAMPP) — la forma facil

1. Asegurate de que PHP este en el PATH (o usa la consola de XAMPP).
   Para agregarlo temporalmente en CMD:
   ```bat
   set PATH=C:\xampp\php;%PATH%
   ```
2. Doble clic en **`instalar.bat`** (dentro de la carpeta `sistema-consumo`).
   Ese script instala dependencias, crea el `.env`, genera la llave, crea la base
   de datos y arranca el servidor.

Si prefieres hacerlo a mano, en CMD (ojo: en Windows es `copy`, no `cp`):

```bat
cd sistema-consumo
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### macOS / Linux

```bash
cd sistema-consumo
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Abre http://localhost:8000

Usuario de ejemplo (creado por el seeder):

- correo: `demo@sistemaconsumo.mx`
- contraseña: `Demo1234!`

## Solución de problemas

- **`Your requirements could not be resolved ... affected by security advisories`**
  Ya viene resuelto (Laravel 12 + `policy.advisories.block: false` en `composer.json`).
  Si tu Composer es viejo, actualizalo con `composer self-update` y vuelve a intentar.
- **`Failed to open stream: vendor/autoload.php`**
  Significa que `composer install` no termino bien. Corre `composer install` otra vez
  y revisa que exista la carpeta `vendor`. Los comandos `php artisan ...` solo
  funcionan despues de esa instalacion.
- **`cp no se reconoce como un comando`** → usa `copy .env.example .env`.
- **`could not find driver`** → en `C:\xampp\php\php.ini` quita el `;` de
  `extension=pdo_sqlite` (o `extension=pdo_mysql`) y reinicia la consola.

### Usar MySQL en lugar de SQLite

En `.env` cambia `DB_CONNECTION=mysql` y ajusta host, base, usuario y contraseña;
después ejecuta `php artisan migrate --seed`.

### Tarifa eléctrica

En `.env`: `ENERGY_RATE=2.85` (pesos por kWh). Se usa en todos los cálculos de costo.

## Estructura MVC

```
app/
  Models/            Modelos Eloquent (User, Device, UsageLog, Group, GroupMember, PowerOutage)
  Http/Controllers/  Controladores (Home, Auth, Dashboard, Device, Consumption, Report, Group)
  Services/          EnergyService: reglas de negocio y cálculos de consumo
resources/views/     Vistas Blade (capa View)
  layouts/app.blade.php
  auth/  dashboard/  devices/  consumo/  reports/  groups/
routes/web.php       Rutas
database/migrations/ Esquema de base de datos
public/              Document root (index.php, css, favicon)
config/              Configuración (energy.php contiene la tarifa)
```

## Funcionalidades

- **Autenticación**: registro e inicio de sesión con validación de caracteres
  (nombre sólo letras; contraseña con mayúscula, minúscula, número y símbolo).
- **Dashboard**: resumen detallado del consumo de **hoy** y del **mes**
  (consumo en uso, consumo fantasma, horas, costo estimado y ranking de aparatos).
- **Dispositivos**: registrar y eliminar (borrado suave, el historial se conserva).
  Si no se conocen los watts, se calculan con volts × amperes.
- **Consumo**: registro diario por **tiempo de uso** (desde 1 minuto, sin límite) o por
  **ciclos** (por ejemplo 5 ciclos de 180 minutos). Es obligatorio uno de los dos modos.
- **Consumo fantasma**: basta marcar "queda enchufado todo el día"; el sistema
  estima automáticamente los watts en espera según el tipo de aparato.
- **Apagones**: interruptor en tiempo real (se calcula la duración al desactivarlo)
  o registro manual con duración estimada. Las horas sin luz se descuentan del
  consumo fantasma.
- **Reportes**: reporte mensual por dispositivo (incluye eliminados), exportación
  CSV y ventana emergente con gráficas de consumo por día y por dispositivo.
- **Compartir**: familias/grupos con código de 6 caracteres; los integrantes ven
  los dispositivos y el consumo compartido de la casa.

## Versión móvil (APK)

El diseño es responsivo (`min-h-dvh`, navegación adaptable), por lo que funciona
directamente en un convertidor web → APK apuntando a la URL donde publiques el proyecto.
