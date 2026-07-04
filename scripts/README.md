# Scripts

## build-tgz.sh

Genera un paquete `.tgz` del modulo `recordatorios` para probarlo en IssabelPBX.

### Uso

```bash
bash scripts/build-tgz.sh
```

### Resultado

El paquete se guarda en `versiones/` con el nombre:

```text
recordatorios-<version>.tgz
```

## Alternativa con Task

Si tienes `task` instalado, puedes usar:

```bash
task check
task lint
task package
task clean
```
