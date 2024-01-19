<?php

namespace Clickbar\AgGrid\Support;

use Illuminate\Database\Eloquent\Model;

class RelationMetadata
{
    public function __construct(
        public string $name,
        public Model $model,
    ) {
    }
}
