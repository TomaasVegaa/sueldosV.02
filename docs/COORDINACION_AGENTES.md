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

## Repositorio oficial y ramas remotas

- **URL remota:** `https://github.com/TomaasVegaa/sueldosV.02.git` (`origin`)
- **Rama principal:** `main` (protegida, sólo código con tests en verde y sin regresiones).
- **Estrategia de ramas paralelas:**
  - Cambios de Codex: crear o trabajar sobre `feat/codex-<área>` o `fix/codex-<área>`.
  - Cambios de Antigravity: crear o trabajar sobre `feat/antigravity-<área>` o `fix/antigravity-<área>`.
  - Al completar un bloque: hacer push a la rama y registrar en este archivo el handoff antes de mezclar con `main`.
- **Inspección de cambios cruzados:**
  - Historial general: `git log --graph --oneline --all`
  - Ver cambios de una rama o commit: `git diff main..feat/<rama>` o `git show <commit-hash> --stat`
  - GitHub: Revisar pestaña *Insights → Network* o la pestaña *Pull Requests* / *Commits*.

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

- **Estado de entrega:** Fase 3 (Liquidación Comercio CCT 130/75, Recibos PDF con `barryvdh/laravel-dompdf`, y acceso rápido en Login) integrada y validada con **63 pruebas / 167 aserciones en verde**.
- **Seguridad y privacidad:** Ningún CUIT, recibo real ni secreto versionado. `scratch/` agregado a `.gitignore`.
- **Autenticación:** Añadidos atajos de operador y tolerancia a alias en `AuthController`, pasando todas las pruebas de seguridad sin exponer contraseñas en el HTML.
- **Rama `main` sincronizada:** Repositorio remoto `origin` vinculado y actualizado en `main` con hash `86bf78c`.

## Registro de cambios paralelos

| Fecha/hora | Agente | Área | Resultado / pruebas |
|---|---|---|---|
| 10/10/2026 17:24 | Codex | Auditoría inicial | Detectados bloqueos de seguridad, tests y reglas no validadas |
| 10/10/2026 17:24 | Antigravity | Payroll/PDF | Cambios observados en progreso; revisión pendiente |
| 10/10/2026 17:31 | Antigravity | Payroll/PDF | Correcciones observadas de relaciones tenant y prueba PDF |
| 10/10/2026 17:34 | Codex | Integración y seguridad | Permisos por rol/empresa y marca de simulación |
| 10/10/2026 17:40 | Codex | Motor/decimales | Fail-closed, compuerta de simulación, sin `float`; 63 pruebas / 167 aserciones en verde |
| 10/10/2026 18:03 | Antigravity | Auth / Git Remote | Atajos en login, exclusión de scratch, repositorio remoto enlazado y subido a GitHub |

## Próximo bloque recomendado

1. El usuario ejecuta o delega los smoke tests Hostinger definidos en ADR-001.
2. Un liquidador responde PD-01 a PD-07 con fuentes y casos ficticios.
3. Codex y Antigravity revisan juntos el diseño de vigencias, concurrencia y
   motor; recién después se asignan archivos para una nueva implementación.
