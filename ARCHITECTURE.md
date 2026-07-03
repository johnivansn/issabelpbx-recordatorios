# Arquitectura

Este modulo sigue la estructura estandar de IssabelPBX y mantiene el runtime simple.

## Piezas principales

- `recordatorios/module.xml`: descriptor del modulo.
- `recordatorios/install.php`: instalacion, permisos, cron y generacion de sonidos.
- `recordatorios/uninstall.php`: limpieza del modulo.
- `recordatorios/functions.inc.php`: logica compartida, helpers de BD, audio y CRUD.
- `recordatorios/page.recordatorios.php`: controlador web.
- `recordatorios/guimodule.php`: integracion con la GUI de Issabel.
- `recordatorios/agi/`: scripts del flujo telefonico.
- `recordatorios/cron/`: programador automatico de recordatorios.
- `recordatorios/ui/`: componentes separados de la interfaz web.

## Flujo de datos

1. El usuario crea o edita un recordatorio desde la web.
2. El recordatorio se guarda en MariaDB con fecha, hora, destino, audio y estado.
3. `cron` revisa cada minuto los recordatorios pendientes.
4. Asterisk recibe un archivo `.call` generado 15 minutos antes de la hora configurada.
5. El script AGI ejecuta el flujo telefonico y reproduce el audio grabado.
6. El recordatorio queda marcado con el estado final.

## Rutas en disco

- Codigo fuente del modulo: `recordatorios/`
- AGI en ejecucion: `/var/lib/asterisk/agi-bin/`
- Sonidos generados del menu: `/var/lib/asterisk/sounds/recordatorios/menu/`
- Audio de recordatorios: `/var/lib/asterisk/sounds/recordatorios/`
- Entrada cron: `/etc/cron.d/recordatorios`

## Objetivos de diseño

- Mantener el modulo simple.
- Usar patrones nativos de IssabelPBX.
- Evitar servicios en segundo plano innecesarios.
- Mantener limpia la instalacion y la desinstalacion.
- Hacer reproducible la generacion de audio.

## Fuera de alcance

- No hay capa de asistente IA.
- No hay servicio personalizado permanente.
- No hay motor de colas adicional.
- No hay artefactos generados dentro del arbol fuente.
