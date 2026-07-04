# Plantilla Reutilizable de Modulo IssabelPBX

Este repositorio puede usarse como base para crear otros modulos de IssabelPBX.

## Que debes cambiar al reutilizarlo

- `recordatorios/module.xml`
  - `rawname`
  - `name`
  - `version`
  - `publisher`
  - `description`
  - `menuitems`
- El nombre del directorio principal del modulo si ya no vas a usar `recordatorios`.
- Los archivos PHP que referencien nombres de archivos, rutas o identificadores del modulo anterior.
- Los sonidos y rutas de audio si el nuevo modulo usa otro flujo.
- Los comandos, tareas y workflow si quieres otro nombre final para el `.tgz`.

## Que puede quedarse sin problema

Mientras este repositorio siga siendo el ejemplo del modulo `recordatorios`, no hace falta borrar todas las apariciones del nombre:

- Los nombres de funciones `recordatorios_*`.
- Los textos visibles del ejemplo.
- Los logs con prefijo `recordatorios:`.
- Los nombres de archivos del modulo actual.
- Las rutas internas del modulo actual mientras sigan apuntando al ejemplo funcional.

Lo importante es entender que esas referencias pertenecen al ejemplo concreto.  
Si se usa este repo para crear otro modulo, entonces si deben reemplazarse por el nuevo nombre y las nuevas rutas.

## Referencias que si son criticas al clonar

Estas son las que mas suelen romper la reutilizacion si se olvidan:

- `recordatorios/module.xml`
- `recordatorios/guimodule.php`
- `recordatorios/install.php`
- `recordatorios/uninstall.php`
- `recordatorios/page.recordatorios.php`
- `recordatorios/agi/recordatorios.agi.php`
- `recordatorios/agi/recordatorios_estado.agi.php`
- `recordatorios/cron/recordatorios_cron.php`
- Rutas como `/var/www/html/admin/modules/recordatorios`
- Rutas como `/var/lib/asterisk/sounds/recordatorios`
- Rutas como `/etc/cron.d/recordatorios`

## Partes pensadas para reutilizar

- `scripts/build-tgz.sh`
- `Taskfile.yml`
- `.github/workflows/build.yml`
- La estructura de `recordatorios/` como ejemplo de modulo completo

## Flujo recomendado

1. Copia este repo.
2. Renombra el modulo y sus identificadores.
3. Ajusta `module.xml`.
4. Revisa `install.php`, `uninstall.php`, `functions.inc.php`, `page.*.php` y `guimodule.php`.
5. Valida con `task lint`.
6. Empaqueta con `task package`.

## Nota

La carpeta `docs/` y las secciones internas sirven como referencia del ejemplo actual y del proceso que seguimos para llegar al modulo funcional.
