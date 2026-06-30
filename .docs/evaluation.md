# Projektbewertung

## Was gut ist

### Immutable Builder Pattern
`QueryBuilder` gibt bei jeder Änderung ein `new self()` zurück. Das ist thread-safe, einfach testbar und verhindert unbeabsichtigte Seiteneffekte beim Wiederverwenden eines Builders.

### Interface-Segregation
Die Interfaces (`RepresentsCriteria`, `RepresentsColumn`, `RepresentsLimit` etc.) sind klar getrennt und erlauben saubere Abhängigkeiten ohne unnötige Kopplung.

### Vollständige Operator-Abdeckung
Alle relevanten SQL-Vergleichsoperatoren sind vorhanden, inkl. `IN (subquery)`, `IS NULL`, `LIKE`, `NOT LIKE`, `NOT IN` etc.

### IsolatedCriteria
Geklammerte WHERE-Gruppen (`(a = 1 OR b = 2)`) werden als eigene Klasse modelliert — das ist eine saubere Unterscheidung zu `Criteria`.

---

## Was fehlt / problematisch ist

### Bug: `AbstractInCondition::init()` (invertierte Logik)
`src/Criterias/Conditions/AbstractInCondition.php:55`

```php
// Aktuell (falsch):
if ( !$values )
    $this->setPreparedParameters( $column, $values ); // wird aufgerufen wenn $values leer ist
else
    $this->subQuery = $subQuery;

// Korrekt:
if ( $values )
    $this->setPreparedParameters( $column, $values );
else
    $this->subQuery = $subQuery;
```

### HavingBuilder ist ein leerer Stub
`src/Builders/HavingBuilder.php` enthält nur einen `@Todo`-Kommentar und keine Implementierung.

### Inkonsistente Typen für Operatoren
`LogicalOperator` ist ein PHP-Enum, `ComparisonOperator` ist eine Klasse mit Konstanten. In PHP 8.1+ sollte `ComparisonOperator` ebenfalls ein Enum sein.

### Kein SELECT-Spalten-Management
`QueryBuilder` verwaltet nur WHERE / ORDER BY / GROUP BY / LIMIT, aber keine `SELECT`-Spalten. Das macht ihn nur als Filter-Builder nutzbar, nicht als vollständigen Query-Builder.

### Keine JOIN-Unterstützung
JOINs fehlen komplett.

### Keine Tests
Nur ein leeres `tests/bootstrap.php` — keine Unit- oder Integrationstests.
