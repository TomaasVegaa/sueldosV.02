# Integración con ARCA — Libro de Sueldos Digital (LSD)

**Estado:** especificación inicial comprobada contra (a) archivo TXT de referencia del usuario, (b) diseño de interfaz ARCA publicado y (c) guía de uso. Debe revalidarse antes de implementar y antes del primer envío real.

## 1. Objetivo y límites
Generar un `.txt` de formato fijo descargable para que **un operador humano** lo importe en ARCA. No almacenar claves fiscales, no efectuar altas/bajas registrales, no automatizar presentación, no suplantar la revisión de ARCA.

Fuentes oficiales (consultadas 09/10/2026):
- Diseño de interfaz — liquidación: https://arca.gob.ar/LibrodeSueldosDigital/documentos/nuevos/LSDiseInterfazLiquidacion.pdf
- Diseño de interfaz — conceptos: https://arca.gob.ar/LibrodeSueldosDigital/documentos/nuevos/LSDiseInterfazConceptos.pdf
- Guía N.º 5 — ingreso de liquidaciones: https://www.arca.gob.ar/LibrodeSueldosDigital/documentos/nuevos/G05_Liq_ingreso_LSD.pdf
- Guía para rectificar: https://www.arca.gob.ar/LibrodeSueldosDigital/documentos/nuevos/LS_Guia_para_rectificar_v1.pdf
- Sitio y ayuda: https://arca.gob.ar/LibrodeSueldosDigital/ayuda/disenios.asp

**Fuente de verdad para bytes, anchos y descripciones:** diseño oficial anterior; este documento es un resumen y NO reemplaza el PDF oficial.

## 2. Observación real (sin publicar datos personales)
El TXT que entregó el usuario (`SIJP...txt`) tiene **1.001 bytes**, 12 líneas ASCII y 11 separadores CRLF, sin salto al final. La estructura observada es:

| Tipo | Cantidad | Ancho en caracteres | Función |
|---|---:|---:|---|
| `01` | 1 | 35 | Cabecera envío |
| `02` | 1 | 115 | Datos referenciales de trabajador |
| `03` | 9 | 51 | Conceptos liquidados |
| `04` | 1 | 370 | Datos/parametrización para F.931 |

Archivo observado: `SJ`, período `202605`, liquidación `M`, número de liquidación `00003`, días base `30`, un trabajador. Los CUIT/CUIL reales no se reproducen aquí.

**Importante:** el archivo proporcionado no prueba por sí solo que su contenido haya sido aceptado por ARCA ni que la configuración de conceptos sea correcta. Requerir confirmación de aceptación o un procedimiento de prueba autorizado.

## 3. Registros en envío `SJ`

### Registro `01` (35 caracteres, obligatorio)

| Posiciones 1-based | Longitud | Campo | Ejemplo sintético |
|---|---:|---|---|
| 1–2 | 2 | Tipo registro | `01` |
| 3–13 | 11 | CUIT empleador | solo datos autorizados y válidos |
| 14–15 | 2 | Identificación | `SJ` |
| 16–21 | 6 | Período | `202605` |
| 22 | 1 | Tipo liquidación | `M`, `Q` o `S` según ARCA |
| 23–27 | 5 | Número liquidación | `00003` |
| 28–29 | 2 | Días base | `30` u otro valor admitido |
| 30–35 | 6 | Cantidad trabajadores tipo `04` | `000001` |

### Registro `02` (115 caracteres para `SJ`)
- 1–2 identificador `02`.
- 3–13 CUIL trabajador.
- 14–23 legajo (10, alfanumérico).
- 24–73 dependencia de revista (50).
- 74–95 CBU de acreditación cuando corresponda (22).
- 96–98 cantidad de días para proporcionalización del tope (3).
- 99–106 fecha de pago `AAAAMMDD`.
- 107–114 fecha de rúbrica `AAAAMMDD` (cuando corresponda).
- 115 forma de pago (ver códigos del diseño oficial).

### Registro `03` (51 caracteres; tantas líneas como conceptos)

| Posiciones | Ancho | Campo |
|---|---:|---|
| 1–2 | 2 | `03` |
| 3–13 | 11 | CUIL trabajador |
| 14–23 | 10 | Código concepto del **empleador** |
| 24–28 | 5 | Cantidad, 3 enteros + 2 decimales (sin separador) |
| 29 | 1 | Unidad según tabla ARCA |
| 30–44 | 15 | Importe, 13 enteros + 2 decimales (sin separador) |
| 45 | 1 | `D` débito o `C` crédito |
| 46–51 | 6 | Período de ajuste retroactivo `AAAAMM` cuando aplique |

Ejemplo matemático de formato: un importe ficticio de $1.234,56 ocupa los 15 caracteres `000000000123456` (13 dígitos enteros + 2 decimales sin coma). Cantidad 8,33 se representa `00833`. No usar floats ni conversiones de locale para producir esto.

