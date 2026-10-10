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
| Documentación canónica, Git y privacidad | Codex | `AGENTS.md`, `README.md`, `.gitignore`, `docs/*` | EN CURSO |
| Autenticación, RBAC, tenant y tests aislados | Codex | `AuthController`, `EmployerContext`, pruebas/configuración | PENDIENTE |
| Liquidación, recibos y UI de nómina | Antigravity | `app/Domain/Payroll`, `app/Livewire/Payroll`, vistas de payroll, modelos de liquidación | EN CURSO / NO VALIDADO |
| `routes/web.php` y layout global | COMPARTIDO | Requiere handoff explícito antes de editar | BLOQUEADO PARA EDICIÓN SIMULTÁNEA |
| Exportador LSD y reglas legales | Nadie | No comenzar hasta resolver decisiones bloqueantes | BLOQUEADO |

## Handoff actual

### Codex → Antigravity

- No asumir que la Fase 1 está aceptada.
- No agregar fórmulas, porcentajes, indemnizaciones ni bases legales nuevas.
- El motor actual no soporta todavía condiciones ni funciones documentadas.
- Los archivos de payroll creados el 10/10/2026 deben permanecer identificados
  como simulación y requieren revisión posterior de seguridad/decimales.
- Antes del próximo bloque, revisar este documento y `estado-requisitos.md`.

### Antigravity → Codex

Pendiente: registrar qué archivos de payroll fueron creados, qué pruebas pasan,
qué supuestos se usaron y qué decisiones humanas faltan.

## Registro de cambios paralelos

| Fecha/hora | Agente | Área | Resultado / pruebas |
|---|---|---|---|
| 10/10/2026 17:24 | Codex | Auditoría inicial | Detectados bloqueos de seguridad, tests y reglas no validadas |
| 10/10/2026 17:24 | Antigravity | Payroll/PDF | Cambios observados en progreso; revisión pendiente |
