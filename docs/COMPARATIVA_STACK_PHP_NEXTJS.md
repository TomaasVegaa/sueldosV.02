# Comparativa obligatoria del stack — PHP/Laravel vs Next.js/TypeScript

**Estado:** decisión pendiente, versión 0.2, 09/10/2026. **No iniciar la implementación del producto antes de aprobar ADR-001.**

## Objetivo de comparación
Implementar el MISMO SRS: aplicación interna para 80–100 empleadores, 5 usuarios simultáneos en 2 oficinas, múltiples convenios, motor de cálculo auditable, novedades, correcciones históricas, recibos PDF y exportación manual TXT al LSD de ARCA. El stack **no modifica** los cálculos, los permisos ni las reglas de versión.

## Dos opciones obligatorias

| Tema | Opción A: PHP con Laravel | Opción B: Next.js con TypeScript |
|---|---|---|
| Aplicación | Monolito Laravel con motor de dominio independiente | Monolito Next.js con servidor/controladores y dominio separado |
| Interfaz | Blade + Livewire + Alpine.js; alternativa Inertia + Vue/React con justificación | React/Next.js; UI original, formularios y tablas |
| Base de datos | MySQL/MariaDB InnoDB con Eloquent, migraciones y transacciones | MySQL/MariaDB InnoDB con ORM, migraciones y transacciones |
| Cálculo monetario | `DECIMAL` + BCMath o `brick/math`; prohibir `float` en importes | `DECIMAL` + biblioteca decimal; prohibir `number` flotante |
| Fórmulas editables | Parser/AST y evaluador propio con lista blanca; prohibido `eval` | Parser/AST y evaluador propio con lista blanca; prohibido `eval`/`Function` |
| PDF / TXT | Librería PHP PDF verificable y TXT de bytes controlados | Librería JS PDF verificable y TXT de bytes controlados |
| Pruebas | PHPUnit/Pest, PHPStan y lint; pruebas integradas de archivo y DB | Vitest/Jest, TypeScript strict, lint; pruebas de archivo y DB |
| Hostinger Business | Hostinger declara soporte Laravel para hosting web. Comprobar PHP/extensiones, Composer, CLI, document root, cron y límites | Comprobar proceso Node/Next, método de despliegue, cuotas y trabajos programados reales en hPanel |
| Riesgo típico | Jobs largos y workers residentes en hosting compartido; protección de carpeta pública | Procesos y memoria Node, límites de hosting, actualizaciones y builds |
| Seguridad y versionado | Roles, sesión segura, auditoría, snapshots inmutables, DB transaccional | **Las mismas exigencias**, aplicadas en servidor |

**No elegir por gusto del agente**: ambos son técnicamente viables en principio, pero la elección depende del alojamiento y de pruebas prácticas. Laravel puede aprovechar una infraestructura PHP/MySQL tradicional sin mantener un servidor Node para cada solicitud, pero **no garantiza por sí mismo** menos trabajo de mantenimiento o mejor rendimiento. Next.js puede funcionar bien si el plan admite la aplicación y sus recursos.

## Evidencia que debe conseguirse en fase 0
1. **PHP:** versión real de hPanel y CLI, extensiones requeridas por versión de Laravel elegida, Composer 2, SSH, permisos de escritura y directorio público seguro. No instalar una versión de Laravel antigua solo porque figura en una guía desactualizada.
2. **Node:** disponibilidad y versión Node, despliegue y reinicio, cuota de RAM/CPU, límites para requests largas y método de tareas programadas.
3. **MySQL:** cantidad y límites de conexiones, migraciones, InnoDB, índices, transacciones, copias y restauración verificable.
4. **En ambos:** cinco sesiones simultáneas sobre empresas distintas; intentos de acceso cruzado bloqueados; PDF privado inaccesible por URL pública; TXT generado byte a byte; pruebas de concurrencia/versionado; no usar datos identificables de trabajadores.
5. **Cargas por lotes:** simular 100 empleados por empleador y preparar prueba mayor según volumen real. Medir tiempo y memoria de cálculo y generación de PDFs/TXT; determinar si se necesita cron/lotes o VPS.
6. **Costo total:** costo de almacenamiento externo/backup, desarrollo, mantenimiento, dependencias y actualizaciones (PHP, Composer, Node, librerías PDF).

## Entregable obligatorio: ADR-001
Crear `docs/ADR-001-arquitectura.md` con: contexto, hipótesis, evidencia y resultados medidos para A y B, restricciones verificadas en Hostinger, alternativas descartadas, riesgos, decisión propuesta y pendiente de aprobación, consecuencias y plan de reversión. **No generar scaffolding del producto definitivo hasta recibir aprobación.**

## Límites de uso en hosting compartido
- No asumir Redis, workers perpetuos, shell root, crons de alta frecuencia ni ejecución ilimitada.
- Si se elige Laravel, publicar solamente `public/` como document root. Proteger `.env`, backups, logs, adjuntos salariales y PDFs fuera de raíz pública; validar que Hostinger permita esto correctamente.
- Si el plan no permite cron o procesos largos, proponer ejecución por lotes o migración a un VPS, con costos y riesgos, antes de comprometer capacidad.
- No duplicar el sistema entero en ambas tecnologías. Las pruebas de fase 0 son exploratorias.

## Fuentes públicas (verificar fecha/vigencia al implementar)
- Hostinger — frameworks y soporte Laravel en hosting web: https://www.hostinger.com/support/which-programming-languages-and-frameworks-are-supported-at-hostinger/
- Hostinger — Composer y SSH: https://www.hostinger.com/support/8727597-how-to-install-composer-locally-at-hostinger/
- Hostinger — configuración de versión PHP: https://www.hostinger.com/support/1575755-how-to-change-the-php-version-of-your-hostinger-hosting-plan/
- Hostinger — Cron: https://www.hostinger.com/es/support/1583465-como-configurar-un-cron-job-en-hostinger/
- Hostinger — Node.js: https://www.hostinger.com/support/how-to-deploy-a-nodejs-website-in-hostinger/
