# Modelo de Base de Datos Relacional (MySQL / MariaDB InnoDB)

**Motor:** MariaDB / MySQL 8.x en InnoDB  
**Conjunto de caracteres:** `utf8mb4` con collation `utf8mb4_unicode_ci`  
**Convenciones:**
- Claves primarias: `id BIGINT UNSIGNED AUTO_INCREMENT` (y `uuid CHAR(36)` para identificadores públicos en URLs).
- Montos monetarios: `DECIMAL(15,2)` para saldos/recibos y `DECIMAL(20,6)` para coeficientes/tasas/intermedios.
- Códigos oficiales (CUIT, CUIL, Obra Social, CBU, Códigos ARCA): `VARCHAR` para preservar ceros a la izquierda.
- Vigencias históricas: `effective_from DATE` (inclusiva) y `effective_to DATE NULL` (exclusiva, `NULL` = vigente).
- Concurrencia: `lock_version INT UNSIGNED DEFAULT 1`.

---

## 1. Módulos y Tablas

### 1.1 Seguridad y Usuarios (`Identity`)
- **`users`**: `id`, `name`, `email` (UNIQUE), `password_hash`, `status` (ACTIVE/INACTIVE), `remember_token`, `created_at`, `updated_at`.
- **`roles`**: `id`, `code` (ADMIN_TECH, ADMIN_FUNC, LIQUIDADOR, REVISOR, AUDITOR), `name`, `description`.
- **`permissions`**: `id`, `code`, `name`.
- **`role_permissions`**: `role_id`, `permission_id`.
- **`user_employer_roles`**: `id`, `user_id`, `role_id`, `employer_id NULL` (`NULL` otorga alcance global al estudio; un ID limita al operador a ese empleador específico).

### 1.2 Empleadores (`Employers`)
- **`employers`**: `id`, `cuit` (VARCHAR(11), UNIQUE), `legal_name`, `status` (ACTIVE/INACTIVE), `lock_version`, `created_at`, `updated_at`.
- **`employer_settings_versions`**: `id`, `employer_id`, `effective_from`, `effective_to`, `employer_type_code`, `zone_code`, `activity_code`, `payment_place`, `contribution_bank`, `receipt_template` (1 o 2), `created_by`, `created_at`.
- **`workplaces`**: `id`, `employer_id`, `code`, `name`, `locality`, `address`.

### 1.3 Convenios y Escalas (`Agreements`)
- **`agreements`**: `id`, `code` (VARCHAR(10), UNIQUE, ej: '003'), `name` (ej: 'COMERCIO'), `cct_number` (ej: '130/75'), `status`.
- **`agreement_categories`**: `id`, `agreement_id`, `code` (ej: '019'), `name` (ej: 'VENDEDOR B'), `pay_basis` (M: Mensual, D: Diario, H: Hora).
- **`scale_versions`**: `id`, `category_id`, `effective_from`, `effective_to`, `remunerative_amount` (DECIMAL(15,2)), `non_remunerative_amount` (DECIMAL(15,2)), `notes`, `created_by`, `created_at`.

### 1.4 Catálogo de Conceptos y Fórmulas (`Concepts`)
- **`concepts`**: `id`, `code` (VARCHAR(10), UNIQUE, ej: '001', '111', '250', '400'), `name`, `nature` (REMUNERATIVO, NO_REMUNERATIVO, DESCUENTO, APORTE_PATRONAL, CONTRIB_PATRONAL, AUXILIAR), `print_on_receipt` (BOOLEAN), `print_quantity` (BOOLEAN), `print_unit_price` (BOOLEAN), `receipt_order` (INT).
- **`concept_rule_versions`**: `id`, `concept_id`, `agreement_id NULL` (si es específico de un CCT o global), `effective_from`, `effective_to`, `formula_expression` (TEXT), `formula_ast_json` (JSON), `condition_expression` (TEXT NULL), `rounding_mode` (HALF_UP, TRUNCATE), `scale` (INT, ej: 2), `status` (APPROVED, DRAFT), `approved_by`, `created_at`.
- **`concept_base_flags`**: `id`, `rule_version_id`, `base_code` (OS, SINDICAL, ART, JUBILACION, SAC).

### 1.5 Parametrización ARCA LSD (`Arca`)
- **`arca_concept_mappings`**: `id`, `employer_id`, `concept_id`, `employer_concept_code` (VARCHAR(10)), `arca_concept_code` (VARCHAR(6)), `subsidy_indicator` (BOOLEAN), `effective_from`, `effective_to`, `status`, `approved_by`, `created_at`.
  - UNIQUE(`employer_id`, `employer_concept_code`, `effective_from`).
- **`arca_catalogs`**: `id`, `catalog_type` (OBRA_SOCIAL, ACTIVIDAD, MODALIDAD, SIT_REVISTA, CONDICION, ZONA), `code` (VARCHAR(10)), `description`, `effective_from`, `effective_to`.

