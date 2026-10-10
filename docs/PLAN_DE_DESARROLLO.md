# Plan de Desarrollo e Hitos de Ejecución

**Ambiente:** Local XAMPP (`PHP 8.2.12`, `MySQL 3306`, `Composer 2.10.3`).  
**Regla de trabajo:** Entregas incrementales. Cada fase se valida con pruebas automatizadas y aceptación humana antes de avanzar a la siguiente.

---

## Cronograma por Fases

### Fase 1: Scaffolding, Seguridad y Entidades Base
- **Objetivo:** Tener la aplicación Laravel corriendo en XAMPP local con base de datos, autenticación, roles, auditoría y administración de empleadores y legajos.
- **Entregables:**
  1. Inicialización limpia de Laravel 11 en el proyecto con Livewire 3 y Tailwind CSS.
  2. Conexión a MySQL en XAMPP (`sueldos_db`).
  3. Migraciones base: `users`, `roles`, `employers`, `employer_settings_versions`, `workplaces`, `workers`, `employments`, `employment_versions`, `audit_events`.
  4. Seeders de catálogos oficiales (Obras Sociales extraídas de `Obrasdgi.pdf`, tablas oficiales ARCA de actividad, modalidad y situación).
  5. Interfaz web: Login individual con registro de auditoría, panel con selector de empresa activa, CRUD de empleadores y legajos (con validación de CUIT/CUIL).
  6. Tests de aislamiento: Verificación de que el usuario de la Empresa A no puede consultar ni modificar datos de la Empresa B.

### Fase 2: Convenios, Escalas y Motor de Fórmulas Seguro
- **Objetivo:** Motor de cálculo independiente en `src/Domain/Engine` con pruebas unitarias exhaustivas.
- **Entregables:**
  1. Migraciones de convenios, categorías, escalas con vigencia y catálogo de conceptos.
  2. Implementación del Lexer, Parser y AST tipado en PHP puro (sin `eval`).
  3. Implementación del Grafo de Dependencias con ordenamiento topológico y detección de ciclos en referencias `LQxxx`.
  4. Diccionario de variables integrado (`SB`, `VI`, `IM`, `AN`, `TH`, `TS`, `TD`, `TN`, `OSO`, `SIT`, `SID`, `SIM`).
  5. Suite de pruebas unitarias (`T-F01` a `T-F12`): Sueldo básico proporcional, inasistencias con signo negativo, antigüedad, presentismo condicionado (`CQ004 < 0.5`), aportes (Jubilación, Ley 19032, OS OSECAC/Otras).

### Fase 3: Liquidación Mensual (Comercio) y Recibos PDF
- **Objetivo:** Proceso completo de liquidación mensual para Comercio y generación de recibos PDF legales.
- **Entregables:**
  1. Pantalla interactiva en Livewire de carga de novedades y valores informados por legajo.
  2. Preliquidación instantánea individual con desglose de conceptos y recálculo en cascada.
  3. Ejecución de cálculo masivo por período con control de estados (`BORRADOR -> CALCULADA -> EN_REVISION -> APROBADA`).
  4. Snapshot inmutable con hash SHA-256 de las liquidaciones aprobadas.
  5. Plantilla de Recibo PDF A4 (art. 140 LCT) que reproduce la estructura de los recibos de referencia aportados:
     - Cabecera con datos patronales y del trabajador (incluyendo último depósito de aportes).
     - Columnas: Código, Concepto, Unidad, Cantidad, Haberes Remunerativos, No Remunerativos y Descuentos.
     - Totales, Neto en números y en letras ("Son Pesos...").
     - Detalle de costo y contribuciones patronales al pie.
  6. Reconciliación exacta contra los casos de prueba provistos en los PDFs de Septiembre 2026.

### Fase 4: Exportador y Validador LSD ARCA
- **Objetivo:** Generación del archivo TXT de ancho fijo para Libro de Sueldos Digital y validador local.
- **Entregables:**
  1. Mapeador de conceptos de empleador a códigos oficiales de ARCA por empleador (`arca_concept_mappings`).
  2. Serializador de registros `01` (35 chars), `02` (115 chars), `03` (51 chars) y `04` (370 chars con acumulación de bases imponibles).
  3. Validador estructural integrado que garantiza anchos, codificación ASCII, CRLF y congruencia de totales antes de permitir la descarga.
  4. Prueba de serialización contra el archivo de referencia `SIJP27167840472.txt`.
  5. Registro de descargas y auditoría (marcado manual del estado de aceptación de ARCA).

### Fase 5: Liquidaciones Especiales, Correcciones y Multiconvenio
- **Objetivo:** Liquidaciones de SAC, Vacaciones, Liquidación Final y gestión de versiones correctivas.
- **Entregables:**
  1. Flujo de corrección histórica (`v1 -> v2`) con motivo, comparativa visual (diff) y preservación inmutable del histórico.
  2. Módulos de SAC (1.ª y 2.ª cuota) y Liquidación Final.
  3. Incorporación de convenios adicionales según requerimiento del estudio.

### Fase 6: Preparación para Despliegue en Hostinger Business
- **Objetivo:** Pruebas de integración, empaquetado y puesta a punto para el hosting.
- **Entregables:**
  1. Configuración de ejecución segura: document root apuntando exclusivamente a `public/`.
  2. Configuración de tareas programadas (Cron de Hostinger) para colas y respaldos automáticos.
  3. Script de respaldo de base de datos cifrado y procedimiento documentado de restauración.
  4. Guía de despliegue paso a paso para el estudio contable.
