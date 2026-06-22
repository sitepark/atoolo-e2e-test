<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Service;

enum RuntimeType: string
{
    case CLI = 'cli';
    case FPM_FCGI = 'fpm-fcgi';
    case WORKER = 'worker';

    /**
     * @return array<RuntimeType>
     */
    public static function otherCases(RuntimeType $type): array
    {
        $cases = [];
        foreach (self::cases() as $other) {
            if ($type !== $other) {
                $cases[] = $other;
            }
        }
        return $cases;
    }
}
