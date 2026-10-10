# Estado de requisitos y compuertas

**Corte:** 10/10/2026  
**Estado global:** PROTOTIPO LOCAL — NO APTO PARA LIQUIDACIONES REALES

Este documento registra evidencia técnica; no certifica corrección legal,
contable ni aceptación por ARCA.

## Compuertas

| Fase | Estado | Motivo principal |
|---|---|---|
| 0 — Descubrimiento y arquitectura | INCOMPLETA | Faltan evidencia Hostinger, comparación desplegada y cierre de decisiones PD-01 a PD-07 |
| 1 — Seguridad y entidades base | PARCIAL / NO ACEPTADA | RBAC y tenant tienen regresiones en verde; faltan concurrencia real, vigencias y aceptación humana |
| 2 — Motor de reglas | PARCIAL / BLOQUEADA | AST sólo aritmético y fail-closed; faltan funciones/tipos aprobados y las fórmulas no están validadas |
| 3 — Mensual Comercio y PDF | PROTOTIPO / BLOQUEADA | UI y PDF funcionan como simulación; tasas, fórmulas, casos y recibo no fueron aceptados por experto |
| 4 — ARCA LSD | NO INICIADA | Sin mapeos versionados por empleador ni serializador validado |
| 5–6 | NO INICIADAS | Dependen de las compuertas anteriores |

## Controles técnicos resueltos en esta iteración

- **SEG-01:** permisos en servidor aplicados a módulos existentes y acciones de
  nómina/PDF, con pruebas negativas por rol y empleador.
- **SEG-02:** empresa activa revalidada contra las empresas autorizadas y tenant
  cerrado por defecto.
- **SEG-03:** usuarios y contraseña de demostración retirados del login público;
  usuario inactivo y rate limit contemplados en el controlador.
- **PRV-01:** `.env`, PDFs, TXT, DBF, videos, ZIP y bases locales excluidos de Git.
- **TST-01:** PHPUnit forzado a SQLite `:memory:` con migración y seed por prueba.

## Bloqueos prioritarios abiertos

- **DAT-01:** implementar vigencias sin solapamiento y semántica de fin exclusiva.
- **CON-01:** bloqueo optimista real mediante comparación de `lock_version`.
- **FOR-01:** ampliar el AST con las funciones y tipos que se aprueben; las
  variables y condiciones desconocidas ya fallan de forma cerrada.
- **FOR-02:** control continuo: no reintroducir `float`/`number_format` en
  importes. Servicio, sugerencias, conversión a letras y vistas usan decimales exactos.
- **NEG-01:** validar diccionario, conceptos, escalas, porcentajes y redondeos con liquidador experto.
- **NEG-02:** retirar tasas patronales hardcodeadas; modelarlas con fuente,
  vigencia y aprobación antes de activarlas.
- **NEG-03:** reparar el catálogo sembrado: hoy contiene fórmulas que referencian
  conceptos ausentes para el conjunto aplicable (`LQ015`, `LQ324`, entre otros).
  No convertir esas referencias a cero sin una decisión profesional.
- **ARQ-01:** ejecutar los dos smoke tests en el plan Hostinger contratado y
  obtener aprobación expresa del ADR.
- **DEP-01:** incorporar PHPStan y una línea base revisada; `brick/math` ya es
  dependencia directa y DomPDF quedó fijado a `^3.1`.

## Evidencia verificada

- PHP 8.2.12, Laravel 12.69.3 y Livewire 4.4.7 cargan localmente.
- Composer 2.10.3 y los requisitos de plataforma instalados pasan localmente.
- La suite completa pasa con **63 pruebas y 167 aserciones** sobre SQLite en memoria.
- Hay regresiones de autenticación, permisos, aislamiento multiempresa y
  denegación de PDFs entre empleadores.
- Pantalla y PDFs de nómina muestran `SIMULACIÓN NO VALIDADA`.
- El cálculo no validado está deshabilitado por defecto y sólo se activa en la
  suite aislada mediante una variable explícita.
- Las pruebas de liquidación usan conceptos sintéticos; el catálogo de Comercio
  no se usa como oráculo de corrección.
- `pint --test`, `composer validate --strict`, requisitos de plataforma y el
  build de Vite pasan localmente.
- Git quedó inicializado; los commits de línea base, seguridad y configuración
  permiten revisar el trabajo incremental.
- La documentación pública actual de Hostinger admite PHP y también Next.js en
  Business, pero el plan concreto todavía no fue probado.

## Decisiones humanas aún necesarias

Se mantienen vigentes las preguntas de `PREGUNTAS_Y_DECISIONES.md`, en especial
PD-01 a PD-07 y PD-13. Una regla sólo puede pasar de `BORRADOR` a `APROBADA`
cuando tenga fuente, vigencia, casos de prueba y aceptación registrada.
