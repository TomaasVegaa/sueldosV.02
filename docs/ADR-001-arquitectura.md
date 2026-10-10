# ADR-001 — Arquitectura y selección de stack

**Estado:** PROPUESTA PROVISIONAL — PENDIENTE DE PRUEBA EN HOSTINGER Y APROBACIÓN HUMANA
**Última revisión:** 10/10/2026
**Alcance:** arquitectura del producto; no valida reglas laborales, contables ni ARCA

## 1. Contexto

La aplicación será de uso interno para aproximadamente 80–100 empleadores,
cinco operadores simultáneos y dos oficinas. Necesita aislamiento por empleador,
versionado histórico, auditoría, cálculo decimal exacto, PDFs privados y TXT para
carga manual en ARCA.

El repositorio ya contiene un prototipo Laravel. Esto es evidencia de avance, no
una aprobación retrospectiva del stack. La afirmación anterior de que el usuario
había aprobado Laravel el 09/10/2026 no tiene evidencia registrada y se retira.

## 2. Estado comprobado

### Entorno local

- PHP 8.2.12 y Composer 2.10.3.
- Laravel 12.69.3 y Livewire 4.4.7, no Laravel 11 / Livewire 3.
- MySQL disponible en XAMPP; las pruebas automatizadas usan SQLite en memoria
  para no modificar `sueldos_db`.
- `brick/math` 0.14.8 está declarado de forma directa para importes.
- `barryvdh/laravel-dompdf` está fijado a la serie compatible `^3.1`; el lock
  actual instala 3.1.2.
- `composer check-platform-reqs` pasa en el equipo local.
- Suite al corte: 63 pruebas, 167 aserciones, todas en verde.

### Evidencia pública de Hostinger consultada el 10/10/2026

- Hostinger informa PHP 8.2–8.5 para sitios nuevos y PHP 8.3 como valor inicial:
  <https://www.hostinger.com/support/1575755-how-to-change-the-php-version-of-your-hostinger-hosting-plan/>.
- Composer 2 y acceso por SSH están documentados para Web Business:
  <https://www.hostinger.com/support/5792078-how-to-use-composer-at-hostinger/>.
- Hostinger informa soporte administrado para aplicaciones Node.js y Next.js en
  Business Web Hosting, con Node 18/20/22/24:
  <https://www.hostinger.com/support/how-to-deploy-a-nodejs-website-in-hostinger/>.
- La disponibilidad pública no demuestra las versiones, extensiones, cuotas ni
  comportamiento del plan concreto contratado. Eso requiere una prueba en hPanel.

## 3. Alternativas

| Criterio | Laravel / PHP | Next.js / TypeScript |
|---|---|---|
| Encaje oficial en Business | PHP y Composer documentados | Next.js y Node documentados actualmente |
| Despliegue | Aplicación PHP tradicional; hay que comprobar que sólo `public/` quede expuesto | Flujo administrado por GitHub/ZIP; npm y reinicios dependen del mecanismo de Hostinger |
| Modelo de aplicación | Monolito modular natural con Blade/Livewire | Monolito modular posible con UI React y servidor Node |
| Cálculo exacto | `brick/math` + `DECIMAL`; no usar `float` | biblioteca decimal + `DECIMAL`; no usar `number` para dinero |
| Operación en hosting compartido | No depender de workers persistentes ni Redis | No asumir procesos auxiliares, shell npm libre ni recursos exclusivos |
| Madurez en este repositorio | Prototipo existente con migraciones y pruebas | No hay prototipo local ni migración equivalente |
| Costo de cambio hoy | Menor, sujeto a corregir deuda del prototipo | Alto: reimplementar y volver a verificar seguridad y dominio |
| Riesgo principal | Confundir que “funciona local” con que está listo para Hostinger o producción | Agregar complejidad y reescritura sin una ventaja funcional demostrada |

Ambas alternativas son técnicamente viables según la información pública. No se
descarta Next.js por incompatibilidad: esa premisa quedó desactualizada.

