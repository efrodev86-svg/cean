#!/usr/bin/env python3
"""Exporta Reinscripción 25-26B (Respuestas).xlsx a database/data/reinscripcion-2526b.json"""

from __future__ import annotations

import json
import re
import sys
from pathlib import Path

try:
    import openpyxl
except ImportError:
    import subprocess

    subprocess.check_call([sys.executable, "-m", "pip", "install", "openpyxl", "-q"])
    import openpyxl

SEDE_DEFAULT = "22DNL0001P"
FECHA_NACIMIENTO_OVERRIDES = {
    "RERDO11227MQTYJNA0": "2004-11-27",
}


def norm_lic(val) -> str | None:
    s = str(val or "").strip()
    if "Español" in s or s.upper() == "ESPANOL":
        return "ESPANOL"
    if "Telesecundaria" in s or s.upper() == "TELESECUNDARIA":
        return "TELESECUNDARIA"
    return s.upper() if s else None


def norm_email(val) -> str | None:
    if val is None:
        return None
    s = str(val).strip().lower()
    if not s or "@" not in s:
        return None
    return s if re.match(r"^[^@\s]+@[^@\s]+\.[^@\s]+$", s) else None


def norm_bool_si_no(val) -> bool:
    s = str(val or "").strip().lower()
    return s in {"sí", "si", "yes", "true", "1"}


def fecha_desde_curp(curp: str) -> str | None:
    curp = curp.strip().upper()
    if curp in FECHA_NACIMIENTO_OVERRIDES:
        return FECHA_NACIMIENTO_OVERRIDES[curp]
    if len(curp) < 10:
        return None
    yy, mm, dd = curp[4:6], curp[6:8], curp[8:10]
    if not (yy.isdigit() and mm.isdigit() and dd.isdigit()):
        return None
    year = 1900 + int(yy) if int(yy) > 30 else 2000 + int(yy)
    return f"{year:04d}-{int(mm):02d}-{int(dd):02d}"


def norm_semestre(val) -> int | None:
    if val is None:
        return None
    s = str(val).strip()
    m = re.search(r"\d+", s)
    return int(m.group()) if m else None


def norm_text(val) -> str | None:
    if val is None:
        return None
    s = str(val).strip()
    return s if s else None


def norm_phone(val) -> str | None:
    if val is None:
        return None
    s = re.sub(r"\D", "", str(val))
    return s if s else None


def resolver_email(inst, personal, legacy) -> str | None:
    return norm_email(inst) or norm_email(personal) or norm_email(legacy)


def row_to_record(row: dict) -> dict | None:
    matricula = norm_text(row.get("matricula") or row.get("Matrícula:\nEnviada por correo previamente."))
    if not matricula:
        return None

    curp = (norm_text(row.get("CURP")) or "").upper()
    email_inst = norm_email(row.get("Correo institucional"))
    email_personal = norm_email(row.get("Correo personal"))
    legacy_email = norm_email(row.get("Correo electrónico:\nInstitucional (2, 4 y 6 semestre)\nPersonal (8 semestre)"))
    email = resolver_email(email_inst, email_personal, legacy_email)

    semestre = norm_semestre(row.get("Semestre:"))
    licenciatura = norm_lic(row.get("Licenciatura:"))

    if semestre is None or licenciatura is None:
        return None

    regular = norm_bool_si_no(row.get("Regular:"))
    irregular = norm_bool_si_no(row.get("Irregular:"))

    return {
        "sede": norm_text(row.get("SEDE")) or SEDE_DEFAULT,
        "matricula": matricula,
        "referencia_pago": norm_text(row.get("Referencia de pago")),
        "curp": curp or None,
        "email_institucional": email_inst,
        "email_personal": email_personal,
        "email": email,
        "nombres": norm_text(row.get("NOMBRE (S)")),
        "apellido_paterno": norm_text(row.get("PRIMER APELLIDO")),
        "apellido_materno": norm_text(row.get("SEGUNDO APELLIDO")),
        "domicilio": norm_text(row.get("Domicilio:")),
        "colonia": norm_text(row.get("Colonia:")),
        "codigo_postal": norm_phone(row.get("Código Postal:")),
        "estado": norm_text(row.get("Estado:")),
        "municipio": norm_text(row.get("Municipio:")),
        "celular": norm_phone(row.get("Celular:")),
        "telefono_emergencia": norm_phone(
            row.get("Tel. de emergencia: \nTiene que ser distinto al anterior.")
        ),
        "nss": norm_phone(row.get("Número de seguro social:")),
        "tiene_diagnostico": norm_bool_si_no(
            row.get("¿Actualmente presenta un diágnostico psicológico, médico o cognitivo?")
        ),
        "diagnostico_detalle": norm_text(row.get("Especifique")),
        "tiene_discapacidad": norm_bool_si_no(row.get("Discapacidad:")),
        "discapacidad_detalle": norm_text(row.get("Especifique:")),
        "estado_civil": norm_text(row.get("Estado Civil:")),
        "labora": norm_bool_si_no(row.get("¿Labora actualmente?")),
        "lugar_trabajo": norm_text(row.get("¿Dónde?")),
        "semestre": semestre,
        "licenciatura": licenciatura,
        "estatus": "irregular" if irregular else "regular",
        "tipo_ingreso": "nuevo",
        "entidad_procedencia": None,
        "ciudad_procedencia": None,
        "asignatura_adeuda": norm_text(row.get("Asignatura que adeuda")),
        "fecha_nacimiento": fecha_desde_curp(curp) if curp else None,
    }


def main() -> int:
    excel = Path(sys.argv[1]) if len(sys.argv) > 1 else Path(
        "/Users/ensq/Downloads/Reinscripción 25-26B (Respuestas).xlsx"
    )
    out = Path(sys.argv[2]) if len(sys.argv) > 2 else Path("database/data/reinscripcion-2526b.json")

    if not excel.is_file():
        print(f"No se encontró el Excel: {excel}", file=sys.stderr)
        return 1

    wb = openpyxl.load_workbook(excel, read_only=True, data_only=True)
    ws = wb.active
    rows = list(ws.iter_rows(values_only=True))
    header = rows[0]
    raw = [dict(zip(header, r)) for r in rows[1:] if any(x is not None for x in r)]

    registros: list[dict] = []
    vistos: set[str] = set()

    for row in raw:
        registro = row_to_record(row)
        if registro is None:
            continue
        if registro["matricula"] in vistos:
            continue
        vistos.add(registro["matricula"])
        registros.append(registro)

    registros.sort(
        key=lambda r: (
            (r.get("apellido_paterno") or "").lower(),
            (r.get("apellido_materno") or "").lower(),
            (r.get("nombres") or "").lower(),
        )
    )

    out.parent.mkdir(parents=True, exist_ok=True)
    out.write_text(json.dumps(registros, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(f"Exportados {len(registros)} alumnos → {out}")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
