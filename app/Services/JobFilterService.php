<?php
namespace App\Services;

use App\Models\Job;
use App\Models\JobAttributeValue;
use Illuminate\Database\Eloquent\Builder;

class JobFilterService
{
//    public static function filter(string $filterString): Builder
//    {
//        $filters = (new self)->parseFilters($filterString); // Call method correctly
//        $query = Job::query();
//        if (!empty($filters['job_type'])) {
//            if (is_array($filters['job_type']))
//            {
//                foreach ($filters['job_type'] as $jobType)
//                {
//                    $query->where('job_type', $jobType);
//                }
//            }
//            else
//                $query->where('job_type', $filters['job_type']);
//        }
//
//        if (!empty($filters['languages'])) {
//
//            $query->whereHas('languages', function ($q) use ($filters) {
//                $q->whereIn('name', $filters['languages']);
//            });
//        }
//
//        if (!empty($filters['locations'])) {
//            $query->whereHas('locations', function ($q) use ($filters) {
//                $q->whereIn('city', $filters['locations']);
//            });
//        }
//
//        if (!empty($filters['attributes'])) {
//            foreach ($filters['attributes'] as $attr => $data) {
//                $query->whereHas('attributes', function ($q) use ($attr, $data) {
//                    $q->where('attributes.name', $attr)
//                        ->where('job_attribute_values.value', $data['operator'], $data['value']);
//                });
//            }
//        }
//
//        return $query;
//    }
//
//    function parseFilters(string $filterString): array
//    {
//        $filters = [];
//
//        // Extract job_type
//        if (preg_match('/job_type=([\w-]+)/', $filterString, $matches)) {
//            $filters['job_type'] = $matches[1];
//        }
//
//        // Extract languages (HAS_ANY)
//        if (preg_match('/languages HAS_ANY \((.*?)\)/', $filterString, $matches)) {
//            $filters['languages'] = explode(',', $matches[1]); // Convert to array
//        }
//
//        // Extract locations (IS_ANY)
//        if (preg_match('/locations IS_ANY \((.*?)\)/', $filterString, $matches)) {
//            $filters['locations'] = explode(',', $matches[1]); // Convert to array
//        }
//
//        // Extract attributes (e.g., years_experience>=3)
//        if (preg_match('/attribute:(\w+)([>=<]+)(\d+)/', $filterString, $matches)) {
//            $filters['attributes'][$matches[1]] = [
//                'operator' => $matches[2], // e.g., '>='
//                'value' => (int) $matches[3], // e.g., 3
//            ];
//        }
//
//        return $filters;
//    }
    protected $query;
    protected $filterParams;

    public function __construct(array $filterParams)
    {
        $this->query = Job::query();
        $this->filterParams = $filterParams;
    }

    public function apply(): Builder
    {
        if (isset($this->filterParams['filter'])) {
            $this->parseFilterString($this->filterParams['filter']);
        }

        return $this->query;
    }

    protected function parseFilterString(string $filterString)
    {
        // Parse the filter string and build the query
        // This is a simplified version - you'll need to expand it

        $conditions = $this->extractConditions($filterString);

        foreach ($conditions as $condition) {
            $this->applyCondition($condition);
        }
    }

    protected function extractConditions(string $filterString): array
    {
        // Implement logic to parse the filter string into individual conditions
        // This should handle AND/OR logic and grouping

        return []; // Return parsed conditions
    }

    protected function applyCondition(array $condition)
    {
        $field = $condition['field'];
        $operator = $condition['operator'];
        $value = $condition['value'];

        if (Str::contains($field, ':')) {
            // EAV attribute filtering
            $this->applyEavCondition($field, $operator, $value);
        } elseif (in_array($field, ['languages', 'locations', 'categories'])) {
            // Relationship filtering
            $this->applyRelationshipCondition($field, $operator, $value);
        } else {
            // Standard field filtering
            $this->applyStandardCondition($field, $operator, $value);
        }
    }

    protected function applyStandardCondition(string $field, string $operator, $value)
    {
        switch ($operator) {
            case '=':
                $this->query->where($field, $value);
                break;
            case '!=':
                $this->query->where($field, '!=', $value);
                break;
            case '>':
                $this->query->where($field, '>', $value);
                break;
            case '<':
                $this->query->where($field, '<', $value);
                break;
            case '>=':
                $this->query->where($field, '>=', $value);
                break;
            case '<=':
                $this->query->where($field, '<=', $value);
                break;
            case 'LIKE':
                $this->query->where($field, 'LIKE', "%{$value}%");
                break;
            case 'IN':
                $this->query->whereIn($field, (array)$value);
                break;
        }
    }

    protected function applyRelationshipCondition(string $relation, string $operator, $value)
    {
        $method = 'whereHas';
        $value = is_array($value) ? $value : [$value];

        switch ($operator) {
            case '=':
            case 'HAS_ANY':
                $this->query->{$method}($relation, function ($query) use ($value) {
                    $query->whereIn('name', $value);
                });
                break;
            case 'IS_ANY':
                $this->query->{$method}($relation, function ($query) use ($value) {
                    $query->where(function ($q) use ($value) {
                        foreach ($value as $item) {
                            if ($relation === 'locations') {
                                $parts = explode(',', $item);
                                $q->orWhere(function ($locQuery) use ($parts) {
                                    foreach ($parts as $i => $part) {
                                        $field = ['city', 'state', 'country'][$i] ?? 'city';
                                        $locQuery->where($field, trim($part));
                                    }
                                });
                            } else {
                                $q->orWhere('name', $item);
                            }
                        }
                    });
                });
                break;
            case 'EXISTS':
                $this->query->{$method}($relation);
                break;
        }
    }

    protected function applyEavCondition(string $field, string $operator, $value)
    {
        $attributeName = explode(':', $field)[1];

        $this->query->whereHas('attributeValues', function ($query) use ($attributeName, $operator, $value) {
            $query->whereHas('attribute', function ($q) use ($attributeName) {
                $q->where('name', $attributeName);
            });

            switch ($operator) {
                case '=':
                    $query->where('value', $value);
                    break;
                case '!=':
                    $query->where('value', '!=', $value);
                    break;
                case '>':
                case '<':
                case '>=':
                case '<=':
                    $query->where('value', $operator, $value);
                    break;
                case 'LIKE':
                    $query->where('value', 'LIKE', "%{$value}%");
                    break;
                case 'IN':
                    $query->whereIn('value', (array)$value);
                    break;
            }
        });
    }


}