## 4. Decisión propuesta, todavía no aceptada

Continuar **provisionalmente** con un monolito modular Laravel 12 + Livewire 4 +
MySQL/MariaDB InnoDB, porque ya existe una base verificable y la aplicación no
requiere una SPA para cumplir su alcance. La decisión se vuelve `ACEPTADA` sólo
si se cumplen las pruebas de la sección 5 y el cliente lo confirma expresamente.

No avanzar a reglas salariales reales ni a exportación ARCA mientras el stack y
las preguntas bloqueantes de negocio sigan sin aceptación.

## 5. Prueba obligatoria en el plan contratado

Usar datos ficticios, una base descartable y subdominios de prueba. No desplegar
este prototipo ni datos reales.

| Verificación | Laravel vacío | Next.js vacío | Criterio de aceptación |
|---|---|---|---|
| Runtime | `php -v`, extensiones y `composer2` | versión Node y build Next | Versiones compatibles y reproducibles |
| Despliegue | document root en `public/`; `.env` y `storage` inaccesibles por HTTP | deploy privado desde GitHub/ZIP, variables y reinicio | Ningún secreto/archivo privado público |
| Base | migración y rollback sobre MySQL de prueba | migración y rollback sobre MySQL de prueba | Transacción, charset y zona horaria verificados |
| Proceso pesado | PDF ficticio y tarea cron corta | PDF ficticio y tarea programada equivalente | Termina dentro de límites medidos |
| Operación | logs, backup/restore y actualización | logs, rollback de build y actualización | Procedimiento repetible por el estudio |
| Medición | tiempo, memoria, errores y cuota | tiempo, memoria, errores y cuota | Registrar resultados comparables, no impresiones |

La prueba queda **PENDIENTE** porque no se proporcionó acceso al plan Hostinger.
No hace falta compartir contraseñas: puede ejecutarla el titular siguiendo un
guion y entregar capturas sin secretos.

## 6. Arquitectura si se acepta Laravel

- Monolito modular: Identidad/RBAC, Empresas, Legajos, Convenios, Motor de
  reglas, Liquidaciones, Documentos, ARCA y Auditoría.
- Autorización en servidor por permiso y `employer_id`; alcance multiempresa
  cerrado por defecto.
- `DECIMAL` en base y `BigDecimal` en dominio, con redondeo explícito por regla.
- Parser/AST limitado, sin `eval`, con variables tipadas y error ante incógnitas.
- Versiones efectivas e inmutables, snapshot de entradas/AST y hash SHA-256.
- PDFs y TXT en almacenamiento privado, descargados sólo mediante controladores
  autenticados y autorizados.
- Tareas largas diseñadas para ejecución acotada/cron; ninguna dependencia de un
  worker persistente hasta demostrar que el hosting lo admite.

## 7. Consecuencias, riesgos y reversión

- El prototipo Laravel puede seguir recibiendo mejoras de seguridad y
  documentación, pero toda salida de nómina seguirá marcada como simulación.
- Antes de una entrega desplegable hay que agregar análisis estático; el formato
  actual ya pasa Pint.
- Las relaciones que omiten scopes sólo son aceptables detrás de autorización y
  filtros explícitos por empleador; deben tener pruebas negativas.
- Si Laravel no supera la prueba de hosting, se conserva el dominio y el esquema
  documentados, se detiene la UI y se prueba Next.js o un plan Cloud/VPS. No se
  migran reglas laborales no aprobadas para justificar el cambio.

## 8. Aprobación requerida

Registrar aquí, mediante commit, fecha y responsable:

- [ ] Resultados reproducibles del smoke test Laravel.
- [ ] Resultados reproducibles del smoke test Next.js.
- [ ] Costos/cuotas del plan concreto y alternativa si no alcanza.
- [ ] Elección expresa del cliente.
- [ ] Cambio de estado de este ADR a `ACEPTADA` o `RECHAZADA`.
