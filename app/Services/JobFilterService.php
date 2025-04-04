<?php
namespace App\Services;

use App\Models\Job;
use App\Models\JobAttributeValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\Attribute;

class JobFilterService
{
    protected array $filterOperators = [
        '=' => '=',
        '!=' => '!=',
        '>' => '>',
        '<' => '<',
        '>=' => '>=',
        '<=' => '<=',
        'LIKE' => 'LIKE',
        'IN' => 'IN',
        'HAS_ANY' => 'HAS_ANY',
        'IS_ANY' => 'IS_ANY',
        'EXISTS' => 'EXISTS',
    ];

//    protected array $logicalOperators = ['AND', 'OR'];

    public function applyFilters(Builder $query, string $filterString): Builder
    {
        $filters = $this->parseFilterString($filterString);

        return $this->applyParsedFilters($query, $filters);
    }

    protected function parseFilterString(string $filterString): array
    {
        $filterString = trim($filterString);
        if (Str::startsWith($filterString, '(') && Str::endsWith($filterString, ')')) {
            $filterString = substr($filterString, 1, -1);
        }

        $result = [];
        $currentPos = 0;
        $length = strlen($filterString);
        $currentGroup = [];
        $currentOperator = 'AND';

        while ($currentPos < $length) {
            $char = $filterString[$currentPos];

            if ($char === '(') {
                $groupEndPos = $this->findMatchingClosingParenthesis($filterString, $currentPos);
                $groupContent = substr($filterString, $currentPos + 1, $groupEndPos - $currentPos - 1);

                $currentGroup[] = $this->parseFilterString($groupContent);
                $currentPos = $groupEndPos + 1;
            } elseif (preg_match('/\s+(AND|OR)\s+/i', substr($filterString, $currentPos), $matches, PREG_OFFSET_CAPTURE)) {
                if (!empty($currentGroup)) {
                    $result[] = [
                        'conditions' => $currentGroup,
                        'operator' => $currentOperator
                    ];
                    $currentGroup = [];
                }

                $currentOperator = strtoupper($matches[1][0]);
                $currentPos += $matches[0][1] + strlen($matches[0][0]);
            } elseif (preg_match('/([a-z_:]+)\s*(=|!=|>|<|>=|<=|LIKE|IN|HAS_ANY|IS_ANY|EXISTS)\s*(\([^)]+\)|[^)\s]+)/i', substr($filterString, $currentPos), $matches, PREG_OFFSET_CAPTURE)) {
                $field = $matches[1][0];
                $operator = strtoupper($matches[2][0]);
                $value = $matches[3][0];

                if (Str::startsWith($value, '(') && Str::endsWith($value, ')')) {
                    $value = substr($value, 1, -1);
                }

                if ($operator === 'IN' || $operator === 'HAS_ANY' || $operator === 'IS_ANY') {
                    $value = array_map('trim', explode(',', $value));
                }

                $currentGroup[] = [
                    'field' => $field,
                    'operator' => $operator,
                    'value' => $value
                ];

                $currentPos += $matches[0][1] + strlen($matches[0][0]);
            } else {
                $currentPos++;
            }
        }

        if (!empty($currentGroup)) {
            $result[] = [
                'conditions' => $currentGroup,
                'operator' => $currentOperator
            ];
        }

        return $result;
    }

    protected function findMatchingClosingParenthesis(string $string, int $startPos): int
    {
        $level = 1;
        $currentPos = $startPos + 1;
        $length = strlen($string);

        while ($currentPos < $length && $level > 0) {
            $char = $string[$currentPos];
            if ($char === '(') {
                $level++;
            } elseif ($char === ')') {
                $level--;
            }
            $currentPos++;
        }

        return $currentPos - 1;
    }

