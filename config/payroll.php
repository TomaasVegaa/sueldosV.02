<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Unvalidated payroll simulation
    |--------------------------------------------------------------------------
    |
    | The current formulas and rates are prototype fixtures. Keep this disabled
    | outside isolated tests until a payroll professional approves versioned
    | rules, sources, rounding and expected cases.
    |
    */
    'unvalidated_simulation_enabled' => (bool) env('PAYROLL_UNVALIDATED_SIMULATION_ENABLED', false),
];
