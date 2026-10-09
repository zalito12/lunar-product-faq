<?php

namespace Gongarce\ProductFaq\Events;

enum ProductFaqChangeReason: string
{
    case QuestionUpdated = 'question_updated';
    case QuestionDeleted = 'question_deleted';
    case ProductAttached = 'product_attached';
    case ProductDetached = 'product_detached';
    case PositionChanged = 'position_changed';
}
