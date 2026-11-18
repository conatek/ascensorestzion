<?php

namespace App\Models;

use App\Models\Concerns\TracksAuthor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Lo que cotizamos: un repuesto, insumo, servicio o mano de obra. Existe una
 * sola vez y cada proveedor cuelga de el su oferta (SupplierItem).
 */
class SupplyItem extends Model
{
    use SoftDeletes, TracksAuthor;

    public const TYPES = ['repuesto', 'insumo', 'servicio', 'mano_obra'];

    private const CODE_PREFIX = [
        'repuesto' => 'REP',
        'insumo' => 'INS',
        'servicio' => 'SRV',
        'mano_obra' => 'MO',
    ];

    protected $fillable = [
        'code',
        'name',
        'description',
        'item_type',
        'category',
        'manufacturer_ref',
        'compatible_brands',
        'compatible_models',
        'unit',
        'default_markup_percent',
        'tax_rate',
        'base_price',
        'image_path',
        'notes',
        'active',
        'is_example',
    ];

    // Los mismos valores por defecto de la tabla, para que un item recien creado
    // los tenga en memoria (el IVA se usa al guardar su primer precio).
    protected $attributes = [
        'item_type' => 'repuesto',
        'unit' => 'und',
        'tax_rate' => 19,
        'active' => true,
    ];

    protected $casts = [
        'compatible_brands' => 'array',
        'default_markup_percent' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'base_price' => 'decimal:2',
        'active' => 'boolean',
        'is_example' => 'boolean',
    ];

    protected static function booted(): void
    {
        // El codigo interno sale del id: REP-00012, SRV-00013...
        static::created(function (SupplyItem $item) {
            if (! $item->code) {
                $prefix = self::CODE_PREFIX[$item->item_type] ?? 'ITM';
                $item->code = sprintf('%s-%05d', $prefix, $item->id);
                $item->saveQuietly();
            }
        });
    }

    /**
     * La unidad es texto libre (cada master puede escribir una nueva), asi que se
     * guarda siempre igual: sin espacios sobrantes y en minusculas. "UND " y "und"
     * son la misma unidad.
     */
    public function setUnitAttribute(?string $value): void
    {
        $unit = mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $value)));

        $this->attributes['unit'] = $unit !== '' ? $unit : 'und';
    }

    public function offers(): HasMany
    {
        return $this->hasMany(SupplierItem::class);
    }
}
