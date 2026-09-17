<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Notification;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_name',
        'nit',
        'contact_name',
        'contact_email',
        'notification_emails',
        'contact_phone',
        'whatsapp_phone',
        'address',
        'city',
        'department',
        'notes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'notification_emails' => 'array',
    ];

    public function sites()
    {
        return $this->hasMany(Site::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'client_id');
    }

    public function equipment()
    {
        return $this->hasManyThrough(Equipment::class, Site::class);
    }

    /**
     * Los correos de notificación limpios (sin vacíos ni espacios).
     *
     * @return array<int, string>
     */
    public function notificationEmails(): array
    {
        return collect($this->notification_emails ?? [])
            ->map(fn ($e) => trim((string) $e))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * A quién van las notificaciones de este cliente: sus usuarios con acceso
     * (login, activos) MÁS los correos de notificación que no correspondan ya a
     * uno de esos usuarios. Sin duplicados. Los correos sueltos se envuelven en un
     * notifiable anónimo (solo canal mail).
     *
     * @return array<int, \App\Models\User|\Illuminate\Notifications\AnonymousNotifiable>
     */
    public function notificationTargets(): array
    {
        $users = $this->users()->where('active', true)->get();

        $seen = $users->pluck('email')
            ->filter()
            ->map(fn ($e) => mb_strtolower(trim($e)))
            ->all();

        $targets = $users->all();

        foreach ($this->notificationEmails() as $email) {
            $key = mb_strtolower($email);
            if (! in_array($key, $seen, true)) {
                $targets[] = Notification::route('mail', $email);
                $seen[] = $key;
            }
        }

        return $targets;
    }
}
