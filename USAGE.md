# Uso

## Web

- Abre la pagina `recordatorios` en IssabelPBX.
- Crea un recordatorio para el dia siguiente.
- Elige destino, hora y audio.
- Usa el listado para reproducir, editar o borrar recordatorios.

## Flujo telefonico

- `1`: crear un recordatorio.
- `2`: listar recordatorios.
- `3`: editar un recordatorio.
- `4`: borrar un recordatorio.
- `5`: salir.

## Flujo de audio

- Usa `*` para cancelar un subflujo.
- Usa `#` para terminar una grabacion.
- El audio se puede reproducir desde el listado web o desde la llamada automatica.

## Flujo tipico

1. Sube o graba el audio del recordatorio.
2. Define la extension o numero de destino.
3. Elige la hora del recordatorio.
4. Guarda el recordatorio.
5. Deja que `cron` dispare la llamada 15 minutos antes de la hora elegida.

## Comportamiento esperado

- Los recordatorios son solo para el dia siguiente.
- La llamada automatica ocurre 15 minutos antes de la hora configurada.
- Solo se pueden editar o borrar los recordatorios pendientes.
- Si el usuario cancela un subflujo, el modulo debe detenerse sin guardar datos parciales.
