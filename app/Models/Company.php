<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'logo_path',
        'logo_public_id',
        'address',
        'web',
        'my_business',
        'facebook',
        'instagram',
        'twitter',
        'youtube',
        'tiktok',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class)->orderBy('order');
    }

    public function products()
    {
        return $this->hasMany(Product::class)->orderBy('order');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function settings()
    {
        return $this->hasOne(CompanySetting::class);
    }

    /**
     * Obtener o crear settings con valores por defecto
     */
    public function getOrCreateSettings(): CompanySetting
    {
        $settings = $this->settings()->first();

        if (!$settings) {
            $templateName = 'modern';
            $defaults = config("templates.schemas.{$templateName}", []);

            // Extraer solo los valores del schema
            $values = [];
            foreach ($defaults as $sectionKey => $section) {
                if (!is_array($section)) continue;
                $values[$sectionKey] = [];
                foreach ($section as $fieldKey => $field) {
                    if (str_starts_with($fieldKey, '_')) continue;
                    if (is_array($field) && isset($field['value'])) {
                        $values[$sectionKey][$fieldKey] = $field['value'];
                    }
                }
            }

            $settings = $this->settings()->create([
                'template_name' => $templateName,
                'customization' => $values,
            ]);
        }

        return $settings;
    }
}
