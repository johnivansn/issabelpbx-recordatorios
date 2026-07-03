# Instalacion

Este modulo se instala como paquete de IssabelPBX (`.tgz`).

## Antes de instalar

- Verifica que la carpeta raiz del paquete sea `recordatorios/`.
- Verifica que `module.xml` mantenga `rawname=recordatorios`.
- Instala el archivo desde el administrador de modulos de IssabelPBX.

## Que hace el instalador

- Crea la tabla de base de datos.
- Instala los archivos AGI.
- Prepara el cron.
- Genera los sonidos del menu.
- Asigna los permisos requeridos.
- Registra los hooks de dialplan necesarios.

## Rutas de instalacion

- Codigo del modulo: `/var/www/html/admin/modules/recordatorios/`
- Archivos AGI: `/var/lib/asterisk/agi-bin/`
- Sonidos: `/var/lib/asterisk/sounds/recordatorios/`
- Archivo cron: `/etc/cron.d/recordatorios`

## Despues de instalar

- Abre la pagina del modulo en PBX.
- Confirma que el modulo aparece en el menu.
- Verifica que exista la carpeta de sonidos.
- Prueba una llamada de recordatorio con una hora cercana.
- Revisa el log de Asterisk si algo falla.

## Notas

- El audio generado se crea al instalar o regenerar.
- Si cambias el nombre del paquete, conserva intacta la carpeta interna.
