@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
{{-- Logo servido por URL pública (no base64): Gmail y otros clientes bloquean las
     imágenes data: en el cuerpo del correo. El PNG es blanco, para la cabecera verde. --}}
<img src="{{ rtrim(config('app.url'), '/') }}/images/logo/logo-atzion-white.png" alt="Ascensores Tzion" style="height: 45px; width: auto; max-height: 45px;">
</a>
</td>
</tr>
