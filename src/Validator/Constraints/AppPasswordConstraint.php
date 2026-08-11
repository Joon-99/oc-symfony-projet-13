<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Sequentially;

#[\Attribute]
class AppPasswordConstraint extends Compound
{
    public const int MIN_LENGTH = 8;
    public const int MAX_LENGTH = 4096; // Symfony's limit for security reasons
    public const string SPECIAL_CHARACTERS = '@$!%*?&-_#';

    public function getConstraints(array $options): array
    {
        $specialCharacters = preg_quote(self::SPECIAL_CHARACTERS, '/');

        return [
            new Sequentially([
                new NotBlank(
                    message: 'Veuillez entrer un mot de passe',
                ),
                new Length(
                    min: self::MIN_LENGTH,
                    max: self::MAX_LENGTH,
                    minMessage: 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                    maxMessage: 'Votre mot de passe ne peut pas dépasser {{ limit }} caractères',
                ),
                new Regex(
                    pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*['.$specialCharacters.']).+$/',
                    message: 'Votre mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial parmi cette liste : '.self::SPECIAL_CHARACTERS,
                ),
                new Regex(
                    pattern: '/^[A-Za-z\d'.$specialCharacters.']+$/',
                    message: 'Votre mot de passe contient des caractères non autorisés, les seuls caractères autorisés sont les lettres, les chiffres et les caractères spéciaux suivants : '.self::SPECIAL_CHARACTERS.'.'
                ),
                new NotCompromisedPassword(
                    message: 'Ce mot de passe a été compromis dans une fuite de données. Veuillez en choisir un autre.',
                    skipOnError: true,
                ),
            ]),
        ];
    }
}
