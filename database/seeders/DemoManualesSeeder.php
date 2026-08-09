<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\Company;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceReport;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Completa la base de demostracion con lo que hace falta para fotografiar los
 * manuales de usuario: fotos de las personas, un nombre real para el super y un
 * catalogo de tarjetas digitales que valga la pena enseñar.
 *
 * Es IDEMPOTENTE: se puede correr las veces que haga falta. Usa updateOrCreate y
 * no borra nada que no haya creado el.
 *
 * Las fotos salen de notes/demo/images-reference (fuera de git) y se copian a
 * public/images/demo-avatars. Deliberadamente NO se suben a Cloudinary: son datos
 * de demostracion, gastarian cuota y el manual se regenera muchas veces.
 *
 *   php artisan db:seed --class=DemoManualesSeeder
 */
class DemoManualesSeeder extends Seeder
{
    private const ORIGEN = 'notes/demo/images-reference';

    private const DESTINO = 'images/demo-avatars';

    /**
     * A quien le toca cada retrato.
     *
     * Las que empiezan por `ex-` son retratos formales de oficina; el resto son
     * casuales. Por eso los cargos de direccion y coordinacion llevan `ex-` y los
     * tecnicos van con las casuales, que es como se ven en campo.
     */
    private const RETRATOS = [
        'master@ascensorestzion.com' => 'ex-m57',
        'coordinador@ascensorestzion.com' => 'ex-f35',
        'super@ascensorestzion.com' => 'ex-m49',
        'tecnico1@ascensorestzion.com' => 'm30',
        'tecnico2@ascensorestzion.com' => 'm42',
        'admin@ccoviedo.com' => 'f34',
        'administracion@torrenorte.co' => 'm51',
        'mantenimiento@sanvicente.org' => 'f49',
        'demo@ascensorestzion.com' => 'ex-f21',
    ];

    public function run(): void
    {
        $this->copiarRetratos();
        $this->asignarFotos();
        $this->nombrarSuper();
        $this->sembrarFirmasPendientes();
        $this->sembrarTarjetas();
        $this->sembrarServicios();
        $this->sembrarProductos();
    }

    /**
     * Deja al tecnico con dos informes firmados por el, esperando la firma del
     * cliente, de una misma visita reciente.
     *
     * Es el escenario de la firma diferida en lote —dos equipos de una sede en
     * una sola visita— y sin el la pantalla "Firmas pendientes" sale vacia. En la
     * base sembrada los ocho informes en ese estado no tienen visit_uuid y son de
     * hace mas de noventa dias, asi que el endpoint los descarta.
     */
    private function sembrarFirmasPendientes(): void
    {
        $tecnico = User::where('email', 'tecnico1@ascensorestzion.com')->first();

        if (! $tecnico) {
            return;
        }

        $yaHay = ServiceReport::where('technician_id', $tecnico->id)
            ->where('status', 'firmado_tecnico')
            ->whereNotNull('visit_uuid')
            ->where('service_date', '>=', now()->subDays(80))
            ->count();

        if ($yaHay >= 2) {
            $this->command->info('Firmas pendientes: ya hay '.$yaHay);

            return;
        }

        // Dos equipos de la misma sede: es lo que hace interesante el lote.
        $candidatos = ServiceReport::where('technician_id', $tecnico->id)
            ->where('status', 'cerrado')
            ->whereNotNull('site_id')
            ->orderByDesc('service_date')
            ->get()
            ->groupBy('site_id')
            ->first(fn ($grupo) => $grupo->count() >= 2)
            ?->take(2);

        if (! $candidatos || $candidatos->count() < 2) {
            $this->command->warn('Sin informes para preparar las firmas pendientes.');

            return;
        }

        $uuid = (string) Str::uuid();

        foreach ($candidatos as $informe) {
            $informe->update([
                'status' => 'firmado_tecnico',
                'visit_uuid' => $uuid,
                'service_date' => now()->subDay()->toDateString(),
                'customer_signature_path' => null,
                'customer_signed_at' => null,
                'customer_signer_name' => null,
                'customer_signer_document' => null,
            ]);
        }

        $this->command->info('Firmas pendientes preparadas: '.$candidatos->count().' informes, visita '.substr($uuid, 0, 8));
    }

