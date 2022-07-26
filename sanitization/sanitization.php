<?php
    // ===================== Sanitization library===============

    /**
     * FILTERS: contain rules and its corresponding filters.
     */
    const FILTERS = [
        'string' => FILTER_SANITIZE_STRING,
        'string[]' => [
            'filter' => FILTER_SANITIZE_STRING,
            'flags' => FILTER_REQUIRE_ARRAY
        ],
        'email' => FILTER_SANITIZE_EMAIL,
        'int' => [
            'filter' => FILTER_SANITIZE_NUMBER_INT,
            'flags' => FILTER_REQUIRE_SCALAR
        ],
        'int[]' => [
            'filter' => FILTER_SANITIZE_NUMBER_INT,
            'flags' => FILTER_REQUIRE_ARRAY
        ],
        'float' => [
            'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
            'flags' => FILTER_FLAG_ALLOW_FRACTION
        ],
        'float[]' => [
            'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
            'flags' => FILTER_REQUIRE_ARRAY
        ],
        'url' => FILTER_SANITIZE_URL,
    ];

    /**
    * array_trim: Recursively trim strings in an array
    * @param array $items: array of items to trim.
    * @return array
    */
    function array_trim(array $items){
        return array_map(function ($item) {
            if (is_string($item)) {
                return trim($item);
            } elseif (is_array($item)) {
                return array_trim($item);
            } else
                return $item;
        }, $items);
    }

    /**
    * Sanitize the inputs based on the rules an optionally trim the string
    * @param (array) $inputs:iput data to sanitize.
    * @param (array) $fields: containing rules of each field.
    * @param (int) $default_filter FILTER_SANITIZE_STRING: if $fields is not set, all fields in $input is sanitize with filter default.
    * @param (array) $filters FILTERS: it contain rules and corresponding filter.
    * @param (bool) $trim: help to trim string.
    * @return array
    */
    function sanitize(array $inputs, array $fields = [], int $default_filter = FILTER_SANITIZE_STRING, array $filters = FILTERS, bool $trim = true): array
    {
        if ($fields) {
            $options = array_map(
                function($field) use($filters) {
                    return  $filters[$field];
                }
                , $fields
            );
            $data = filter_var_array($inputs, $options);
        } else {
            $data = filter_var_array($inputs, $default_filter);
        }

        return $trim ? array_trim($data) : $data;
    }