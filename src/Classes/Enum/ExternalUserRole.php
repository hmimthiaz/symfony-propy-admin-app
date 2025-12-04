<?php

namespace App\Classes\Enum;

use App\Classes\Interface\EnumInterface;

enum ExternalUserRole: string implements EnumInterface
{
    case NO_ACCESS = 'forbidden';

    case STAFF = 'staff';

    case SUPPORT = 'support';

    case EDITOR = 'editor';

    case MANAGER = 'manager';

    case ADMIN = 'admin';

    public static function getStatuses(): array
    {
        return [
            self::NO_ACCESS->value,
            self::STAFF->value,
            self::SUPPORT->value,
            self::EDITOR->value,
            self::MANAGER->value,
            self::ADMIN->value,
        ];
    }

    public static function getCaption(string $type): string
    {
        $captions = [
            self::NO_ACCESS->value => 'No Access',
            self::STAFF->value => 'Staff',
            self::SUPPORT->value => 'Support',
            self::EDITOR->value => 'Editor',
            self::MANAGER->value => 'Manager',
            self::ADMIN->value => 'Admin',
        ];

        return $captions[$type] ?? 'Unknown';
    }

    public static function isValid(string $type): bool
    {
        return in_array($type, self::getStatuses());
    }
}
