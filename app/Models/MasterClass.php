<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterClass extends Model
{
    public const ALLOWED_SLOTS = ['09:00', '11:00', '13:00', '15:00'];

    protected $fillable = [
        'category_id',
        'master_id',
        'title',
        'description',
        'date',
        'time_slot',
        'capacity',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    protected function date(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => CarbonImmutable::parse($value),
            set: fn (string $value) => CarbonImmutable::parse($value)->toDateString(),
        );
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function master(): BelongsTo
    {
        return $this->belongsTo(User::class, 'master_id');
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bookings')
            ->withTimestamps();
    }

    public static function slotLabel(string $slot): string
    {
        $end = (int) substr($slot, 0, 2) + 2;

        return $slot.'-'.sprintf('%02d:00', $end);
    }

    public function availablePlaces(): int
    {
        $booked = $this->bookings_count ?? $this->bookings()->count();

        return max(0, $this->capacity - $booked);
    }
}
