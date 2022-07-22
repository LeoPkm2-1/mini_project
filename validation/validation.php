<?php
    // library validation ================================================

    // field in input form has require some (one or more) validation rules
    /**
     * ex: username is required , between 3-20 character, alphanumeric
     */

      /**
       * fields is array that contain elements that describe the rules for coresponding fields 
       * key-element is field-name and value is rules for the field.
       * $fields = [
       *    'email' => required | email'
       *    'username' => 'required | alphanumeric | between: 3,255
       * ] 
       */

        /**
         * validate
         * @param (array) $data => data to validate
         * @param (array) $fields => contains the validation rules for each field in that array
         * @param (array) $messages => default and custom error message
         * @return (array) => contain validation all errors, which the key of each element is field-name and value is error-message.\
         * 
         * validate function need to iterate over the $fields. for each field , it loops through rules of itand validates the value against each rule. If the validation fails, it’ll add an error message to an $errors array
         */

        const DEFAULT_VALIDATION_ERRORS = [
            'required' => 'Please enter the %s',
            'email' => 'The %s is not a valid email address',
            'min' => 'The %s must have at least %s characters',
            'max' => 'The %s must have at most %s characters',
            'between' => 'The %s must have between %d and %d characters',
            'same' => 'The %s must match with %s',
            'alphanumeric' => 'The %s should have only letters and numbers',
            'secure' => 'The %s must have between 8 and 64 characters and contain at least one number, one upper case letter, one lower case letter and one special character',
            'unique' => 'The %s already exists',
        ];

       function validate(array $data, array $fields,array $messages = []){

            $split = function($str,$separator){
                return array_map('trim',explode($separator,$str));
            };

            // get the message rules
            // $rule_messages = array_filter($messages, fn($message) =>  is_string($message));
            $rule_messages = array_filter($messages, function ($message){ return  is_string($message);});

            // set the validation messages
            $validation_errors = array_merge(DEFAULT_VALIDATION_ERRORS, $rule_messages);
            
            $errors = []; // containt all errors messages for all field that have checked.

            foreach($fields as $field => $option){
                // get the array of rules for the current field
                $rules = $split($option,'|');

                // for current field field loop over through rules.
                foreach ($rules as $rule ) {
                    // run validation for current rule and the current field.
                    // the rule may have parameters. if it have parameter it contain ':'. extract the rule name and params of the rule.
                    $params =[]; // param list of the current rule.
                    if(strpos($rule, ':')) {    // rule have params
                        [$rule_name, $param_str] = $split($rule, ':');
                        $params = $split($param_str, ',');

                    }else{
                        $rule_name = trim($rule);
                    }

                    $fn = 'is_'. $rule_name;
                    if(is_callable($fn)){
                        $pass = $fn($data, $field , ...$params);
                        if(!$pass){
                            $errors[$field] = sprintf(
                                $messages[$field][$rule_name] ?? $validation_errors[$rule_name],
                                $field, 
                                ...$params
                            );
                        }
                    }

                }
            }
            
            return $errors;
       }

       function is_required(array $data, string $field)
        {
            return isset($data[$field]) && trim($data[$field]) !== '';
        }

        function is_email(array $data, string $field)
        {
            if (empty($data[$field])) {
                return true;
            }
        
            return filter_var($data[$field], FILTER_VALIDATE_EMAIL);
        }        

        function is_min(array $data, string $field, int $min)
        {
            if (!isset($data[$field])) {
                return true;
            }
        
            return mb_strlen($data[$field]) >= $min;
        }        

        function is_max(array $data, string $field, int $max)
        {
            if (!isset($data[$field])) {
                return true;
            }
        
            return mb_strlen($data[$field]) <= $max;
        }        

        function is_between(array $data, string $field, int $min, int $max)
        {
            if (!isset($data[$field])) {
                return true;
            }
        
            $len = mb_strlen($data[$field]);
            return $len >= $min && $len <= $max;
        }        

        function is_alphanumeric(array $data, string $field)
        {
            if (!isset($data[$field])) {
                return true;
            }
        
            return ctype_alnum($data[$field]);
        }        

        function is_same(array $data, string $field, string $other)
        {
            if (isset($data[$field], $data[$other])) {
                return $data[$field] === $data[$other];
            }
        
            if (!isset($data[$field]) && !isset($data[$other])) {
                return true;
            }
        
            return false;
        }        

        function is_secure(array $data, string $field)
        {
            if (!isset($data[$field])) {
                return false;
            }
        
            $pattern = "#.*^(?=.{8,64})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#";
            return preg_match($pattern, $data[$field]);
        }        