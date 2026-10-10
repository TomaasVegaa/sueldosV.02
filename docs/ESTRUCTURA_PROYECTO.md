# Estructura del Proyecto Laravel (Monolito Modular)

Ubicación del proyecto: `c:\xampp\htdocs\sueldosV.02`

```text
sueldosV.02/
├── AGENTS.md                  # Contrato de ejecución para agentes de IA
├── SRS_SUELDOS_WEB.md         # Requisitos de negocio y especificaciones funcionales
├── docs/                      # Documentación arquitectónica y técnica
│   ├── ADR-001-arquitectura.md
│   ├── MODELO_DE_DATOS.md
│   ├── DICCIONARIO_FORMULAS.md
│   ├── ESTRUCTURA_PROYECTO.md
│   └── PLAN_DE_DESARROLLO.md
│
├── app/                       # Aplicación Laravel (Orquestación HTTP / UI)
│   ├── Http/
│   │   ├── Controllers/       # Controladores para descarga privada de PDFs y TXTs
│   │   └── Middleware/        # MultiEmployerScope, VerifyActiveSession, TwoFactor
│   ├── Livewire/              # Componentes reactivos por módulo
│   │   ├── Employers/         # Listado, selector de empresa activa, configuración
│   │   ├── Workforce/         # Legajos, historial temporal, novedades
│   │   ├── Agreements/        # Convenios, categorías, escalas versionadas
│   │   ├── Concepts/          # Catálogo, editor y simulador de fórmulas
│   │   ├── Payroll/           # Preliquidación en grilla, cálculo masivo, workflow
│   │   └── Arca/              # Mapeos por empleador, generación y validador LSD
│   ├── Models/                # Modelos Eloquent con relaciones y scopes
│   └── Policies/              # Políticas de autorización por empleador y rol
│
├── src/Domain/                # Dominio Puro (Independiente de Laravel y de la DB)
│   ├── Engine/                # Motor de Cálculo y Fórmulas
│   │   ├── Lexer.php          # Tokenizador de expresiones matemáticas/lógicas
│   │   ├── Parser.php         # Parser Pratt que genera AST tipado
│   │   ├── Ast/               # Nodos AST (Number, Variable, BinaryOp, CallNode...)
│   │   ├── DependencyGraph.php# Grafo y ordenamiento topológico (resuelve LQxxx)
│   │   └── Evaluator.php      # Evaluador seguro con BigDecimal / BCMath
│   ├── Arca/                  # Interfaz LSD ARCA
│   │   ├── Record01.php       # Cabecera SJ / RE (35 chars)
│   │   ├── Record02.php       # Datos del trabajador (115 chars)
│   │   ├── Record03.php       # Conceptos de liquidación (51 chars)
│   │   ├── Record04.php       # Bases imponibles F.931 (370 chars)
│   │   ├── LsdSerializer.php  # Serializador de ancho fijo con padding y ASCII
│   │   └── LsdValidator.php   # Validador de estructura y congruencia
│   └── Money/                 # Utilidades de redondeo y moneda exacta (brick/math)
│
├── database/
│   ├── migrations/            # Migraciones ordenadas y reversibles (InnoDB)
│   └── seeders/               # Catálogos oficiales (Obras Sociales, tablas ARCA)
│
├── resources/
│   ├── views/
│   │   ├── layouts/           # App layout con barra lateral y selector de empresa
│   │   ├── livewire/          # Vistas Blade de componentes Livewire
│   │   └── pdf/               # Plantillas Blade para Recibos A4 (cumplimiento LCT 140)
│   └── css/                   # Tailwind CSS
│
├── storage/
│   └── app/
│       └── private/           # Directorio PRIVADO para PDFs y TXTs generados
│           ├── receipts/
│           └── arca_txt/
│
└── tests/
    ├── Unit/
    │   ├── EngineTest.php     # Tests de fórmulas matemáticas, orden y dependencias
    │   ├── LsdSerializerTest.php # Tests byte a byte de cada registro
    │   └── MoneyTest.php      # Tests de redondeos y precisión decimal
    └── Feature/
        ├── MultiEmployerIsolationTest.php # Tests de seguridad y acceso cruzado
        ├── PayrollWorkflowTest.php        # Flujo Borrador -> Aprobada -> Inmutable
        └── ConcurrencyLockTest.php        # Detección de colisiones (lock_version)
```