    /**
     * Copia los retratos a public/ recortados a cuadrado, que es como los pinta
     * la aplicacion. Sin recortar, un retrato vertical sale deformado en el avatar.
     */
    private function copiarRetratos(): void
    {
        $origen = base_path(self::ORIGEN);
        $destino = public_path(self::DESTINO);

        if (! File::isDirectory($origen)) {
            $this->command->warn("No existe {$origen}: se omiten las fotos.");

            return;
        }

        File::ensureDirectoryExists($destino);
        $copiados = 0;

        foreach (File::files($origen) as $archivo) {
            if (! in_array(strtolower($archivo->getExtension()), ['png', 'jpg', 'jpeg'], true)) {
                continue;
            }

            $salida = $destino.'/'.$archivo->getFilenameWithoutExtension().'.jpg';
            $this->recortarCuadrado($archivo->getPathname(), $salida);
            $copiados++;
        }

        $this->command->info("Retratos copiados: {$copiados} → public/".self::DESTINO);
    }

    /**
     * Recorta a cuadrado con GD (no hay intervention/image en el proyecto).
     *
     * El recorte vertical no va centrado sino desplazado hacia arriba: en un
     * retrato la cara esta en el tercio superior y un recorte centrado la corta
     * por la frente.
     */
    private function recortarCuadrado(string $origen, string $destino, int $lado = 480): void
    {
        $info = getimagesize($origen);
        $imagen = match ($info[2]) {
            IMAGETYPE_PNG => imagecreatefrompng($origen),
            IMAGETYPE_JPEG => imagecreatefromjpeg($origen),
            default => null,
        };

        if (! $imagen) {
            return;
        }

        [$ancho, $alto] = $info;
        $corte = min($ancho, $alto);
        $x = (int) (($ancho - $corte) / 2);
        $y = (int) (($alto - $corte) * 0.18);

        $cuadrado = imagecreatetruecolor($lado, $lado);
        imagefill($cuadrado, 0, 0, imagecolorallocate($cuadrado, 255, 255, 255));
        imagecopyresampled($cuadrado, $imagen, 0, 0, $x, $y, $lado, $lado, $corte, $corte);
        imagejpeg($cuadrado, $destino, 88);

        imagedestroy($imagen);
        imagedestroy($cuadrado);
    }

    private function asignarFotos(): void
    {
        $asignadas = 0;

        foreach (self::RETRATOS as $email => $retrato) {
            $usuario = User::where('email', $email)->first();

            if (! $usuario) {
                $this->command->warn("Sin usuario para {$email}");

                continue;
            }

            $ruta = self::DESTINO."/{$retrato}.jpg";

            if (! File::exists(public_path($ruta))) {
                $this->command->warn("Falta el retrato {$retrato}");

                continue;
            }

            // Ruta relativa: el front la usa tal cual como src y funciona igual
            // que una URL de Cloudinary, sin depender de la red.
            $usuario->update(['image_url' => '/'.$ruta, 'image_public_id' => null]);
            $asignadas++;
        }

        $this->command->info("Fotos asignadas: {$asignadas}");
    }

    /**
     * "Super Administrador" es un marcador de posicion y sale en cada captura del
     * manual de ese rol. Se le pone nombre de persona.
     */
    private function nombrarSuper(): void
    {
        $super = User::where('email', 'super@ascensorestzion.com')->first();

        if ($super && str_contains(strtolower($super->name), 'super admin')) {
            $super->update([
                'name' => 'Ricardo Duque Ospina',
                'phone' => $super->phone ?: '+57 310 555 0010',
            ]);
            $this->command->info('Super renombrado a Ricardo Duque Ospina');
        }
    }

    /** Una tarjeta por persona del equipo interno, con su retrato. */
    private function sembrarTarjetas(): void
    {
        $empresa = Company::first();

        if (! $empresa) {
            $this->command->warn('No hay empresa: se omiten las tarjetas.');

            return;
        }

        $tarjetas = [
            ['Antonio', 'Contreras', 'antoniocontreras', 'Gerente General', 'ex-m57', '+57 310 555 0001',
                'Dirección general de Ascensores Tzion. Contratos, propuestas y relación con clientes corporativos.'],
            ['Ricardo', 'Duque Ospina', 'ricardoduque', 'Director de Operación', 'ex-m49', '+57 310 555 0010',
                'Planeación y control de la operación de mantenimiento en todo el parque de equipos.'],
            ['Sandra', 'Mejía', 'sandramejia', 'Coordinadora de Servicio', 'ex-f35', '+57 310 555 0002',
                'Programación de visitas, asignación de técnicos y seguimiento del cumplimiento de contratos.'],
            ['Juan David', 'Ríos', 'juandavidrios', 'Técnico de Mantenimiento', 'm30', '+57 310 555 0003',
                'Mantenimiento preventivo y correctivo de ascensores. Atención de emergencias 24/7.'],
            ['Andrés Felipe', 'Cardona', 'andresfelipecardona', 'Técnico de Mantenimiento', 'm42', '+57 310 555 0004',
                'Mantenimiento preventivo, modernizaciones y adecuaciones de equipos.'],
        ];

        foreach ($tarjetas as $i => [$nombre, $apellido, $slug, $cargo, $retrato, $telefono, $descripcion]) {
            Card::updateOrCreate(
                ['slug' => $slug],
                [
                    'company_id' => $empresa->id,
                    'first_name' => $nombre,
                    'last_name' => $apellido,
                    'job_title' => $cargo,
                    'photo_path' => '/'.self::DESTINO."/{$retrato}.jpg",
                    'mobile_phone' => $telefono,
                    'whatsapp' => $telefono,
                    'email' => 'contacto@ascensorestzion.com',
                    'whatsapp_message' => "Hola {$nombre}, vi tu tarjeta de Ascensores Tzion y quiero más información.",
                    'description' => $descripcion,
                    'is_active' => true,
                ],
            );
        }

        $this->command->info('Tarjetas: '.count($tarjetas));
    }

