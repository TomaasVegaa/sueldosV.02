# Coordinación de agentes

**Actualizado:** 10/10/2026  
**Objetivo:** permitir trabajo paralelo sin sobrescribir archivos ni convertir
hipótesis laborales en reglas productivas.

## Reglas de operación

1. Leer `AGENTS.md`, `docs/estado-requisitos.md` y este archivo antes de editar.
2. Ejecutar `git status --short` antes y después de cada bloque.
3. Un archivo tiene un solo responsable activo. Para transferirlo, registrar el
   handoff antes de editar.
4. No mezclar en un mismo commit documentación, seguridad y reglas salariales.
5. No agregar ni activar fórmulas laborales sin decisión humana registrada.
6. Todo cálculo visible debe decir **SIMULACIÓN NO VALIDADA** hasta su aceptación.
7. No ejecutar pruebas contra `sueldos_db` ni otra base con datos de trabajo.

## Asignación temporal

| Área | Responsable | Archivos principales | Estado |
|---|---|---|---|
| Documentación canónica, Git y privacidad | Codex | `AGENTS.md`, `README.md`, `.gitignore`, `docs/*` | BLOQUE COMPLETADO |
| Autenticación, RBAC, tenant y tests aislados | Codex | `AuthController`, `EmployerContext`, pruebas/configuración | BLOQUE COMPLETADO |
| Liquidación, recibos y UI de nómina | Sin responsable activo | `app/Domain/Payroll`, `app/Livewire/Payroll`, vistas de payroll, modelos de liquidación | PAUSADO / NO VALIDADO |
| `routes/web.php` y layout global | COMPARTIDO | Requiere handoff explícito antes de editar | BLOQUEADO PARA EDICIÓN SIMULTÁNEA |
| Exportador LSD y reglas legales | Nadie | No comenzar hasta resolver decisiones bloqueantes | BLOQUEADO |

## Handoff actual

### Codex → Antigravity

- Partir del último commit y ejecutar `git status --short` antes de editar.
- No asumir que la Fase 1 está aceptada aunque la suite esté en verde.
- No agregar fórmulas, porcentajes, indemnizaciones ni bases legales nuevas.
- No quitar la marca `SIMULACIÓN NO VALIDADA` de pantalla ni PDFs.
- La autorización de nómina/PDF y las pruebas negativas ya están integradas; no
  usar `withoutGlobalScopes()` sin filtro explícito y prueba de acceso cruzado.
- El motor y el servicio conservan bloqueos documentados en
  `estado-requisitos.md`; proponer diseño antes de modificarlos.
- Antes del próximo bloque, revisar este documento y `estado-requisitos.md`.

### Antigravity → Codex

No se recibió un handoff escrito. Codex integró los cambios observables de
relaciones/servicio/PDF después de que los archivos quedaron estables. Sigue
pendiente que Antigravity documente supuestos y fuentes de las fórmulas.

## Registro de cambios paralelos

| Fecha/hora | Agente | Área | Resultado / pruebas |
|---|---|---|---|
| 10/10/2026 17:24 | Codex | Auditoría inicial | Detectados bloqueos de seguridad, tests y reglas no validadas |
| 10/10/2026 17:24 | Antigravity | Payroll/PDF | Cambios observados en progreso; revisión pendiente |
| 10/10/2026 17:31 | Antigravity | Payroll/PDF | Correcciones observadas de relaciones tenant y prueba PDF; sin handoff escrito |
| 10/10/2026 17:34 | Codex | Integración y seguridad | Permisos por rol/empresa y marca de simulación |
| 10/10/2026 17:40 | Codex | Motor/decimales | Fail-closed, compuerta de simulación, sin `float`; 63 pruebas / 167 aserciones en verde |

## Próximo bloque recomendado

1. El usuario ejecuta o delega los smoke tests Hostinger definidos en ADR-001.
2. Un liquidador responde PD-01 a PD-07 con fuentes y casos ficticios.
3. Codex y Antigravity revisan juntos el diseño de vigencias, concurrencia y
   motor; recién después se asignan archivos para una nueva implementación.
