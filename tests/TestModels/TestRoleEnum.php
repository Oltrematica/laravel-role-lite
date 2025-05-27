<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Tests\TestModels;

enum TestRoleEnum: string
{
    case ADMIN = 'admin-enum-role';
    case EDITOR = 'editor-enum-role';
    case VIEWER = 'viewer-enum-role';
}
