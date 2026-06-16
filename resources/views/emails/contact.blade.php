@component('mail::message')
# Nuevo mensaje de contacto

Recibiste un nuevo mensaje desde el formulario de contacto del sitio web.

**Nombre:** {{ $data['nombre'] }}<br>
**Correo:** {{ $data['email'] }}<br>
@if(!empty($data['telefono']))**Teléfono:** {{ $data['telefono'] }}<br>
@endif
**Asunto:** {{ $data['asunto_label'] }}

**Mensaje:**

{{ $data['mensaje'] }}

@component('mail::button', ['url' => 'mailto:'.$data['email'], 'color' => 'success'])
Responder
@endcomponent

Equipo de Ascensores Tzion
@endcomponent
