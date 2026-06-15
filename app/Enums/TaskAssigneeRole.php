<?php

namespace App\Enums;

enum TaskAssigneeRole: string
{
    case Assignee = 'assignee';
    case Reviewer = 'reviewer';
    case Watcher  = 'watcher';
}
