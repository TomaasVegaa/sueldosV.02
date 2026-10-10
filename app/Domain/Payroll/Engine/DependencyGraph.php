<?php

namespace App\Domain\Payroll\Engine;

use RuntimeException;

class DependencyGraph
{
    /** @var array<string, array<string>> conceptCode => list of required conceptCodes */
    protected array $adjacencyList = [];

    /** @var array<string, mixed> conceptCode => concept definition object/array */
    protected array $concepts = [];

    /**
     * Add a concept to the graph.
     *
     * @param  string  $code  (e.g., "001", "002", "004")
     * @param  string  $type  ("REMUNERATIVO", "NO_REMUNERATIVO", "DESCUENTO")
     */
    public function addConcept(string $code, string|AstNode $formula, string $type = 'REMUNERATIVO', mixed $metadata = null, ?int $order = null): void
    {
        $code = strtoupper(trim($code));
        $node = is_string($formula) ? Parser::parseString($formula) : $formula;
        $referencedVars = $node->getReferencedVariables();

        $dependencies = [];
        foreach ($referencedVars as $var) {
            // Check for direct concept dependency LQxxx
            if (preg_match('/^LQ([0-9A-Z_]+)$/i', $var, $matches)) {
                $dependencies[] = strtoupper($matches[1]);
            }
        }

        $this->adjacencyList[$code] = array_unique($dependencies);
        $this->concepts[$code] = [
            'code' => $code,
            'formula' => $formula,
            'ast' => $node,
            'type' => strtoupper($type),
            'metadata' => $metadata,
            'order' => $order ?? (is_numeric($code) ? (int) $code : 500),
            'uses_th' => in_array('TH', $referencedVars, true),
            'uses_ts' => in_array('TS', $referencedVars, true),
            'uses_td' => in_array('TD', $referencedVars, true),
        ];
    }

    /**
     * Resolve the topological calculation order.
     * Concepts that depend on TH or TS are automatically placed after the respective remunerative/non-taxable concepts.
     *
     * @return array<string> List of concept codes in executable order
     *
     * @throws RuntimeException If a circular dependency is detected
     */
    public function getCalculationOrder(): array
    {
        // Add implicit dependencies for TH, TS, TD
        $remunerativeCodes = [];
        $nonTaxableCodes = [];

        foreach ($this->concepts as $code => $data) {
            if ($data['type'] === 'REMUNERATIVO') {
                $remunerativeCodes[] = $code;
            } elseif ($data['type'] === 'NO_REMUNERATIVO') {
                $nonTaxableCodes[] = $code;
            }
        }

        $fullAdjacency = $this->adjacencyList;

        foreach ($this->concepts as $code => $data) {
            $currentOrder = (int) ($data['order'] ?? (is_numeric($code) ? (int) $code : 500));

            if ($data['uses_th']) {
                foreach ($remunerativeCodes as $remCode) {
                    if ($remCode !== $code) {
                        $remOrder = (int) ($this->concepts[$remCode]['order'] ?? (is_numeric($remCode) ? (int) $remCode : 500));
                        if ($data['type'] !== 'REMUNERATIVO' || $remOrder < $currentOrder) {
                            $fullAdjacency[$code][] = $remCode;
                        }
                    }
                }
            }
            if ($data['uses_ts']) {
                foreach ($nonTaxableCodes as $nonTaxCode) {
                    if ($nonTaxCode !== $code) {
                        $nonTaxOrder = (int) ($this->concepts[$nonTaxCode]['order'] ?? (is_numeric($nonTaxCode) ? (int) $nonTaxCode : 500));
                        if ($data['type'] !== 'NO_REMUNERATIVO' || $nonTaxOrder < $currentOrder) {
                            $fullAdjacency[$code][] = $nonTaxCode;
                        }
                    }
                }
            }
            $fullAdjacency[$code] = array_values(array_unique($fullAdjacency[$code]));
        }

        // Kahn's algorithm for topological sorting
        $inDegree = [];
        foreach (array_keys($this->concepts) as $code) {
            $inDegree[$code] = 0;
        }

        $graph = [];
        foreach (array_keys($this->concepts) as $code) {
            $graph[$code] = [];
        }

        foreach ($fullAdjacency as $dependent => $prerequisites) {
            foreach ($prerequisites as $prereq) {
                // If prereq is not part of this run, ignore it
                if (! isset($this->concepts[$prereq])) {
                    continue;
                }
                $graph[$prereq][] = $dependent;
                $inDegree[$dependent]++;
            }
        }

        $queue = [];
        foreach ($inDegree as $code => $degree) {
            if ($degree === 0) {
                $queue[] = $code;
            }
        }

        $sorted = [];
        while (! empty($queue)) {
            $current = array_shift($queue);
            $sorted[] = $current;

            foreach ($graph[$current] as $neighbor) {
                $inDegree[$neighbor]--;
                if ($inDegree[$neighbor] === 0) {
                    $queue[] = $neighbor;
                }
            }
        }

        if (count($sorted) !== count($this->concepts)) {
            $unresolved = array_diff(array_keys($this->concepts), $sorted);
            throw new RuntimeException(
                'Dependencia circular detectada entre conceptos de liquidación: '.implode(', ', $unresolved)
            );
        }

        return $sorted;
    }
}
