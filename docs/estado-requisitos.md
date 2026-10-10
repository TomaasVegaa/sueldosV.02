# Estado de requisitos y compuertas

**Corte:** 10/10/2026  
**Estado global:** PROTOTIPO LOCAL — NO APTO PARA LIQUIDACIONES REALES

Este documento registra evidencia técnica; no certifica corrección legal,
contable ni aceptación por ARCA.

## Compuertas

| Fase | Estado | Motivo principal |
|---|---|---|
| 0 — Descubrimiento y arquitectura | INCOMPLETA | Faltan evidencia Hostinger, comparación desplegada y cierre de decisiones PD-01 a PD-07 |
| 1 — Seguridad y entidades base | NO ACEPTADA | RBAC no aplicado, tenant fail-open, concurrencia no probada y tests no aislados |
| 2 — Motor de reglas | PARCIAL / BLOQUEADA | AST sólo aritmético; variables desconocidas valen cero; fórmulas sin aprobación |
| 3 — Mensual Comercio y PDF | EN DESARROLLO / BLOQUEADA | Código en construcción; casos y reglas no aceptados por experto |
| 4 — ARCA LSD | NO INICIADA | Sin mapeos versionados por empleador ni serializador validado |
| 5–6 | NO INICIADAS | Dependen de las compuertas anteriores |

## Bloqueos prioritarios

- **SEG-01:** aplicar permisos en servidor a rutas y acciones Livewire.
- **SEG-02:** validar siempre la empresa activa contra las empresas autorizadas.
- **SEG-03:** retirar usuarios/contraseña de demostración de la pantalla pública.
- **PRV-01:** mantener fuera de Git PDFs, TXT, DBF, videos, bases y `.env`.
- **TST-01:** ejecutar pruebas con SQLite en memoria o base MySQL exclusiva.
- **DAT-01:** implementar vigencias sin solapamiento y semántica de fin exclusiva.
- **CON-01:** bloqueo optimista real mediante comparación de `lock_version`.
- **FOR-01:** no aceptar variables inexistentes; agregar condiciones y tipos.
- **FOR-02:** eliminar `float` de importes y conversiones monetarias.
- **NEG-01:** validar diccionario, conceptos, escalas, porcentajes y redondeos con liquidador experto.
- **ARQ-01:** completar evidencia de Hostinger y actualizar ADR a las versiones reales.

## Evidencia verificada

- PHP 8.2.12, Laravel 12.69.3 y Livewire 4.4.7 cargan localmente.
- Las rutas existentes responden bajo Laravel.
- Los archivos PHP inspeccionados pasan `php -l`.
- Seis pruebas unitarias del motor pasan; la cobertura funcional es todavía insuficiente.
- La suite feature original apunta a `sueldos_db` y puede modificarla; no constituye evidencia reproducible.
- `pint --test` detecta formato pendiente en numerosos archivos.
- No había repositorio Git al comenzar esta intervención.

## Decisiones humanas aún necesarias

Se mantienen vigentes las preguntas de `PREGUNTAS_Y_DECISIONES.md`, en especial
PD-01 a PD-07 y PD-13. Una regla sólo puede pasar de `BORRADOR` a `APROBADA`
cuando tenga fuente, vigencia, casos de prueba y aceptación registrada.
