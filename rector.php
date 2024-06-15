<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\EarlyReturn\Rector\If_\ChangeOrIfContinueToMultiContinueRector;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\Php56\Rector\FuncCall\PowToExpRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([__DIR__ . '/src']);

    $rectorConfig->rules([InlineConstructorDefaultToPropertyRector::class]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::TYPE_DECLARATION,
        SetList::PRIVATIZATION,
    ]);

    // The list of Rector rules are available here:
    // https://github.com/rectorphp/rector/blob/main/docs/rector_rules_overview.md
    $rectorConfig->skip([
        PowToExpRector::class,
        SimplifyIfElseToTernaryRector::class,
        RemoveAlwaysElseRector::class,
        ChangeOrIfContinueToMultiContinueRector::class,
        UnusedForeachValueToArrayKeysRector::class,
    ]);
};
