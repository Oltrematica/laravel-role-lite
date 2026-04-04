<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Tests\TestModels;

enum TestPermissionEnum: string
{
    case VIEW_USERS = 'user.view';
    case CREATE_USERS = 'user.create';
    case EDIT_POSTS = 'post.update';
}
