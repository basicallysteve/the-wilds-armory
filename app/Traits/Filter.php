<?php

namespace App\Traits;

use Illuminate\Support\Carbon;
trait Filter {
    
    public function parseFilters(array $request): string
    {
        $filters = [];
        $orBlock = [];
        //handle filter_or conditions
        if (isset($request['filter_or']) && is_array($request['filter_or'])) {
            foreach ($request['filter_or'] as $key => $value) {
                // if value is comma separated, convert to array
                if (is_string($value) && strpos($value, ',') !== false) {
                    $value = explode(',', $value);
                }
                if (is_array($value)) {
                    // If value is an array, use $in for MongoDB
                    $orBlock[] = [$key => ['$in' => $value]];
                } elseif (is_numeric($value)) {
                    // If value is numeric, use $eq for MongoDB
                    $orBlock[] = [$key => ['$eq' => (float)$value]];
                } elseif (is_string($value)) {
                    $orBlock[] = [$key => ['$eq' => $value]];
                }
            }
            if (!empty($orBlock)) {
                $filters['$or'] = $orBlock;
            }
        }

        // Handle filter[key]=value
        if (isset($request['filter']) && is_array($request['filter'])) {
            foreach ($request['filter'] as $key => $value) {
                // If value is an array, use $in for MongoDB
                if (is_array($value)) {
                    $filters[$key] = ['$in' => $value];
                } else {
                    $filters[$key] = ['$eq' => $value];
                }
            }
        }

        //handle startswith, endswith, contains
        if (isset($request['starts_with']) && is_array($request['starts_with'])) {
            foreach ($request['starts_with'] as $key => $value) {
                if (is_string($value)) {
                    $filters[$key] = ['$regex' => '^' . preg_quote($value, '/')];
                }
            }
        }

        if (isset($request['ends_with']) && is_array($request['ends_with'])) {
            foreach ($request['ends_with'] as $key => $value) {
                if (is_string($value)) {
                    $filters[$key] = ['$regex' => preg_quote($value, '/') . '$'];
                }
            }
        }

        if (isset($request['contains']) && is_array($request['contains'])) {
            foreach ($request['contains'] as $key => $value) {
                if (is_string($value)) {
                    $filters[$key] = ['$regex' => preg_quote($value, '/')];
                }
            }
        }



        // Handle range[key]=low,high
        if (isset($request['range']) && is_array($request['range'])) {
            foreach ($request['range'] as $key => $range) {
                if (is_string($range)) {
                    $parts = explode(',', $range, 2);
                    if (count($parts) === 2) {
                        $rangeFilter = [];
                        // Convert to MongoDB date format if necessary
                        if ($this->isDate($parts[0])) {
                            $parts[0] = $this->convertToMongoDate($parts[0]);
                        }
                        if ($this->isDate($parts[1])) {
                            $parts[1] = $this->convertToMongoDate($parts[1]);
                        }
                        if ($parts[0] !== '' && $parts[0] !== "null") {
                            $rangeFilter['$gte'] = is_numeric($parts[0]) ? (float)$parts[0] : $parts[0];
                        }
                        if ($parts[1] !== '' && $parts[1] !== "null") {
                            $rangeFilter['$lte'] = is_numeric($parts[1]) ? (float)$parts[1] : $parts[1];
                        }
                        if (!empty($rangeFilter)) {
                            $filters[$key] = $rangeFilter;
                        }
                    }
                }
            }
        }
        
        return json_encode($filters);
    }

    private function isDate($value): bool
    {
        try {
            Carbon::parse($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // create a function that converts a date string to an appropriate MongoDB date format
    private function convertToMongoDate($value): string
    {
        if ($this->isDate($value)) {
            return Carbon::parse($value)->toIso8601String();
        }
        return $value; // Return as is if not a date
    }
    

}