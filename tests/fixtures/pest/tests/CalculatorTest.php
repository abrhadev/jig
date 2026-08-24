<?php

declare(strict_types=1);

use Fixture\Calculator;

test('adds two integers', function (): void {
    expect(new Calculator()->add(2, 3))->toBe(5);
});
