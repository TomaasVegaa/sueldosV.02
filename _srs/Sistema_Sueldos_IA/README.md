# Paquete de inicio para agente de programación — Sistema Web de Sueldos

**Versión 0.2 — 09/10/2026 — incorpora PHP/Laravel como opción técnica; no software listo para producción.**

Este ZIP permite comenzar un proyecto con **Codex, Claude Code o Antigravity**. Contiene un SRS basado en los requerimientos del usuario, documentación técnica y reglas para que el agente trabaje por etapas y no implemente cálculos no validados.

## ¿Qué archivo abrir?

- **`SRS_SUELDOS_WEB.md`**: especificación de negocio y requisitos funcionales/no funcionales.
- **`AGENTS.md`**: instrucciones obligatorias para el agente. Ideal en la raíz de un repositorio (Codex y otros agentes lo leen como contexto de trabajo).
- **`docs/ARCA_LSD.md`**: estructura real de TXT y fuentes oficiales ARCA.
- **`docs/MODELO_DE_DATOS.md`**: propuesta de tablas, versionado y reglas.
- **`docs/PLAN_PRUEBAS.md`**: pruebas de cálculos, auditoría, simultaneidad y TXT.
- **`docs/PREGUNTAS_Y_DECISIONES.md`**: incógnitas que **NO** deben suponerse.
- **`docs/PLAN_DE_FASES.md`**: secuencia de hitos y criterios para avanzar.
- **`docs/COMPARATIVA_STACK_PHP_NEXTJS.md`**: evaluación de Laravel/PHP frente a Next.js/TypeScript para Hostinger Business, con pruebas de aceptación.
- **`ejemplos/ESTRUCTURA_LSD_SINTETICA_NO_PRESENTAR.txt`**: muestra de **estructura solamente**; CUIL/CUIT inválidos y números monetarios a cero, NO presentar en ARCA.
- **`scripts/validar_estructura_lsd.py`**: inspección local de estructura, SIN validación normativa ni identificación de contribuyente.

## Cómo comenzar en la herramienta elegida

1. Descomprimí el ZIP en una carpeta vacía del proyecto o subí los documentos a tu repositorio **privado**.
2. Abrí esa carpeta en el agente, asegurándote de que `AGENTS.md` y `SRS_SUELDOS_WEB.md` quedan en la raíz.
3. Pegá el siguiente pedido:

> Leé `AGENTS.md`, `SRS_SUELDOS_WEB.md` y todos los documentos de `docs/`. Antes de programar, compará obligatoriamente **Laravel (PHP)** y **Next.js (TypeScript)** para mi plan **Hostinger Business**, verificando requisitos reales, riesgos de hosting, costo y mantenimiento. Entregá una matriz comparativa, una recomendación justificada, el ADR pendiente de mi aprobación, el esquema ER y el plan de pruebas. NO elijas ni implementes un stack sin mi aprobación; no programes todavía liquidaciones ni publiques nada.

4. Corregí/confirmá las decisiones pendientes y pasá un ejemplo de liquidación real **anonimizado** al equipo técnico para pruebas controladas.
5. Implementá módulos con pruebas y verificación funcional antes de publicar.

## Tratamiento de datos personales
Los archivos originales aportados contienen datos reales de empleadores y empleados. **No se incluyen en este paquete**. El ejemplo TXT que se incluye no es fiscalmente válido: muestra tamaños y estructura sin identificar personas.

No compartir en un repositorio público ni con herramientas externas sin controles los PDFs/TXTs/DBF originales. Los documentos del SRS incluyen exclusivamente nombres genéricos y algunos **totales de prueba sin identidad** provenientes de los ejemplos previos.

## Límites actuales
- El motor de fórmulas requiere confirmar variables y comportamiento real.
- El alcance multiconvenio está requerido, pero los conceptos proporcionados corresponden a Comercio.
- La estructura del TXT ha sido analizada; la aceptación en ARCA debe comprobarse por un operador autorizado.
- Hostinger publica compatibilidad de Laravel/PHP en hosting web y opciones Node.js según tipo de despliegue; eso **no confirma** que la configuración real del cliente tenga capacidad suficiente. Verificar hPanel y probar ambas alternativas con datos ficticios antes de decidir.
- La migración de DBF es posibilidad, no promesa; requiere acceso legítimo, respaldo y mapeo.

## Fuentes oficiales
- ARCA diseño LSD: https://arca.gob.ar/LibrodeSueldosDigital/ayuda/disenios.asp
- ARCA guía LSD: https://www.arca.gob.ar/LibrodeSueldosDigital/documentos/nuevos/G05_Liq_ingreso_LSD.pdf
- Hostinger frameworks (incluye Laravel para web hosting): https://www.hostinger.com/support/which-programming-languages-and-frameworks-are-supported-at-hostinger/
- Hostinger Node.js Business: https://www.hostinger.com/support/how-to-deploy-a-nodejs-website-in-hostinger/
- Hostinger Composer: https://www.hostinger.com/support/8727597-how-to-install-composer-locally-at-hostinger/
- Hostinger Cron: https://www.hostinger.com/es/support/1583465-como-configurar-un-cron-job-en-hostinger/
