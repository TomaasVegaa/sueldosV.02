# AGENTS.md — Contrato de trabajo para Codex / Claude Code / Antigravity

## 0. Leer primero
Antes de proponer cambios, leer en orden:
1. `README.md` (uso del paquete)
2. `SRS_SUELDOS_WEB.md` (requisitos, alcance, restricciones)
3. `docs/ARCA_LSD.md` (formatos oficiales y casos del TXT)
4. `docs/MODELO_DE_DATOS.md` (dominio y consistencia)
5. `docs/PLAN_PRUEBAS.md` (criterios de aceptación)
6. `docs/PREGUNTAS_Y_DECISIONES.md` (hipótesis sin validar)
7. `docs/PLAN_DE_FASES.md` (entregas y orden)
8. `docs/COMPARATIVA_STACK_PHP_NEXTJS.md` (alternativas y prueba de hosting)

### 0.1 Trabajo paralelo entre agentes
Antes de editar, leer también `docs/COORDINACION_AGENTES.md` y
`docs/estado-requisitos.md`. Cuando Codex y Antigravity trabajen en paralelo:

- respetar la asignación temporal de módulos y archivos;
- no editar un archivo marcado por el otro agente sin registrar primero el traspaso;
- ejecutar `git status --short` antes y después de cada bloque de trabajo;
- dejar pruebas, límites y decisiones pendientes en el registro de handoff;
- detener cálculos laborales nuevos si una decisión figura como `BLOQUEADA`.

El SRS es un **borrador que necesita revisión humana**. Nunca presentar una liquidación como legalmente correcta sin verificar criterios, convenios y normas aplicables.

## 1. Misión
Desarrollar una nueva aplicación web interna de liquidación de haberes para un estudio contable argentino, con aproximadamente 80–100 empleadores, 5 operadores simultáneos y dos oficinas, trazabilidad, multiempresa/multiconvenio, versionado histórico, PDFs y archivos TXT para importación **manual** en ARCA.

## 2. NO hacer
- NO copiar código, imágenes, interfaz, marca o diseño visual de Cortez ni de ninguna aplicación existente. Las referencias sirven para definir necesidades de negocio.
- NO asumir que fórmulas o escalas proporcionadas de Comercio aplican a todos los convenios.
- NO suponer que los conceptos son globales porque el usuario dijo "creo"; modelar la incertidumbre y validar.
- NO implementar fórmulas sin definiciones explícitas de cada variable, bases y condiciones.
- NO hardcodear porcentajes de seguridad social, topes, jornales o indemnizaciones como si fueran permanentes.
- NO usar `eval`, `new Function` ni ejecutar expresiones introducidas por usuarios como código JS/PHP/SQL; crear intérprete limitado de fórmulas.
- NO calcular importes con `number` de punto flotante (JS) ni `float` (PHP); utilizar bibliotecas de cálculo decimal exacto, columnas `DECIMAL`/`NUMERIC` y reglas de redondeo probadas.
- NO permitir cambiar resultados históricos cerrados en lugar de crear una versión de corrección.
- NO autenticar ante ARCA ni presentar declaraciones; solo descargar un TXT para carga manual.
- NO incluir CUIT, CUIL, CBU, nombres, importes reales de sueldos o contraseñas de usuarios en Git, prompts, tests, capturas y logs sin anonimización/aprobación expresa.
- NO iniciar migración de DBF real sin permiso, respaldo, informe de mapeo y rollback.
- NO desplegar directamente en producción, ni lanzar migraciones irreversibles, ni enviar archivos a ARCA sin aprobación humana.
- NO simular éxito de importación ARCA: solo un operador puede confirmar el resultado.
- NO usar un resultado de un PDF previo como fundamento legal de cálculo.

## 3. Primero: entrega documental y decisión
**Primera orden:** inspeccionar el repositorio si existe, leer el SRS, enumerar decisiones bloqueantes, proponer arquitectura/dependencias, esquema ER inicial, plan de pruebas y estructura de carpetas. Solicitar aprobación antes de codificar cálculos laborales reales.

Crear `docs/ADR-001-arquitectura.md` y `docs/estado-requisitos.md`; cada decisión incluirá alternativas, tradeoffs y validación necesaria. Priorizar un **monolito modular** mantenible frente a microservicios. **ANTES de elegir o generar scaffolding**, comparar explícitamente Laravel/PHP y Next.js/TypeScript con la matriz de `docs/COMPARATIVA_STACK_PHP_NEXTJS.md`, verificar el plan Hostinger Business con prototipos vacíos y obtener aprobación del cliente. No imponer Next.js por defecto, ni descartar PHP sin medir.

