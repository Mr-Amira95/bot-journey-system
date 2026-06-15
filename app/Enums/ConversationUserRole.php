<?php

namespace App\Enums;

enum ConversationUserRole: string
{
    case Member = 'member';
    case Admin  = 'admin';
}
