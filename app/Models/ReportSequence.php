<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReportSequence extends Model
{
    protected $fillable = ['type', 'year', 'last_number'];

    public static function nextNumber(string $type): string
    {
        return DB::transaction(function () use ($type) {
            $year = (int) date('Y');

            $sequence = static::lockForUpdate()
                ->where('type', $type)
                ->where('year', $year)
                ->first();

            if (! $sequence) {
                $sequence = static::create([
                    'type' => $type,
                    'year' => $year,
                    'last_number' => 0,
                ]);
            }

            $sequence->increment('last_number');

            return sprintf('%s-%d-%05d', $type, $year, $sequence->last_number);
        });
    }
}
