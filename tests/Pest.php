<?php

declare(strict_types=1);

use Oltrematica\RoleLite\Tests\TestCase;
use Oltrematica\RoleLite\Tests\UnitTestCase;

uses(TestCase::class)->in(__DIR__.'/Feature');

/**
 * it loads UnitTestCase for the Unit tests
 * This TestCase doesn't load any service provider
 */
uses(UnitTestCase::class)->in(__DIR__.'/Unit');
