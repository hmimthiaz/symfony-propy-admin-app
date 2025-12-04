<?php

namespace App\Classes\Interface;

interface EnumInterface
{
    public static function getStatuses(): array;

    public static function getCaption(string $type): string;

    public static function isValid(string $type): bool;
}
