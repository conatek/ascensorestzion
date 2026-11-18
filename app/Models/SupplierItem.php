<?php

namespace App\Models;

use App\Models\Concerns\TracksAuthor;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * La oferta vigente de un proveedor para un item: su precio (sin IVA), moneda,
 * plazo y la fecha en que nos lo dio. Cada cambio de precio deja una fila en
 * supplier_price_logs.
 */
class SupplierItem extends Model
{
    use TracksAuthor;

    public const SOURCES = ['lista', 'cotizacion', 'factura', 'telefono', 'importacion'];

    /** A partir de cuantos dias un precio se considera desactualizado. */
    public const STALE_DAYS = 90;

    protected $fillable = [
        'supplier_id',
        'supply_item_id',
        'supplier_ref',
        'price',
        'currency',
        'min_quantity',
        'lead_time_days',
        'price_date',
        'valid_until',
        'source',
        'is_preferred',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'min_quantity' => 'decimal:2',
        'lead_time_days' => 'integer',
        'is_preferred' => 'boolean',
    ];

    protected $appends = ['is_stale'];

    /**
     * Fechas como "Y-m-d" pelado y no con el cast `date` de Eloquent, que
     * escribe "00:00:00" y rompe las comparaciones por fecha en sqlite (ya paso
     * en schedule_exceptions). Hacia fuera salen como cadena "Y-m-d".
     */
    protected function priceDate(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => CarbonImmutable::parse($value)->toDateString(),
        );
    }

    protected function validUntil(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value ? CarbonImmutable::parse($value)->toDateString() : null,
        );
    }

    /** Precio viejo (mas de STALE_DAYS dias) o con la vigencia del proveedor vencida. */
    protected function isStale(): Attribute
    {
        return Attribute::make(get: function () {
            $today = CarbonImmutable::today();

            if ($this->valid_until && CarbonImmutable::parse($this->valid_until)->lt($today)) {
                return true;
            }

            return $this->price_date
                && CarbonImmutable::parse($this->price_date)->diffInDays($today) > self::STALE_DAYS;
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function supplyItem(): BelongsTo
    {
        return $this->belongsTo(SupplyItem::class)->withTrashed();
    }

    public function priceLogs(): HasMany
    {
        return $this->hasMany(SupplierPriceLog::class)->orderByDesc('id');
    }
}