    protected function applyParsedFilters(Builder $query, array $filters, string $logicalOperator = 'AND'): Builder
    {
        foreach ($filters as $filterGroup) {
            $operator = strtolower($filterGroup['operator'] ?? $logicalOperator);
            $conditions = $filterGroup['conditions'] ?? [$filterGroup];

            $query->where(function (Builder $query) use ($conditions, $operator) {
                foreach ($conditions as $condition) {
                    if (isset($condition['field'])) {
                        $this->applyCondition($query, $condition, $operator);
                    } else {
                        $this->applyParsedFilters($query, $condition['conditions'] ?? $condition, $condition['operator'] ?? 'AND');
                    }
                }
            });
        }

        return $query;
    }

    protected function applyCondition(Builder $query, array $condition, string $logicalOperator = 'and'): void
    {
        $field = $condition['field'];
        $operator = $condition['operator'];
        $value = $condition['value'];

        if (strpos($field, 'attribute:') === 0) {
            $attributeName = substr($field, 10);
            $this->applyEavCondition($query, $attributeName, $operator, $value, $logicalOperator);
            return;
        }

        if (in_array($field, ['languages', 'locations', 'categories'])) {
            $this->applyRelationshipCondition($query, $field, $operator, $value, $logicalOperator);
            return;
        }

        $method = $logicalOperator === 'or' ? 'orWhere' : 'where';

        switch ($operator) {
            case '=':
            case '!=':
            case '>':
            case '<':
            case '>=':
            case '<=':
                $query->$method($field, $operator, $value);
                break;
            case 'LIKE':
                $query->$method($field, 'LIKE', "%$value%");
                break;
            case 'IN':
                $query->$method(function (Builder $query) use ($field, $value) {
                    $query->whereIn($field, $value);
                });
                break;
            default:
                throw new \InvalidArgumentException("Unsupported operator: $operator");
        }
    }

    protected function applyRelationshipCondition(Builder $query, string $relation, string $operator, $value, string $logicalOperator): void
    {
        $method = $logicalOperator === 'or' ? 'orWhereHas' : 'whereHas';

        switch ($relation) {
            case 'languages':
                $query->$method($relation, function (Builder $query) use ($operator, $value) {
                    if (is_array($value)) {
                        $query->whereIn('languages.name', $value);
                    } else {
                        $query->where('languages.name', $operator === '=' ? '=' : 'LIKE', $value);
                    }
                });
                break;

            case 'locations':
                $query->$method($relation, function (Builder $query) use ($operator, $value) {
                    if (is_array($value)) {
                        $query->where(function ($q) use ($value) {
                            foreach ($value as $location) {
                                $q->orWhere('locations.city', $location)
                                    ->orWhere('locations.state', $location)
                                    ->orWhere('locations.country', $location);
                            }
                        });
                    } else {
                        $query->where('locations.city', $value)
                            ->orWhere('locations.state', $value)
                            ->orWhere('locations.country', $value);
                    }
                });
                break;

            case 'categories':
                $query->$method($relation, function (Builder $query) use ($operator, $value) {
                    if (is_array($value)) {
                        $query->whereIn('categories.name', $value);
                    } else {
                        $query->where('categories.name', $operator === '=' ? '=' : 'LIKE', $value);
                    }
                });
                break;
        }
    }
    protected function applyEavCondition(Builder $query, string $attributeName, string $operator, $value, string $logicalOperator): void
    {
        $method = $logicalOperator === 'or' ? 'orWhereHas' : 'whereHas';

        $query->$method('jobAttributeValues', function (Builder $query) use ($attributeName, $operator, $value) {
            $query->whereHas('attribute', function (Builder $q) use ($attributeName) {
                $q->where('name', $attributeName);
            });

            if (in_array($operator, ['>', '<', '>=', '<='])) {
                $value = (float)$value;
                $query->whereRaw('CAST(value AS DECIMAL(10,2)) '.$operator.' ?', [$value]);
            } else {
                switch ($operator) {
                    case '=':
                    case '!=':
                        $query->where('value', $operator, $value);
                        break;
                    case 'LIKE':
                        $query->where('value', 'LIKE', "%$value%");
                        break;
                    case 'IN':
                        $query->whereIn('value', (array)$value);
                        break;
                    default:
                        throw new \InvalidArgumentException("Unsupported operator for EAV: $operator");
                }
            }
        });
    }
}
