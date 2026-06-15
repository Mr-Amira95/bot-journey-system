<?php

namespace App\Enums;

enum TaskLogAction: string
{
    case Created          = 'created';
    case Updated          = 'updated';
    case StatusChanged    = 'status_changed';
    case Assigned         = 'assigned';
    case CommentAdded     = 'comment_added';
    case AttachmentAdded  = 'attachment_added';
}