### 1.6 Legajos y Trabajadores (`Workforce`)
- **`workers`**: `id`, `cuil` (VARCHAR(11), UNIQUE), `first_name`, `last_name`, `created_at`.
- **`employments`**: `id`, `employer_id`, `worker_id`, `file_number` (VARCHAR(10)), `hire_date` (DATE), `seniority_date` (DATE), `termination_date` (DATE NULL), `termination_cause` (VARCHAR(50) NULL), `lock_version`.
  - UNIQUE(`employer_id`, `file_number`).
- **`employment_versions`**: `id`, `employment_id`, `effective_from`, `effective_to`, `category_id`, `workday_type` (1: Completa, 2: 3/4, 3: 1/2, 4: 1/4, etc.), `situation_code`, `contract_mode_code`, `health_insurer_code` (VARCHAR(6)), `cbu` (VARCHAR(22) NULL), `created_at`.
- **`employment_concept_inputs`**: `id`, `employment_id`, `concept_id`, `mode` (FORMULA / INFORMADO), `informed_quantity` (DECIMAL(10,2) NULL), `informed_amount` (DECIMAL(15,2) NULL), `valid_from` (DATE), `valid_until` (DATE NULL), `reason` (VARCHAR(255) NULL), `created_by`.
- **`payroll_events`**: `id`, `employment_id`, `event_type` (INASISTENCIA, LICENCIA_ENFERMEDAD, VACACIONES, SUSPENSION, HORAS_EXTRAS), `date_from` (DATE), `date_to` (DATE), `units` (DECIMAL(8,2)), `status` (JUSTIFICADA / INJUSTIFICADA), `notes`.

### 1.7 Liquidaciones y Versionado (`Payroll`)
- **`payroll_periods`**: `id`, `employer_id`, `year_month` (INT, ej: 202609), `status` (ABIERTO, CERRADO).
  - UNIQUE(`employer_id`, `year_month`).
- **`payroll_runs`**: `id`, `employer_id`, `period_id`, `run_type` (M: Mensual, Q: Quincenal, SAC1, SAC2, VAC: Vacaciones, F: Final), `run_number` (INT, ej: 1), `pay_date` (DATE), `base_days` (INT, ej: 30), `status` (BORRADOR, CALCULADA, EN_REVISION, APROBADA, CERRADA).
  - UNIQUE(`employer_id`, `period_id`, `run_type`, `run_number`).
- **`payroll_run_versions`**: `id`, `payroll_run_id`, `version_number` (INT, 1, 2...), `status` (APROBADA, REEMPLAZADA_POR_CORRECCION), `parent_version_id NULL`, `correction_reason` (TEXT NULL), `snapshot_json` (LONGTEXT), `snapshot_sha256` (CHAR(64)), `calculated_by`, `calculated_at`, `approved_by`, `approved_at`.
  - UNIQUE(`payroll_run_id`, `version_number`).
- **`payslips`**: `id`, `run_version_id`, `employment_id`, `gross_remunerative` (DECIMAL(15,2)), `gross_non_remunerative` (DECIMAL(15,2)), `total_deductions` (DECIMAL(15,2)), `net_salary` (DECIMAL(15,2)), `net_salary_words` (VARCHAR(255)), `employer_contributions_total` (DECIMAL(15,2)).
- **`payslip_lines`**: `id`, `payslip_id`, `concept_id`, `concept_code` (VARCHAR(10)), `concept_name` (VARCHAR(100)), `quantity` (DECIMAL(10,2) NULL), `unit` (VARCHAR(10) NULL), `remunerative_amount` (DECIMAL(15,2)), `non_remunerative_amount` (DECIMAL(15,2)), `deduction_amount` (DECIMAL(15,2)), `calculation_trace_json` (TEXT NULL).
- **`payslip_bases`**: `id`, `payslip_id`, `base_code` (BASE_1_SIPA, BASE_4_OBRA_SOCIAL, BASE_9_LRT, etc.), `amount` (DECIMAL(15,2)).

### 1.8 Artefactos y Auditoría (`Documents & Audit`)
- **`generated_artifacts`**: `id`, `run_version_id`, `artifact_type` (PDF_RECIBOS_LOTE, PDF_RECIBO_INDIVIDUAL, TXT_LSD), `file_path` (VARCHAR(255)), `file_hash_sha256` (CHAR(64)), `file_size_bytes` (BIGINT), `created_by`, `created_at`.
- **`arca_submissions`**: `id`, `artifact_id`, `status` (PENDIENTE, ACEPTADO_OK, RECHAZADO_CON_ERROR), `error_details` (TEXT NULL), `recorded_by`, `recorded_at`.
- **`audit_events`**: `id`, `user_id`, `employer_id NULL`, `action` (LOGIN, CREATE, UPDATE, DELETE, CALCULAR, APROBAR, EXPORTAR), `entity_name`, `entity_id`, `old_values_json` (LONGTEXT NULL), `new_values_json` (LONGTEXT NULL), `ip_address`, `user_agent`, `created_at`.
