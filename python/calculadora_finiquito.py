#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
calculadora_finiquito.py
========================
Calculadora de Finiquito Laboral según la normativa boliviana vigente.
Puede usarse como script independiente o como API para integrarse con PHP.

Normativa aplicada:
- Ley General del Trabajo (LGT) — 8/12/1942
- D.S. 28699 — Garantías laborales (2006)
- D.S. 23932 — Bono de antigüedad (1994)
- D.S. 1802 — Aguinaldo (2013)
- Art. 57 LGT — Prima anual

Uso:
    python calculadora_finiquito.py --ingreso 2020-01-15 --retiro 2025-01-15 --salario 5000 --tipo despido_injustificado
    python calculadora_finiquito.py --json '{"fecha_ingreso":"2020-01-15","fecha_retiro":"2025-01-15","salario_mensual":5000,"tipo_retiro":"despido_injustificado"}'
"""

import argparse
import json
import sys
from datetime import date, datetime
from dataclasses import dataclass, field, asdict


# Salario Mínimo Nacional Bolivia 2024-2025
SMN_BOLIVIA = 2362.00


@dataclass
class ResultadoFiniquito:
    trabajador_nombre: str = ""
    empresa: str = ""
    fecha_ingreso: str = ""
    fecha_retiro: str = ""
    salario_mensual: float = 0.0
    tipo_retiro: str = ""

    # Tiempo
    dias_trabajados: int = 0
    anos_trabajados: float = 0.0
    meses_trabajados: float = 0.0

    # Conceptos
    salario_diario: float = 0.0
    indemnizacion: float = 0.0
    desahucio: float = 0.0
    vacaciones_dias: float = 0.0
    vacaciones_monto: float = 0.0
    aguinaldo: float = 0.0
    prima: float = 0.0
    porcentaje_antiguedad: int = 0
    bono_antiguedad: float = 0.0

    # Total
    total_finiquito: float = 0.0

    # Referencias legales
    base_legal: dict = field(default_factory=dict)


def calcular_finiquito(
    fecha_ingreso: str,
    fecha_retiro: str,
    salario_mensual: float,
    tipo_retiro: str,
    trabajador_nombre: str = "",
    empresa: str = ""
) -> ResultadoFiniquito:
    """
    Calcula el finiquito laboral según la normativa boliviana.

    Args:
        fecha_ingreso: Fecha de inicio del contrato (YYYY-MM-DD)
        fecha_retiro:  Fecha de fin del contrato (YYYY-MM-DD)
        salario_mensual: Salario mensual en bolivianos
        tipo_retiro: 'despido_injustificado', 'despido_justificado',
                     'voluntario', 'mutuo_acuerdo'

    Returns:
        ResultadoFiniquito con todos los conceptos calculados
    """
    fi = datetime.strptime(fecha_ingreso, "%Y-%m-%d").date()
    fr = datetime.strptime(fecha_retiro, "%Y-%m-%d").date()

    if fr <= fi:
        raise ValueError("La fecha de retiro debe ser posterior a la fecha de ingreso.")

    resultado = ResultadoFiniquito(
        trabajador_nombre=trabajador_nombre,
        empresa=empresa,
        fecha_ingreso=fecha_ingreso,
        fecha_retiro=fecha_retiro,
        salario_mensual=salario_mensual,
        tipo_retiro=tipo_retiro,
    )

    # ── Tiempo trabajado ──
    dias = (fr - fi).days
    anos = dias / 365
    meses = dias / 30

    resultado.dias_trabajados  = dias
    resultado.anos_trabajados  = round(anos, 4)
    resultado.meses_trabajados = round(meses, 2)
    resultado.salario_diario   = round(salario_mensual / 30, 2)

    # ── 1. INDEMNIZACIÓN (Art. 13 LGT) ──
    # 1 salario por año. Mínimo 90 días para cobrar.
    if dias >= 90:
        resultado.indemnizacion = round(salario_mensual * anos, 2)
    resultado.base_legal["indemnizacion"] = "Art. 13 LGT — 1 mes/año trabajado"

    # ── 2. DESAHUCIO (Art. 12 LGT) ──
    # 3 meses de salario solo si es despido injustificado
    if tipo_retiro == "despido_injustificado":
        resultado.desahucio = round(salario_mensual * 3, 2)
    resultado.base_legal["desahucio"] = "Art. 12 LGT — 3 meses (solo despido injustificado)"

    # ── 3. VACACIONES (Art. 44 LGT, D.S. 12058) ──
    dias_vacacion_por_ano = 15
    if anos >= 10:
        dias_vacacion_por_ano = 30
    elif anos >= 5:
        dias_vacacion_por_ano = 20

    vacaciones_dias = (dias_vacacion_por_ano / 360) * dias
    vacaciones_monto = vacaciones_dias * resultado.salario_diario
    resultado.vacaciones_dias  = round(vacaciones_dias, 1)
    resultado.vacaciones_monto = round(vacaciones_monto, 2)
    resultado.base_legal["vacaciones"] = f"Art. 44 LGT — {dias_vacacion_por_ano} días/año (escala por antigüedad)"

    # ── 4. AGUINALDO PROPORCIONAL (D.S. 1802) ──
    mes_retiro = fr.month
    dia_retiro = fr.day
    dias_en_anio = (mes_retiro - 1) * 30 + dia_retiro
    resultado.aguinaldo = round(salario_mensual * (dias_en_anio / 360), 2)
    resultado.base_legal["aguinaldo"] = "D.S. 1802 — Proporcional al año en curso"

    # ── 5. PRIMA ANUAL (Art. 57 LGT) ──
    prima = salario_mensual * anos
    if prima > salario_mensual:
        prima = salario_mensual  # máximo 1 salario proporcional
    resultado.prima = round(prima, 2)
    resultado.base_legal["prima"] = "Art. 57 LGT — Prima anual proporcional"

    # ── 6. BONO DE ANTIGÜEDAD (D.S. 23932, D.S. 25495) ──
    tabla_antiguedad = [
        (25, 100), (24, 90), (22, 82), (20, 74), (18, 66),
        (16, 58), (14, 50), (12, 42), (10, 34), (8, 26),
        (6, 18), (4, 11), (2, 5), (0, 0)
    ]
    pct = 0
    for min_anos, porcentaje in tabla_antiguedad:
        if anos >= min_anos:
            pct = porcentaje
            break

    resultado.porcentaje_antiguedad = pct
    resultado.bono_antiguedad = round((SMN_BOLIVIA * pct / 100) * meses, 2)
    resultado.base_legal["bono_antiguedad"] = f"D.S. 23932 — {pct}% del SMN (Bs. {SMN_BOLIVIA}) × {round(meses,1)} meses"

    # ── TOTAL ──
    resultado.total_finiquito = round(
        resultado.indemnizacion + resultado.desahucio + resultado.vacaciones_monto
        + resultado.aguinaldo + resultado.prima + resultado.bono_antiguedad,
        2
    )

    return resultado


def mostrar_resultado(r: ResultadoFiniquito, formato: str = "texto"):
    if formato == "json":
        print(json.dumps(asdict(r), ensure_ascii=False, indent=2))
        return

    print("\n" + "═" * 65)
    print("   ESTUDIO JURÍDICO & ASOCIADOS — CÁLCULO DE FINIQUITO")
    print("═" * 65)
    if r.trabajador_nombre:
        print(f"   Trabajador: {r.trabajador_nombre}")
    if r.empresa:
        print(f"   Empresa:    {r.empresa}")
    print(f"   Período:    {r.fecha_ingreso} → {r.fecha_retiro}")
    print(f"   Salario:    Bs. {r.salario_mensual:,.2f}")
    print(f"   Tipo:       {r.tipo_retiro.replace('_',' ').title()}")
    print(f"   Tiempo:     {r.dias_trabajados} días ({r.anos_trabajados:.2f} años / {r.meses_trabajados:.1f} meses)")
    print("─" * 65)
    print(f"   Salario diario:                   Bs. {r.salario_diario:>10,.2f}")
    print("─" * 65)
    print(f"   Indemnización (Art. 13 LGT):      Bs. {r.indemnizacion:>10,.2f}")
    print(f"   Desahucio (Art. 12 LGT):          Bs. {r.desahucio:>10,.2f}")
    print(f"   Vacaciones ({r.vacaciones_dias:.1f} días):           Bs. {r.vacaciones_monto:>10,.2f}")
    print(f"   Aguinaldo proporcional:            Bs. {r.aguinaldo:>10,.2f}")
    print(f"   Prima anual proporcional:          Bs. {r.prima:>10,.2f}")
    print(f"   Bono antigüedad ({r.porcentaje_antiguedad}%):         Bs. {r.bono_antiguedad:>10,.2f}")
    print("═" * 65)
    print(f"   TOTAL FINIQUITO:                  Bs. {r.total_finiquito:>10,.2f}")
    print("═" * 65)
    print()


def main():
    parser = argparse.ArgumentParser(
        description="Calculadora de Finiquito Laboral Boliviano",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog=__doc__
    )
    parser.add_argument("--ingreso", "-i", help="Fecha de ingreso (YYYY-MM-DD)")
    parser.add_argument("--retiro",  "-r", help="Fecha de retiro (YYYY-MM-DD)")
    parser.add_argument("--salario", "-s", type=float, help="Salario mensual en Bs.")
    parser.add_argument("--tipo",    "-t",
        choices=["despido_injustificado","despido_justificado","voluntario","mutuo_acuerdo"],
        default="despido_injustificado",
        help="Tipo de retiro")
    parser.add_argument("--nombre",  "-n", default="", help="Nombre del trabajador")
    parser.add_argument("--empresa", "-e", default="", help="Empresa")
    parser.add_argument("--json",    "-j", help="Datos en formato JSON")
    parser.add_argument("--formato", "-f", choices=["texto","json"], default="texto")

    args = parser.parse_args()

    try:
        if args.json:
            data = json.loads(args.json)
            r = calcular_finiquito(
                fecha_ingreso    = data["fecha_ingreso"],
                fecha_retiro     = data["fecha_retiro"],
                salario_mensual  = float(data["salario_mensual"]),
                tipo_retiro      = data.get("tipo_retiro","despido_injustificado"),
                trabajador_nombre= data.get("trabajador_nombre",""),
                empresa          = data.get("empresa",""),
            )
        elif args.ingreso and args.retiro and args.salario:
            r = calcular_finiquito(
                fecha_ingreso    = args.ingreso,
                fecha_retiro     = args.retiro,
                salario_mensual  = args.salario,
                tipo_retiro      = args.tipo,
                trabajador_nombre= args.nombre,
                empresa          = args.empresa,
            )
        else:
            # Modo interactivo
            print("\n══ CALCULADORA DE FINIQUITO LABORAL BOLIVIANO ══\n")
            ingreso = input("Fecha de ingreso (YYYY-MM-DD): ").strip()
            retiro  = input("Fecha de retiro  (YYYY-MM-DD): ").strip()
            salario = float(input("Salario mensual (Bs.): ").strip())
            print("Tipo de retiro:")
            print("  1. Despido injustificado")
            print("  2. Despido justificado")
            print("  3. Retiro voluntario")
            print("  4. Mutuo acuerdo")
            opcion = input("Opción (1-4): ").strip()
            tipos = {
                "1": "despido_injustificado",
                "2": "despido_justificado",
                "3": "voluntario",
                "4": "mutuo_acuerdo"
            }
            tipo = tipos.get(opcion, "despido_injustificado")
            nombre  = input("Nombre trabajador (opcional): ").strip()
            empresa = input("Empresa (opcional): ").strip()
            r = calcular_finiquito(ingreso, retiro, salario, tipo, nombre, empresa)
            args.formato = "texto"

        mostrar_resultado(r, args.formato)

    except ValueError as e:
        print(f"Error: {e}", file=sys.stderr)
        sys.exit(1)
    except KeyError as e:
        print(f"Campo requerido faltante: {e}", file=sys.stderr)
        sys.exit(1)


if __name__ == "__main__":
    main()
