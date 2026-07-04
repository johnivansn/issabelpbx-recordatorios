#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
MODULE_DIR="${MODULE_DIR:-${ROOT_DIR}/recordatorios}"
OUTPUT_DIR="${OUTPUT_DIR:-${ROOT_DIR}/versiones}"

if [[ ! -f "${MODULE_DIR}/module.xml" ]]; then
  echo "No se encontro ${MODULE_DIR}/module.xml" >&2
  exit 1
fi

RAWNAME="$(php -r '$xml = simplexml_load_file($argv[1]); echo (string) $xml->rawname;' "${MODULE_DIR}/module.xml")"
VERSION="$(php -r '$xml = simplexml_load_file($argv[1]); echo (string) $xml->version;' "${MODULE_DIR}/module.xml")"

if [[ -z "${RAWNAME}" || -z "${VERSION}" ]]; then
  echo "No se pudo leer rawname/version desde module.xml" >&2
  exit 1
fi

PACKAGE_NAME="${RAWNAME}-${VERSION}.tgz"
STAGING_DIR="$(mktemp -d)"
trap 'rm -rf "${STAGING_DIR}"' EXIT

mkdir -p "${OUTPUT_DIR}"
cp -R "${MODULE_DIR}" "${STAGING_DIR}/${RAWNAME}"

tar -czf "${OUTPUT_DIR}/${PACKAGE_NAME}" -C "${STAGING_DIR}" "${RAWNAME}"

echo "Paquete generado: ${OUTPUT_DIR}/${PACKAGE_NAME}"
