# Decisiones pendientes y preguntas al estudio

**Semáforo**: BLOQUEANTE = no programar cálculo/exportación correspondiente hasta resolver; IMPORTANTE = afecta diseño/esfuerzo; OPCIONAL = se puede postergar.

| ID | Prioridad | Pregunta / incertidumbre | Acción para resolver |
|---|---|---|---|
| PD-01 | BLOQUEANTE | ¿Qué representan realmente `TH`, `TS`, `TSI`, `NR`, `SIT`, `SID`, `SIM`, `VI`, `IM`, `AN`, `OSO`, `LQxxx`, `CQxxx` en Cortez? | Diccionario de variables del sistema + 2 ejemplos detallados |
| PD-02 | BLOQUEANTE | ¿La lista de conceptos es global, por CCT o por empresa? El usuario indicó "creo". | Abrir concepto en 2 convenios distintos, cambiar en copia/entorno de prueba y comprobar impacto; nunca tocar datos productivos sin backup |
| PD-03 | BLOQUEANTE | ¿Qué conceptos del empleador están asociados a qué códigos ARCA y con qué flags previsionales? | Exportar/capturar tabla de parametrización de ARCA por empleador, anonimizada |
| PD-04 | BLOQUEANTE | ¿Existe prueba/documento de aceptación del TXT actual en LSD? | Confirmar una importación manual exitosa, sin claves fiscales compartidas |
| PD-05 | BLOQUEANTE | ¿Cuáles son las reglas vigentes por CCT y período para sueldo, licencias, vacaciones, SAC y finales? | Experto contable valida casos por convenio y fuente normativa |
| PD-06 | BLOQUEANTE | ¿Cómo trata Cortez redondeo, topes, bases y condiciones de jornada parcial? | Comparar detalle de cálculo con PDFs y fórmulas; registrar casos |
| PD-07 | BLOQUEANTE | ¿Cómo se distingue la versión del período que ya fue cargada/presentada en ARCA? | Definir aprobación y tratamiento de correcciones/rectificaciones |
| PD-08 | IMPORTANTE | ¿Cuántos trabajadores tiene la empresa más grande y cuántos legajos históricos hay? | Conteo aproximado, determinar carga/performance |
| PD-09 | IMPORTANTE | ¿Cuáles son todos los convenios vigentes y qué empresas usan cada uno? | Exportar/capturar listados y categorías; priorizar implementación |
| PD-10 | IMPORTANTE | ¿Se pueden copiar los `.DBF` de años anteriores? ¿Qué otros archivos necesita Cortez para interpretarlos? | Copia con autorización, listado de ficheros, respaldo previo |
| PD-11 | IMPORTANTE | ¿Qué métodos de importación de empresas/legajos se usan hoy (planillas, DBF, manual)? | Muestras anonimizadas, definir migración |
| PD-12 | IMPORTANTE | ¿Cuánto cambia una escala por convenio, por categoría y por fecha? ¿Quién aprueba la actualización? | Definir flujo funcional y responsable |
| PD-13 | IMPORTANTE / DECISIÓN ARQUITECTÓNICA | ¿Qué stack conviene en Hostinger Business: **PHP/Laravel** o **Next.js/Node**? ¿Versiones PHP/extensiones, Composer/SSH, cron, MySQL, proceso Node, cuotas, memoria, tiempo de ejecución y backups disponibles? | Verificar hPanel, realizar pruebas mínimas **de ambas opciones** sin datos reales; completar ADR-001 con comparación y aprobación del propietario |
| PD-14 | IMPORTANTE | ¿Cuántos usuarios tienen facultad de cerrar/exportar/corregir y a qué empresas acceden? | Matriz de roles nominales (sin poner contraseñas) |
| PD-15 | IMPORTANTE | ¿Recibo PDF original/duplicado, firma, QR, detalle patronal, gráficos? | Aprobar maqueta nueva e información que debe aparecer |
| PD-16 | IMPORTANTE | ¿Fechas de pago, rúbrica, número de liquidación y días base se manejan igual en todos los casos? | Validar práctica actual y requisitos ARCA |
| PD-17 | IMPORTANTE | ¿La empresa desea ver costos patronales separados y exportar planilla netos/CBU a banco? | Delimitar reportes de primera versión |
| PD-18 | IMPORTANTE | ¿Qué datos de licencias se registran (médico, comprobante, días, acumulación)? | Definir campos mínimos, validación y privacidad |
| PD-19 | IMPORTANTE | ¿Qué se hace con trabajadores dados de baja que vuelven a ingresar? | Modelo de múltiples relaciones laborales |
| PD-20 | IMPORTANTE | ¿Qué cantidad de años históricos se migrarán y cuánto tiempo conservarlos? | Política legal y operativa del estudio |
| PD-21 | OPCIONAL | ¿Se requiere importar novedades mensuales desde Excel/CSV? | Priorizar según volúmenes |
| PD-22 | OPCIONAL | ¿Se necesitan firmas digitales/electrónicas propias? | Especificación separada si corresponde |
| PD-23 | OPCIONAL | ¿Se desea notificación interna de empresas pendientes? | Fase posterior |

## Cómo resolver PD-02 sin copiar código protegido
Pedir pantallas/configuraciones del sistema bajo uso legítimo y observar **comportamiento** de catálogo (código repetido, ámbito y reglas). No es necesario inspeccionar/reproducir código propietario. Diseñar el nuevo sistema con catálogos globales y asignaciones específicas desde el inicio.

## Datos que nunca se deben pedir al usuario
Contraseñas de ARCA, claves fiscales, claves de Hostinger, tokens de GitHub, bases de datos reales sin una estrategia de privacidad, ni archivos de empleados reales subidos a repositorios públicos. Para cuestiones funcionales, usar capturas anonimizadas o archivos locales controlados.

## Próxima entrevista recomendada (30–45 min)
1. Elegir 1 empleador de Comercio con 2–3 empleados, 1 licencia y 1 caso de media jornada, todos **anonimizados**.
2. Examinar tabla de categorías/escalas de ese convenio.
3. Ver cómo se cargan novedades, cómo se ejecuta `Calcular` y cómo se corrigen importes.
4. Examinar parametrización de conceptos para ARCA del empleador.
5. Exportar un TXT y confirmar su carga manual en ARCA.
6. Acordar reglas para correcciones después de cerrar/presentar.

**Salida de entrevista:** ejemplo de cálculo reproducible, diccionario de variables, datos mínimos y aprobación de flujo.
