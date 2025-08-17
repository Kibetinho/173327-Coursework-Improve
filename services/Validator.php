<?php

class Validator
{
    public static function nonEmpty(string $value, string $fieldName, array &$errors): void
    {
        if (trim($value) === '') {
            $errors[] = $fieldName . ' is required';
        }
    }

    public static function email(string $value, string $fieldName, array &$errors): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = $fieldName . ' is invalid';
        }
    }

    public static function minLen(string $value, int $min, string $fieldName, array &$errors): void
    {
        if (mb_strlen($value) < $min) {
            $errors[] = $fieldName . ' must be at least ' . $min . ' characters';
        }
    }

    public static function intRange($value, int $min, int $max, string $fieldName, array &$errors): void
    {
        if (!is_numeric($value)) {
            $errors[] = $fieldName . ' must be a number';
            return;
        }
        $iv = (int)$value;
        if ($iv < $min || $iv > $max) {
            $errors[] = $fieldName . ' must be between ' . $min . ' and ' . $max;
        }
    }
}


