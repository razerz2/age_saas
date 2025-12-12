<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StrongPassword implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (empty($value)) {
            return true; // Permite valores vazios (nullable)
        }

        // Mínimo 8 caracteres
        if (strlen($value) < 8) {
            return false;
        }

        // Pelo menos uma letra maiúscula
        if (!preg_match('/[A-Z]/', $value)) {
            return false;
        }

        // Pelo menos uma letra minúscula
        if (!preg_match('/[a-z]/', $value)) {
            return false;
        }

        // Pelo menos um número
        if (!preg_match('/[0-9]/', $value)) {
            return false;
        }

        // Pelo menos um caractere especial
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/', $value)) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'A senha deve ter no mínimo 8 caracteres, incluindo pelo menos uma letra maiúscula, uma letra minúscula, um número e um caractere especial (!@#$%^&*()_+-=[]{}|;:,.<>?).';
    }
}

