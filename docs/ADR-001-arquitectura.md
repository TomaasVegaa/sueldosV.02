# ADR-001 — Decisión de Arquitectura: Monolito Modular en PHP (Laravel)

**Estado:** APROBADO por el usuario (09/10/2026)  
**Decisión técnica:** PHP / Laravel + MySQL/MariaDB + Livewire  
**Ambiente inicial:** Local (XAMPP: PHP 8.2.12, Composer 2.10, MySQL 3306)  
**Ambiente destino:** Hostinger Business Web Hosting  

---

## 1. Contexto y Decisión

El sistema es una aplicación web interna para un estudio contable que liquida haberes para 80–100 empleadores, con 5 operadores simultáneos en 2 oficinas, versionado histórico inmutable, recibos PDF legales y exportación LSD a ARCA.

El usuario confirmó que:
1. Se desarrollará en **PHP** para aprovechar el plan contratado en **Hostinger Business**.
2. La primera etapa se desarrollará y probará en **entorno local sobre XAMPP** (donde ya se verificó PHP 8.2.12, Composer 2.10.3 y MySQL activo en puerto 3306).
3. La parametrización de Libro de Sueldos Digital (LSD) se define **por empleador** (con plantillas predeterminadas por convenio).
4. Las 15 funciones no reconocidas observadas en el software anterior (como aumentos masivos automáticos, escalafones no usados, etc.) **no forman parte del alcance**.

---

## 2. Pila Tecnológica Seleccionada

| Capa / Componente | Tecnología elegida | Justificación |
|---|---|---|
| **Backend / Framework** | **PHP 8.2+ con Laravel 11** | Compatible nativo con Hostinger Business y XAMPP local. ORM Eloquent, transacciones, migraciones y RBAC integrados. |
| **Base de Datos** | **MySQL / MariaDB (InnoDB)** | Transaccional, foreign keys compuestas, triggers para inmutabilidad de liquidaciones cerradas. |
| **Interfaz de Usuario** | **Blade + Livewire 3 + Alpine.js** | Reactividad moderna en servidor sin la sobrecarga ni el desacoplamiento de una SPA separada. Grillas ágiles de liquidación. |
| **Estilos y Componentes** | **Tailwind CSS + Blade Components** | Diseño 100% original, moderno, sobrio y optimizado para escritorio/oficina (sin copiar el software legado). |
| **Cálculo Monetario** | `brick/math` (`BigDecimal`) + `DECIMAL(15,2)` | Prohibido el uso de `float` en importes para evitar errores de coma flotante. Redondeo explícito por regla. |
| **Motor de Fórmulas** | **Parser AST seguro en PHP puro** | Sin `eval` ni `create_function`. Parser Pratt con validación léxica, sintáctica, chequeo de tipos y detección de ciclos en dependencias (`LQxxx`). |
| **Generación de PDFs** | **dompdf / barryvdh/laravel-dompdf** | Renderizado en servidor desde plantillas Blade HTML/CSS. Sin dependencias externas pesadas (como Puppeteer o Chrome headless que no corren en hosting compartido). |
| **Exportación ARCA LSD** | **Serializador de ancho fijo nativo** | Generación byte a byte validada contra especificaciones oficiales (registros 01, 02, 03, 04) con codificación ASCII / CRLF. |
| **Almacenamiento de Archivos** | **Storage privado (`storage/app/private`)** | PDFs y TXTs nunca expuestos en `public_html`. Descarga únicamente mediante endpoints autenticados y autorizados por rol y empleador. |
| **Pruebas y Calidad** | **Pest PHP / PHPUnit + PHPStan (Nivel 8)** | Suite de tests unitarios (motor matemático, serializador LSD byte a byte) y de integración (aislamiento multiempresa, concurrencia). |

---

## 3. Principios de Diseño y Seguridad

1. **Aislamiento Multiempresa Estricto:** Toda tabla de datos operativos contiene `employer_id`. Las consultas se filtran mediante Global Scopes y Policies de Laravel. Un operador no autorizado para la empresa B recibe `403 Forbidden` / `404 Not Found`.
2. **Inmutabilidad de Liquidaciones Cerradas:** Una liquidación con estado `APROBADA` o `CERRADA` no permite `UPDATE` ni `DELETE`. Esto se asegura a nivel de aplicación y mediante **triggers de base de datos** en MySQL. Cualquier corrección genera una nueva versión `N+1` vinculada a la anterior.
3. **Reproducibilidad Histórica:** Cada versión de liquidación almacena un **snapshot JSON** con las variables de entrada, las versiones de reglas aplicadas y las escalas salariales utilizadas, sellado con un hash SHA-256. Un cambio de escala en un mes posterior nunca recalcula ni altera recibos de meses anteriores.
4. **Control de Concurrencia:** Columna `lock_version` (bloqueo optimista) en liquidaciones y legajos para evitar sobreescritura accidental entre los 5 operadores simultáneos.
5. **Auditoría Append-Only:** Tabla `audit_events` que registra quién, cuándo, desde qué IP y qué valores anteriores y nuevos tuvo cada operación crítica.
