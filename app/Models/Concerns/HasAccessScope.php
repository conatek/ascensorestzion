<?php

namespace App\Models\Concerns;

/**
 * A que datos llega cada usuario segun su rol. Los permisos de Spatie dicen QUE
 * puede hacer (ver reportes, firmar...); esto dice SOBRE QUE registros:
 *
 *  - Personal de oficina (master, super, coordinator): todo.
 *  - Tecnico: solo lo suyo (sus reportes, sus visitas, sus check-ins y los
 *    equipos que atiende).
 *  - Usuario del cliente (rol admin): solo lo de su cliente.
 *
 * Cualquier otro caso (sin rol, cliente sin cliente asignado) no ve nada.
 */
trait HasAccessScope
{
    public const STAFF_ROLES = ['master', 'super', 'coordinator'];

    public function isStaff(): bool
    {
        return $this->hasAnyRole(self::STAFF_ROLES);
    }

    public function isFieldTechnician(): bool
    {
        return ! $this->isStaff() && $this->hasRole('technician');
    }

    public function isClientUser(): bool
    {
        return ! $this->isStaff() && ! $this->hasRole('technician') && $this->hasRole('admin');
    }

    /**
     * Cliente al que queda limitado un usuario del portal. Null para el personal
     * (sin limite). Un usuario de cliente sin cliente asignado no pasa: antes
     * ese caso se quedaba sin filtro y veia los datos de todos.
     */
    public function clientScopeId(): ?int
    {
        if (! $this->isClientUser()) {
            return null;
        }

        abort_if(! $this->client_id, 403, 'No tienes un cliente asignado.');

        return (int) $this->client_id;
    }
}
