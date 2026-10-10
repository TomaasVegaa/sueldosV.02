# Plan de pruebas y datos de referencia — versión 0.1

## 1. Política de evidencia
El sistema nuevo debe demostrar **dos cosas por separado**:
1. **Regresión:** reproduce de forma explicable los importes del sistema anterior donde corresponda.
2. **Corrección vigente:** la regla utilizada fue revisada y aprobada por un liquidador con base normativa/convenio del período.

Una regresión exacta **NO** equivale a certeza jurídica. Los PDFs existentes sirven para comparar; las diferencias pueden originarse en normativa, parámetros, interpretación, ajustes manuales o defectos del cálculo anterior.

Prohibido usar los CUIL/CUIT, nombres, domicilios y CBU reales en tests del repositorio. Las referencias de importes agregados no van ligadas a identidad.

## 2. Casos de regresión observados (identidades anonimizadas)

| Caso | Escenario | Haberes remunerativos | No remunerativos | Descuentos | Neto observado |
|---|---|---:|---:|---:|---:|
| C1 | Mensual Comercio, media jornada, 1 trabajador | $668.700,86 | $65.000,00 | $167.831,70 | **$565.869,16** |
| C2 | Mensual Comercio, media jornada y adicional de caja | $658.052,68 | $114.908,02 | $165.222,90 | **$607.737,80** |
| C3 | Liquidación final Comercio con indemnizaciones, SAC, preaviso | $2.022.296,78 | $44.906.620,59 | $440.524,31 | **$46.488.393,06** |

En cada caso se verifica la identidad contable: `neto = remunerativo + no_remunerativo - descuentos`.

Los valores proceden de los PDFs aportados por el usuario; no validar legalmente estos importes sin revisar configuración, base de cálculo, convenios y condiciones del caso.

### C1 — Detalles para investigar
- Básico liquidado por 29 días, día gremio separado, presentismo, incremento no remunerativo, descuentos 11 % / 3 % / 3 % / 2 % / 0,5 %, aporte fijo OSECAC.
- Base de seguridad social observada alrededor de $668.700,86 y una base mayor usada para aportes/contribuciones de obra social (~$1.467.401,67); no derivar esto de simple factor sin revisar fórmula y normativa.
- Ejemplo de requerimiento: cálculos diferenciados según jornada parcial y base.

### C2 — Detalles para investigar
- Adicional de caja, faltante de caja, presentismo, conceptos no remunerativos y descuentos.
- Verificar cómo se presentan conceptos remunerativos y no remunerativos, y cómo afectan aportes, contribuciones y costo patronal.

### C3 — Detalles para investigar
- El legajo permite **fecha de ingreso y fecha de antigüedad diferentes**.
- Remuneraciones, componentes indemnizatorios, vacaciones no gozadas, SAC y preaviso deben discriminarse y validarse individualmente.
- No asumir que toda indemnización tiene idéntico tratamiento de aportes/contribuciones.
- Revisar con especialista el valor total: contiene partidas muy significativas; documentar descuentos, exclusiones y criterio de base.

## 3. Pruebas del motor de fórmulas

| ID | Tipo | Dato sintético | Resultado esperado |
|---|---|---|---|
| T-F01 | Básico por días | `SB=900000`, `VI=15`, fórmula `SB*VI/30` | `450000` antes de redondeo |
| T-F02 | Inasistencia | `SB=900000`, `VI=2`, `0-(SB/30*VI)` | `-60000` |
| T-F03 | Antigüedad ejemplo simple | `TH=900000`, `AN=3`, demás exclusiones = 0 | `27000` según fórmula ejemplo |
| T-F04 | Dependencias | `LQ250` depende de `TH`, `TH` de otros conceptos | Orden correcto por grafo |
| T-F05 | Circular | A depende de B y B depende de A | Rechazo con ciclo explicado |
| T-F06 | Invalidación | Fórmula referencia `LQ999` inexistente | Rechazo explícito |
| T-F07 | Condición | Regla por obra social `OSO=...` con entrada controlada | Aplica solo si se cumple |
| T-F08 | Exactitud | Operaciones decimales con centavos | Sin errores binarios de `number` |
| T-F09 | Versionado | Regla v1 y v2 con vigencias distintas | Período histórico conserva v1 |
| T-F10 | Fórmula inactiva | `...*0` heredada | Resultado cero; NO quitar multiplicación |
| T-F11 | División | Denominador cero | Error claro o regla explícita, nunca NaN guardado |
| T-F12 | Ajuste manual | Concepto manual autorizado + razón | Auditoría, no alterar fórmula base |