    private function sembrarServicios(): void
    {
        $empresa = Company::first();

        if (! $empresa) {
            return;
        }

        $servicios = [
            ['Mantenimiento preventivo', 1,
                '<p>Plan periódico de <strong>limpieza, revisión, control y ajuste</strong> de todos los componentes '.
                'del equipo, conforme a las normas <strong>NTC 5926</strong> y <strong>NTC 2503</strong>. '.
                'Cada visita queda documentada con informe firmado y fotografías.</p>'],
            ['Atención de emergencias 24/7', 2,
                '<p>Línea disponible <strong>las veinticuatro horas</strong> para atrapamientos y fallas críticas. '.
                'El sistema registra la hora de la llamada y calcula el tiempo de respuesta real de cada atención.</p>'],
            ['Mantenimiento correctivo', 3,
                '<p>Diagnóstico y reparación de fallas, con <strong>codificación de la causa</strong> y '.
                'cotización de repuestos cuando aplica. Todo queda en la hoja de vida del equipo.</p>'],
            ['Modernización de equipos', 4,
                '<p>Actualización de maniobra, puertas, cabina y sistemas de seguridad en equipos antiguos, '.
                'con <strong>cronograma y entregables definidos</strong> antes de empezar.</p>'],
            ['Inspección y certificación', 5,
                '<p>Inspección técnica y acompañamiento en los <strong>trámites de certificación</strong> '.
                'exigidos a la copropiedad, con el soporte documental completo.</p>'],
        ];

        foreach ($servicios as [$nombre, $orden, $descripcion]) {
            Service::updateOrCreate(
                ['company_id' => $empresa->id, 'name' => $nombre],
                ['description' => $descripcion, 'order' => $orden, 'is_active' => true],
            );
        }

        // El servicio generico original queda redundante con los cinco nuevos.
        Service::where('company_id', $empresa->id)->where('name', 'Mantenimiento')->delete();

        $this->command->info('Servicios: '.count($servicios));
    }

    private function sembrarProductos(): void
    {
        $empresa = Company::first();

        if (! $empresa) {
            return;
        }

        $productos = [
            ['Botonera de cabina en acero inoxidable', 1, 890000, 0,
                'Botonera antivandálica con braille y señal sonora de piso, conforme a la norma de accesibilidad.'],
            ['Operador de puertas con variador', 2, 4250000, 10,
                'Apertura y cierre suaves, con detección de obstáculos por cortina infrarroja de 154 haces.'],
            ['Cortina de seguridad infrarroja', 3, 1180000, 0,
                'Detiene el cierre ante cualquier obstáculo en el vano. Instalación en menos de una jornada.'],
            ['Sistema de rescate automático (ARD)', 4, 3600000, 0,
                'Ante un corte de energía lleva la cabina al piso más cercano y abre las puertas.'],
            ['Tablero de maniobra electrónico', 5, 7900000, 5,
                'Reemplazo de maniobra relevada por control electrónico, con diagnóstico de fallas en pantalla.'],
            ['Kit de iluminación LED de cabina', 6, 620000, 0,
                'Reduce el consumo y mejora la percepción de seguridad del usuario. Incluye luz de emergencia.'],
        ];

        foreach ($productos as [$nombre, $orden, $precio, $descuento, $descripcion]) {
            Product::updateOrCreate(
                ['company_id' => $empresa->id, 'name' => $nombre],
                [
                    'price' => $precio,
                    'discount' => $descuento,
                    'description' => "<p>{$descripcion}</p>",
                    'order' => $orden,
                    'is_active' => true,
                ],
            );
        }

        $this->command->info('Productos: '.count($productos));
    }
}
