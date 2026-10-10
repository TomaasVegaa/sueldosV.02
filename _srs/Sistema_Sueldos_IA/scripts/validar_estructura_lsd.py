#!/usr/bin/env python3
"""Verifica SOLO la forma básica de un archivo LSD. NO valida aceptación en ARCA.

La salida no muestra CUIT, CUIL, CBU ni importes para proteger datos.
Uso: python scripts/validar_estructura_lsd.py ejemplos/ESTRUCTURA_LSD_SINTETICA_NO_PRESENTAR.txt
"""
from __future__ import annotations
from collections import Counter
from pathlib import Path
import argparse
import sys

WIDTHS = {b"01": 35, b"02": 115, b"03": 51, b"04": 370, b"05": 65, b"06": 93}


def inspect_bytes(data: bytes) -> list[str]:
    messages: list[str] = []
    if not data:
        return ["ERROR: archivo vacío"]
    if any(byte >= 128 for byte in data):
        messages.append("AVISO: caracteres no ASCII detectados; verificar codificación oficial")
    raw_lines = data.splitlines()
    if data.count(b"\n") and data.count(b"\r\n") != data.count(b"\n"):
        messages.append("AVISO: no todos los saltos son CRLF como la muestra proporcionada")
    counts = Counter()
    for n, raw in enumerate(raw_lines, 1):
        kind = raw[:2]
        counts[kind] += 1
        label = kind.decode("ascii", errors="replace")
        if kind not in WIDTHS:
            messages.append(f"ERROR línea {n}: tipo desconocido [{label}]")
            continue
        expected = WIDTHS[kind]
        if len(raw) != expected:
            messages.append(f"ERROR línea {n}: tipo {label}, longitud {len(raw)}; esperada {expected}")
    if not raw_lines[0].startswith(b"01"):
        messages.append("ERROR: el registro 01 no es el primero")
    if counts[b"01"] != 1:
        messages.append("ERROR: se espera exactamente una cabecera tipo 01")
    if counts[b"04"] < 1:
        messages.append("ERROR: no hay registros tipo 04")
    header = raw_lines[0] if len(raw_lines[0]) == 35 else b""
    if header:
        send_type = header[13:15]
        declared = header[29:35]
        if not declared.isdigit() or int(declared) != counts[b"04"]:
            messages.append("ERROR: cantidad de trabajadores (04) no coincide con cabecera")
        if send_type == b"SJ":
            if counts[b"02"] < 1 or counts[b"03"] < 1:
                messages.append("AVISO: SJ sin tipo 02 o 03, verificar si es válido para este caso")
        elif send_type == b"RE":
            if any(counts[t] for t in (b"02", b"03", b"05")):
                messages.append("ERROR: envío RE contiene 02/03/05, contrario a guía de rectificación")
        else:
            messages.append("ERROR: identificador de envío diferente de SJ/RE")
    out = [f"Registros: {len(raw_lines)}; tipos: " + ", ".join(
        f"{k.decode('ascii', errors='replace')}={v}" for k, v in sorted(counts.items())
    )]
    return out + (messages or ["OK: estructura básica de tipos/anchos/cabecera comprobada (NO validez fiscal ni de ARCA)"])


def main() -> int:
    parser = argparse.ArgumentParser(description="Validación estructural básica de TXT LSD, sin exponer datos personales")
    parser.add_argument("archivo", type=Path)
    p = parser.parse_args()
    try:
        data = p.archivo.read_bytes()
    except OSError as exc:
        print(f"Error de lectura: {exc.__class__.__name__}", file=sys.stderr)
        return 2
    findings = inspect_bytes(data)
    for finding in findings:
        print(finding)
    return 1 if any(s.startswith("ERROR") for s in findings) else 0

if __name__ == "__main__":
    raise SystemExit(main())
