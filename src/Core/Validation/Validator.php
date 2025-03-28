<?php

namespace App\Core\Validation;

class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function validate(array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule) {
                $this->validateField($field, $rule);
            }
        }

        return empty($this->errors);
    }

    private function validateField(string $field, string $rule): void
    {
        $value = $this->data[$field] ?? null;

        switch ($rule) {
            case 'required':
                if (empty($value)) {
                    $this->addError($field, 'Ce champ est requis');
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'L\'adresse email n\'est pas valide');
                }
                break;

            case 'min':
                if (!empty($value) && strlen($value) < 8) {
                    $this->addError($field, 'Ce champ doit contenir au moins 8 caractères');
                }
                break;

            case 'max':
                if (!empty($value) && strlen($value) > 255) {
                    $this->addError($field, 'Ce champ ne doit pas dépasser 255 caractères');
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, 'Ce champ doit être numérique');
                }
                break;

            case 'alpha':
                if (!empty($value) && !ctype_alpha($value)) {
                    $this->addError($field, 'Ce champ ne doit contenir que des lettres');
                }
                break;

            case 'alphanum':
                if (!empty($value) && !ctype_alnum($value)) {
                    $this->addError($field, 'Ce champ ne doit contenir que des lettres et des chiffres');
                }
                break;

            case 'url':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, 'L\'URL n\'est pas valide');
                }
                break;

            case 'date':
                if (!empty($value) && !strtotime($value)) {
                    $this->addError($field, 'La date n\'est pas valide');
                }
                break;

            case 'in':
                $allowedValues = explode(',', substr($rule, 3));
                if (!empty($value) && !in_array($value, $allowedValues)) {
                    $this->addError($field, 'La valeur n\'est pas autorisée');
                }
                break;

            case 'unique':
                // À implémenter selon le modèle
                break;

            case 'exists':
                // À implémenter selon le modèle
                break;
        }
    }

    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        if (empty($this->errors)) {
            return null;
        }

        $firstField = array_key_first($this->errors);
        return $this->errors[$firstField][0];
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
} 