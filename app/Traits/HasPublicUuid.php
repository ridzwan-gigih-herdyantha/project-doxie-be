<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Adds a public-facing, non-guessable `uuid` column to a model while keeping
 * the auto-incrementing integer primary key for internal relations.
 *
 * The `uuid` is generated on creation, used as the route key, and should be
 * exposed via the API in place of the sequential `id`.
 */
trait HasPublicUuid
{
    use HasUuids;

    /**
     * The columns that should receive a generated UUID.
     *
     * Overriding this to target `uuid` (instead of the primary key) keeps the
     * integer `id` as an auto-incrementing primary key.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Resolve route-model bindings by the public UUID instead of the `id`.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