## 4. Stacks candidatos — NO ELEGIDOS
- **Opción PHP:** Laravel (versión vigente y compatible con el PHP disponible en Hostinger Business), PHP con tipos, MySQL/MariaDB InnoDB, Eloquent + migraciones, Blade/Livewire/Alpine o Inertia/Vue si es necesario; PHPUnit/Pest y PHPStan. Exportador TXT puro y PDF guardado fuera del directorio web. Dinero con `DECIMAL` + BCMath/brick/math; prohibidos `float` para importes exactos y `eval` para reglas.
- **Opción JavaScript/TypeScript:** Next.js con TypeScript strict y servidor Node, MySQL/MariaDB con ORM/migraciones, biblioteca decimal exacta, motor autónomo; pruebas Node y controles de seguridad equivalentes.
- **Ambas:** monolito modular, tablas transaccionales y trazables, seguridad por empresa en servidor, repositorio Git privado, CI, staging, backups, exportación LSD por bytes y hash de artefactos.
- **Hostinger:** no asumir procesos de cola persistentes, Redis, recursos exclusivos ni versiones concretas sin revisión. Para PHP comprobar extensiones, Composer/SSH, document root `public/`, cron y tareas pesadas; para Node comprobar despliegue, runtime y cuotas. Si Business no alcanza, documentar antes el costo/impacto de una alternativa de hosting.
- **Selección:** entregar tabla de pros/contras y evidencia de prueba en `docs/ADR-001-arquitectura.md`; pedir confirmación del cliente. La UI original y las reglas funcionales son independientes del stack.

## 5. Entregas incrementales obligatorias
Cada PR/iteración deberá incluir:
1. Alcance exacto de requisitos RF implementados.
2. Diseño y migraciones DB documentadas.
3. Pruebas unitarias, integraciones y negativos/seguridad relacionados.
4. Evidencia reproducible del stack elegido (PHP: `php artisan test`, PHPStan/Pint o equivalentes; Node: `npm test`, `npm run lint`, `npm run typecheck`, build).
5. Capturas con datos ficticios (si corresponde).
6. Riesgos, límites y próximos pasos.

**No avanzar a la siguiente fase** hasta que la anterior pase aceptación humana.

## 6. Orden recomendado de trabajo
- Sprint/Fase 0: requisitos pendientes, especificación variables, catálogos, fuente ARCA y **comparación / prueba PHP Laravel vs Next.js** en Hostinger; aprobar ADR antes de generar el proyecto.
- Fase 1: scaffolding, login, permisos, entidades base, auditoría, pruebas.
- Fase 2: motor de expresiones y versiones; pruebas puras de fórmulas.
- Fase 3: liquidación mensual de Comercio con un set validado, workflow y PDF.
- Fase 4: integración TXT ARCA y mapeos; tests byte a byte; prueba de carga con operador.
- Fase 5: restantes tipos de liquidaciones, más convenios y correcciones; pruebas de regresión.
- Fase 6: importador DBF si procede, endurecimiento de seguridad, backup/restore y piloto de producción.

## 7. Criterios generales para el código
- Ids internos inmutables (UUID/ULID o bigint); claves únicas compuestas donde corresponda.
- `company_id/employer_id` en cada fila perteneciente a empresa; validar autorización para todas las consultas y mutaciones.
- `effective_from/effective_to` para reglas, escalas, categoría y atributos históricos.
- `lock_version` para concurrencia.
- Documento inmutable con hash SHA-256 de versión de entrada y bytes de salida.
- Restricciones DB + lógica transaccional; manejo de errores estructurados.
- El orden del cálculo se decide por grafo de dependencias; detectar ciclos.
- Versionar AST/resolución de variables junto al resultado para reproducibilidad.
- Tests con fechas de extremos, prorrateo, redondeos, bases distintas y múltiples liquidaciones/mes.
- Seguir diseño ARCA oficial al **momento de implementación** y documentar fecha de consulta.

## 8. Evaluación legal y contable
Un experto liquidador deberá aceptar cada regla/caso antes de publicarlo. Ante dudas, crear una pregunta de decisión y bloque de validación; jamás «resolver» adivinando la norma. La salida en pantalla se identificará como **simulación** hasta su aprobación.

## 9. Regla de privacidad
La carpeta `ejemplos/` usa identificadores deliberadamente inválidos y montos puestos a cero para mostrar solo la forma del TXT. No intentar presentarla a ARCA. Si un desarrollador recibe archivos reales, debe tratarlos como secretos; proponer anonimización y uso local, y nunca incluirlos en el repositorio.

## 10. Prompt de primera iteración
"Leé `AGENTS.md`, `SRS_SUELDOS_WEB.md` y `docs/COMPARATIVA_STACK_PHP_NEXTJS.md` completos. Sin implementar lógica de liquidación todavía, entregá (a) mapa de requisitos, (b) comparación técnica Laravel/PHP vs Next.js/TypeScript y ADR de arquitectura para Hostinger Business, con pruebas propuestas y criterios verificables, (c) diseño ER y permisos/versionado, (d) cronograma por hitos con tests, (e) preguntas bloqueantes. NO elijas stack ni empieces a codificar sin mi aprobación."
