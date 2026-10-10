# Diccionario de Variables y Motor de Fórmulas

**Fuente de referencia:** Archivo de producción `CONCEPTO CONVENIOS.pdf` y recibos de haberes reales (`LIQ SDO SEPTIEMBRE 2026.pdf`).  
**Alcance:** **GLOBAL para todos los convenios del estudio** (Comercio CCT 130/75, Farmacia, Gastronómicos, Sanidad, etc.). Cualquier concepto creado o modificado es transversal y aplicable a cualquier empleador y convenio.  
**Propósito:** Especificar las variables reconocidas, su tipo de dato, origen y semántica en el motor de liquidación.

---

## 1. Variables de Entrada y Entorno

| Variable | Tipo | Descripción y Origen | Ejemplo / Uso |
|---|---|---|---|
| `SB` | `Decimal` | **Sueldo Básico** de la categoría del convenio para la vigencia del período. | `SB*VI/30*1` |
| `NR` | `Decimal` | **Sueldo No Remunerativo** de la categoría del convenio. | `NR*1` |
| `IM` | `Decimal` | **Importe Fijo** configurado en el catálogo del concepto (ej. adicional de $4000 o porcentaje de descuento). | `TH*IM/100` |
| `VI` | `Decimal` | **Valor Informado** para el legajo en el período (ej. días trabajados, unidades de inasistencia, horas extras, porcentaje de embargo). Si no se informa, toma 0 o el valor por defecto. | `SB/30*VI` |
| `AN` | `Entero` | **Años de Antigüedad** reconocidos del trabajador a la fecha de liquidación (`fecha_liquidacion - seniority_date`). | `(TH-LQ015)*0.01*AN` |
| `SIT` | `Entero` | **Situación / Jornada** del empleado: `1` = Completa, `2` = 3/4 jornada (0.75), `3` = 1/2 jornada (0.50), `4` = 1/4 jornada (0.25), `5` = 1/3 jornada (0.333). | `IIF(SIT=3, 0.5, 1)` |
| `SID` | `Entero` | **Situación Días:** Días efectivamente liquidados en el mes para el trabajador (proporcionalización de bases). | `SID/SIM` |
| `SIM` | `Entero` | **Situación Mes:** Días estándar del mes (habitualmente 30). | `SID/SIM` |
| `OSO` | `Cadena` | **Código de Obra Social** asignada al trabajador (ej. `126205` para OSECAC). | `OSO=126205` |

---

## 2. Acumuladores de Subtotales

| Variable | Tipo | Descripción | Regla de Cálculo |
|---|---|---|---|
| `TH` | `Decimal` | **Total Haberes Remunerativos**: Sumatoria de todos los conceptos liquidados de naturaleza remunerativa para el legajo. | Suma de líneas con `nature = REMUNERATIVO` |
| `TS` | `Decimal` | **Total Salarios No Remunerativos**: Sumatoria de todos los conceptos liquidados no remunerativos. | Suma de líneas con `nature = NO_REMUNERATIVO` |
| `TD` | `Decimal` | **Total Descuentos**: Sumatoria de aportes y descuentos al empleado. | Suma de líneas con `nature = DESCUENTO` |
| `TN` | `Decimal` | **Total Neto**: Remunerativo + No Remunerativo - Descuentos (`TH + TS - TD`). | Sueldo a cobrar antes de retenciones judiciales |
| `TSI` | `Decimal` | **Total Salarios No Remunerativos Sujetos a Aportes Sindicales/OS** (según acuerdo). | Subconjunto de `TS` |

---

## 3. Referencias entre Conceptos

- **`LQxxx`**: Representa el **importe monetario resultante** del concepto con código visible `xxx`.
  - Ejemplo: `LQ001` es el monto liquidado en el Sueldo Básico.
  - Ejemplo: Presentismo en Comercio: `(TH - LQ015 - LQ020 - LQ010) / 12 * 1`.
  - Ejemplo: Jubilación: `TH * 11 / 100`.
- **`CQxxx`**: Representa la **cantidad informada o liquidada** del concepto con código visible `xxx`.
  - Ejemplo: Condición de presentismo: `CQ004 < 0.5` (se liquida si las inasistencias `004` son menores a media falta).

---

## 4. Funciones y Operadores Permitidos en el Intérprete

1. **Aritméticos:** `+`, `-`, `*`, `/`, `(` y `)`.
2. **Comparaciones:** `=`, `<>`, `<`, `<=`, `>`, `>=`.
3. **Lógicos:** `AND`, `OR`, `NOT` (también `.AND.`, `.OR.` del formato tradicional).
4. **Condicional:** `IIF(condicion, valor_si_verdadero, valor_si_falso)`.
5. **Matemáticos:** `ROUND(valor, decimales)`, `MIN(a, b)`, `MAX(a, b)`, `ABS(valor)`.

### Reglas de Seguridad e Integridad del Intérprete
- Prohibida cualquier función de ejecución dinámica (`eval`, llamadas al sistema o inyección SQL).
- El motor construye un **Grafo Dirigido Acíclico (DAG)** de dependencias antes de liquidar:
  - Detecta ciclos (si A depende de B y B de A) y los rechaza con un error descriptivo.
  - Ordena automáticamente el cálculo en orden topológico (los conceptos base primero, luego adicionales, presentismo, sumas no remunerativas y finalmente los descuentos y retenciones).
