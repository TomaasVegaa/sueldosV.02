# Sueldos Web

Aplicación interna en desarrollo para administrar empleadores, legajos,
convenios y futuras liquidaciones de haberes de un estudio contable argentino.

> Estado actual: prototipo local. Las pantallas y resultados de cálculo son
> simulaciones no validadas y no deben utilizarse para liquidaciones reales,
> recibos legales ni presentaciones ante ARCA.

## Lectura obligatoria antes de trabajar

1. `AGENTS.md`
2. `SRS_SUELDOS_WEB.md`
3. `docs/ARCA_LSD.md`
4. `docs/MODELO_DE_DATOS.md`
5. `docs/PLAN_PRUEBAS.md`
6. `docs/PREGUNTAS_Y_DECISIONES.md`
7. `docs/PLAN_DE_FASES.md`
8. `docs/COMPARATIVA_STACK_PHP_NEXTJS.md`
9. `docs/ADR-001-arquitectura.md`
10. `docs/estado-requisitos.md`
11. `docs/COORDINACION_AGENTES.md`

## Stack local observado

- PHP 8.2.12
- Laravel 12.69.3
- Livewire 4.4.7
- MySQL/MariaDB para desarrollo local
- Vite y Tailwind CSS

La compatibilidad y capacidad del plan Hostinger Business siguen pendientes de
evidencia reproducible. El ADR no reemplaza esa prueba.

## Puesta en marcha local

No usar datos reales. Crear una base exclusiva de desarrollo y mantener `.env`
fuera de Git.

```powershell
C:\xampp\php\php.exe artisan migrate --seed
npm install
npm run dev
C:\xampp\php\php.exe artisan serve
```

Las pruebas automatizadas deben usar una base aislada. Consultar
`docs/estado-requisitos.md` antes de ejecutar la suite completa.

## Seguridad y privacidad

- No versionar `.env`, PDFs, TXT de ARCA, DBF, videos, backups ni bases SQLite.
- No incluir CUIT, CUIL, CBU, nombres o importes reales en fixtures y capturas.
- Los PDFs y TXT generados deben quedar en almacenamiento privado.
- Ningún agente debe desplegar, migrar datos reales ni enviar archivos a ARCA.

## Trabajo en paralelo

Codex y Antigravity coordinan responsabilidades mediante
`docs/COORDINACION_AGENTES.md`. Git es obligatorio para atribuir cambios,
revisarlos y evitar sobrescrituras.
