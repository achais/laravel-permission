<?php

namespace Achais\Permission\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface Menu
{
    public function roles(): BelongsToMany;

    public function parent(): BelongsTo;

    public function children(): HasMany;

    public static function findByName(string $name): self;

    public static function findById(int $id): self;
}
