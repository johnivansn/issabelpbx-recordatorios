# Recordatorios Diarios (*5432)

Modulo de IssabelPBX 5 para crear, listar, editar, borrar y reproducir recordatorios diarios con llamada automatica 15 minutos antes de la hora configurada.

## Que hace

- Crea recordatorios para el dia siguiente.
- Permite subir o grabar audio.
- Reproduce audio desde la interfaz web de Issabel.
- Genera llamadas automaticas con `cron`.
- Ofrece un flujo telefonico mediante `*5432`.

## Documentacion publica

- [Arquitectura](ARCHITECTURE.md)
- [Instalacion](INSTALL.md)
- [Uso](USAGE.md)
- [Changelog](CHANGELOG.md)
- [Scripts](scripts/README.md)

## Versionado y releases

- La version oficial del modulo vive en `recordatorios/module.xml`.
- Los tags de Git usan el formato `vX.Y.Z`.
- El workflow de build valida la sintaxis PHP y genera el `.tgz` como artifact de prueba en `main` y en `pull_request`.
- No publicamos releases automáticos desde GitHub; el paquete final se genera y se instala según el flujo de trabajo del proyecto.
- Si cambias el codigo, actualiza primero `module.xml` y prueba el build en `main` o en una PR.

## Estructura del repositorio

- `recordatorios/`: codigo fuente del modulo para IssabelPBX.
- `docs/`: documentacion tecnica privada.
- `versiones/`: entregables o paquetes generados.
- `scripts/`: utilidades para empaquetar y probar.

## Requisitos

- IssabelPBX 5
- Asterisk 18+
- PHP 7.4
- MariaDB
- `cron`

## Estado actual

- Version actual del modulo: `1.0.1`
- Base empaquetada inicial: `1.0.0`