### Registro `04` (370 caracteres, obligatorio)
- 1–2 tipo `04`, 3–13 CUIL.
- Campos para familia, CCT, seguro, tipo empleador, situación de revista, condición, actividad, modalidad, localidad, obra social y datos complementarios.
- Bases monetarias y remuneración bruta de formato fijo, con campos entre posiciones 101–370; **usar tabla original oficial de los 47 campos**, no inferir offsets automáticamente.
- Entre otras posiciones publicadas por ARCA: remuneración bruta 161–175, bases imponibles 1 a 9 en 176–310, base imponible 10 en 341–355, importe a detraer en 356–370. Existen campos adicionales 311–340; NO ignorarlos ni llenarlos por intuición.
- Con más de una liquidación para el mismo CUIL/AAAAMM, **bases/remuneración bruta/importe a detraer se acumulan** como indica la Guía N.º 5.
- La guía señala que datos/perfil de la última liquidación del período prevalecen para el cálculo del F.931; exigir advertencia al cambiar situación de revista entre liquidaciones.

### Registro `05`
Opcional y condicionado: datos de trabajadores eventuales según definición del diseño oficial. No insertar a todas las empresas.

### Registro `06`
Opcional y condicionado: observaciones según diseño oficial; no usarlo como contenedor libre indiscriminado de información sensible.

## 4. Conceptos del empleador vs conceptos ARCA
El código en registro 03 es el **concepto del empleador**. Debe existir parametrización aceptada por ARCA para vincularlo con el código de concepto oficial.

El archivo de interfaz de conceptos ARCA define, entre otros:
- código ARCA de 6 caracteres;
- código empleador de 10 caracteres;
- descripción de 150 caracteres;
- marca de repetición;
- indicadores para cada régimen de aportes/contribuciones.

**No** aplicar un mapeo universal a 80–100 empresas sin validación. El estudio debe revisar qué mapeos se comparten y cuáles son propios de una razón social/convenio. Versionar mapeo y vigencia.

## 5. Archivos de rectificación: distinguir los flujos
La guía de rectificación de ARCA describe un flujo especial identificado como **`RE`**, con condiciones distintas del envío normal:
- tipo `01` y tipo `04` obligatorios;
- no acepta tipos `02`, `03` ni `05` en ese procedimiento;
- tipo `01` con identificación `RE` y campos específicos en blanco;
- registro `04` completo para los empleados incluidos en la rectificación.

Esto **no significa** que un usuario pueda simplemente exportar el TXT `SJ` otra vez y considerarlo una rectificativa correcta. Requiere flujo de aprobación y la decisión funcional de cuándo rectificar o ajustar. Implementar `RE` solo tras revisar la guía vigente y probarlo de manera autorizada.

## 6. Reglas de validación técnica

**Encabezado y orden**
- Un solo `01` al comienzo; `SJ` o `RE` coherente con presencia de tipos de registro.
- Contar registros `04` y contrastar contra la cantidad declarada en `01`.
- Por empresa, separar exportaciones por CUIT/periodo/tipo/número, según validación oficial.
- No duplicar trabajador en registros `02`/`04` de la misma liquidación, salvo formato reglado que lo permita.

**Tipos/longitudes**
- `01` 35; `02` 115; `03` 51; `04` 370; implementar `05`/`06` con su diseño exacto.
- Campos de ancho fijo rellenados según tipo (ceros/espacios según especificación); comprobar Unicode y bytes antes de exportar.
- Observación del ejemplo: ASCII y CRLF. Comprobar requisitos oficiales de codificación y finales de archivo, no imponerlos por la muestra únicamente.
- Valores numéricos sin punto, coma, signo libre ni notación científica.
- Códigos CUIL/CUIT tratados como cadenas, preservando ceros en otros identificadores (legajos, OS, etc.).

**Semántica**
- Verificación fiscal de CUIT/CUIL y correlación empresa-trabajador.
- Validación de fechas, período, tipo, número, condiciones previsionales y obra social vigentes.
- Todos los conceptos enviados deben tener asociación ARCA válida, activa y aprobada.
- Bases por período/trabajador acumuladas de modo correcto; sin sumas negativas inválidas por grupos de conceptos (ver guía).
- Ningún TXT listo para presentación si hay errores críticos. Reporte de advertencias e incidencias con ubicación de registro/campo, mostrando CUIL enmascarado.

## 7. Pruebas de bytes y pruebas funcionales
1. Prueba unitaria: serializar cada registro y comparar ancho exacto.
2. Prueba unitaria: offset 1-based de cada campo según PDF.
3. Prueba: 1 trabajador con nueve conceptos, como estructura del archivo observado.
4. Prueba: mensual y SAC para un CUIL en el mismo período; acumulación de bases.
5. Prueba: dos trabajadores y conteo del tipo 04.
6. Prueba: faltan mapeos de conceptos -> bloquear exportación.
7. Prueba: `RE` sin tipos `02`/`03` y campos de cabecera en blanco **solo cuando módulo RE se habilite y valide**.
8. Prueba: importes límites, ceros, redondeos, fechas y CUIL incorrectos.
9. Prueba: mostrar al operador advertencia de dato sensible en archivo antes de descarga.
10. Prueba de integración real en ARCA dirigida por operador autorizado, sin agentes con credenciales fiscales.

## 8. Privacidad de fixtures
`../ejemplos/ESTRUCTURA_LSD_SINTETICA_NO_PRESENTAR.txt` conserva tipos de línea/anchos y está **intencionalmente invalidado**: CUIT/CUIL se sustituyeron por `00000000000` e importes por cero. Es solo prueba de serialización, NO caso fiscal válido.
