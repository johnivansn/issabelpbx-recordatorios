# Recordatorios Diarios (*5432)

Modulo de IssabelPBX 5 para crear, listar, editar, borrar y reproducir recordatorios diarios con llamada automatica 15 minutos antes de la hora configurada.

## Que hace

- Crea recordatorios para el dia siguiente.
- Permite subir o grabar audio.
- Reproduce audio desde la interfaz web de Issabel.
- Genera llamadas automaticas con `cron`.
- Ofrece un flujo telefonico mediante `*5432`.

## Como reutilizar este repo

Este proyecto puede servir como base para otros modulos de IssabelPBX.

Lo importante al reutilizarlo es cambiar:

- `recordatorios/module.xml` con el nuevo `rawname`, nombre visible y version.
- El nombre del directorio del modulo si el ejemplo deja de llamarse `recordatorios`.
- Las rutas internas de AGI, sonidos y pagina web que dependan del nombre del modulo.
- La salida del empaquetado si quieres otro nombre final en el `.tgz`.

El script `scripts/build-tgz.sh` ya permite cambiar `MODULE_DIR` y `OUTPUT_DIR` por variables de entorno, asi que se puede reutilizar sin tocarlo mucho.

Mientras este repo siga siendo el ejemplo del modulo actual, es normal que aparezcan muchas referencias a `recordatorios` en codigo, logs y rutas.  
Eso no es un problema por si mismo: solo debes reemplazar esas referencias cuando clonas el repo para construir otro modulo distinto.

## Estructura pensada para plantilla

La estructura del repo esta organizada para que puedas copiarla como base de otros modulos:

- `recordatorios/`: ejemplo completo de modulo funcional.
- `scripts/`: helpers de empaquetado reutilizables.
- `Taskfile.yml`: tareas simples para validar y empaquetar.
- `docs/`: documentacion interna del ejemplo y del proceso.
- `README.md`, `INSTALL.md`, `USAGE.md`, `ARCHITECTURE.md`: docs publicas que puedes adaptar para otro modulo.

## Documentacion publica

- [Arquitectura](ARCHITECTURE.md)
- [Instalacion](INSTALL.md)
- [Uso](USAGE.md)
- [Changelog](CHANGELOG.md)
- [Scripts](scripts/README.md)

## Versionado y releases

- La version oficial del modulo vive en `recordatorios/module.xml`.
- Los tags de Git usan el formato `vX.Y.Z`.
- El workflow de build valida la sintaxis PHP y revisa `module.xml` en `main` y en `pull_request`.
- El `.tgz` se genera localmente con `scripts/build-tgz.sh` cuando hace falta probar el modulo en Issabel.
- No publicamos releases automáticos desde GitHub; el paquete final se genera y se instala según el flujo de trabajo del proyecto.
- Si cambias el codigo, actualiza primero `module.xml` y prueba el build en `main` o en una PR.
- Si prefieres usar una interfaz de tareas, ejecuta `task lint` o `task package` con `Taskfile.yml`.

## Estructura del repositorio

- `recordatorios/`: codigo fuente del modulo para IssabelPBX.
- `docs/`: documentacion tecnica privada.
- `versiones/`: entregables o paquetes generados.
- `scripts/`: utilidades para empaquetar y probar.
- `Taskfile.yml`: tareas rapidas para validar y empaquetar.

## Requisitos

- IssabelPBX 5
- Asterisk 18+
- PHP 7.4
- MariaDB
- `cron`

## Estado actual

- Version actual del modulo: `1.0.1`
- Base empaquetada inicial: `1.0.0`
