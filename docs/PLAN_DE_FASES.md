# Plan de implementación con compuertas de calidad

## Principio
El objetivo no es producir todas las pantallas rápidamente. Primero comprobar que el motor de cálculo es confiable, el archivo ARCA cumple diseño y una corrección histórica nunca destruye datos. No declarar terminado un módulo sin pruebas.

| Etapa | Entregable | Requisitos clave | Salida aceptable |
|---|---|---|---|
| 0 — análisis | ADR, ER, glosario, matriz de convenios, plan, pruebas PHP vs Next.js | PD-01…PD-07 y PD-13 | Decisión de stack justificada y aprobada antes de generar código de producto; reglas bloqueantes explicitadas |
| 1 — núcleo | Inicio de sesión, roles, empresas, legajos, auditoría | RF-001…015; RF-040/041 | 5 usuarios y aislamiento probado |
| 2 — reglas | CCT, categorías, escalas, conceptos y motor tipado | RF-020…030 | Tests fórmula/condiciones/versionado completos |
| 3 — mensual | Novedades, liquidación Comercio, revisión y PDF | RF-042…060; RF-080/081 | Casos C1/C2 reproducidos y validados |
| 4 — ARCA | Mapeos, validador y TXT fijo | RF-083…090 | TXT aceptado en prueba autorizada por ARCA |
| 5 — integral | SAC, vacaciones, licencias, final, retroactivos, más CCT | RF-050/061; RF-070…074 | Casos por tipo/CCT aprobados; histórico sin cambios |
| 6 — migración y operación | DBF si procede, backup, monitoreo, piloto | RF-075…077; RF-100…104 | Restauración probada, piloto de oficinas, plan rollback |

## Reglas de despliegue
- Git privado. Nunca publicar `.env`, dumps SQL, PDFs reales ni TXT con identidades.
- Separar staging/producción por bases de datos y configuraciones.
- Antes del scaffolding: comparar pequeñas pruebas de **PHP/Laravel** y **Next.js/Node.js** en Hostinger Business, con datos ficticios, validando PHP/extensiones/Composer/cron y despliegue Node, conectividad MySQL, PDF y TXT, límites de recursos y cinco sesiones. Aprobar ADR para implementar un solo stack.
- Definir URLs autenticadas para PDFs; nunca guardarlos en carpetas web públicas.
- Backups diarios y simulacro de restauración. Definir RPO/RTO con el estudio.
- Despliegues siempre reversibles; migraciones DB revisadas y respaldadas.

## Tareas técnicas sugeridas para el agente (sin empezar cálculo laboral)
1. Inicializar proyecto y scripts de calidad.
2. Modelo inicial de usuarios, roles, empleadores y registros de auditoría.
3. Tests de permisos, aislamiento por empresa y concurrencia.
4. CRUD de empresa/categorías con diseño original.
5. Diseño/implementación del parser AST en **modo simulación**, a partir de ejemplos matemáticos.
6. Motor de períodos y versiones con snapshots inmutables.
7. Exportador TXT puro y tests de formato con fixture sin datos personales.
8. Reunión y aceptación contable antes de conectar el motor al uso productivo.