Las variables de fórmula de Cortez (`TH`, `TS`, `LQxxx`, etc.) requieren diccionario funcional y pruebas con entradas conocidas. Los ejercicios anteriores son **simulación matemática**, no norma salarial.

## 4. Pruebas de exportación ARCA

| ID | Caso | Criterio |
|---|---|---|
| T-A01 | Forma del TXT observado | Líneas `01/02/03/04` y sus anchos exactos (35, 115, 51, 370) |
| T-A02 | Offsets de 01 | `SJ`, AAAAMM, tipo M/Q/S, número, días base y cantidad en posiciones oficiales |
| T-A03 | Offsets de 03 | Cantidad e importe sin separadores, unidad, D/C, AAAAMM ajuste |
| T-A04 | Mapeo faltante | Bloquear archivo listo para carga y explicar conceptos sin asociar |
| T-A05 | CUIL invalido | Bloquear exportación real |
| T-A06 | 2 empleados | Cantidad tipo 04 y cabecera coherentes |
| T-A07 | Sueldo + SAC | Acumulación de bases 04 según guía |
| T-A08 | Liquidación posterior | Respetar valores de última liquidación/perfil cuando aplique |
| T-A09 | Corrección RE | Si se implementa, registros 01+04 según guía de rectificación |
| T-A10 | Trazabilidad | Log usuario/fecha/hash/versión y estatus manual de ARCA |
| T-A11 | Archivo de ejemplo anonimizado | Respeta forma, pero no supera validación de CUIT/CUIL |
| T-A12 | Idempotencia | Exportar 2 veces misma versión y parámetros -> bytes idénticos o metadata excluida del TXT |

## 5. Pruebas de historial
1. Crear liquidación v1, emitir PDF y TXT; registrar hash.
2. Modificar escala del mes siguiente; confirmar que v1 permanece intacta.
3. Encontrar error histórico, solicitar corrección y crear borrador v2.
4. Comparar v1/v2 con líneas y diferencias monetarias.
5. Aprobar v2, volver a emitir; comprobar que v1 y artefactos originales se conservan.
6. Verificar que la exportación de v1 no se actualiza por accidente.
7. Si v1 ya se presentó a ARCA, bloquear «reemplazo silencioso» y mostrar proceso autorizado.
8. Registrar usuario y motivo de la corrección; comprobar acceso a auditoría con perfil auditor.

## 6. Pruebas de concurrencia y aislamiento
- Cinco sesiones a la vez crean/actualizan liquidaciones de distintas empresas sin interferencia.
- Dos sesiones editan misma liquidación desde versión n: segunda detecta conflicto si ya cambió n.
- Usuario sin permiso de Empresa B intenta leer/modificar trabajador, archivo PDF o exportación TXT de B cambiando id de URL/request: 403/404 seguro.
- Queries paginadas, reportes y descargas aplican aislamiento por empresa.
- Adjuntos no son servidos por carpeta pública; URLs de descarga requieren autenticar/autorización.

## 7. Pruebas de PDFs
- Original/duplicado según configuración y diseño aprobado.
- Un recibo por trabajador y consolidado por empresa.
- Conceptos, unidades, totales, neto, identificación del período y datos obligatorios.
- Página A4, legibilidad y texto seleccionable; sin recortes ni importes desbordados.
- PDF histórico regenerado desde snapshot produce mismos montos aunque cambie regla vigente.
- No publicar PDFs reales mediante rutas predecibles o almacenamiento abierto.

## 8. Pruebas de seguridad/backup
- Intentos erróneos de login, recuperación segura, expiración de sesión y revocación.
- CSRF/XSS/SQLi básico; validaciones servidor; archivo malicioso rechazado.
- Backup cifrado, restauración a staging y cotejo de conteos/hashes.
- Confidencialidad en logs, monitoreo, endpoints y GitHub.

## 9. Pruebas para habilitar producción
**Gate 1:** sistema base + roles + integridad.  
**Gate 2:** 1 convenio validado y 3 casos reales anonimizados conciliados.  
**Gate 3:** exportación TXT aceptada en procedimiento real permitido por ARCA bajo un operador autorizado.  
**Gate 4:** multifuncionalidad adicional (SAC/final/etc.) solo si cada tipo tiene casos y firmas de aprobación.  
**Gate 5:** backup restaurado, permisos auditados, 5 usuarios concurrentes y manual de operación.
