<?php

namespace Clickbar\AgGrid\Contracts;

interface AgGridValueGetter
{
    public function get(mixed $data): mixed;
}
