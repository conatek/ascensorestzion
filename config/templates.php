<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Plantillas Disponibles
    |--------------------------------------------------------------------------
    |
    | Registro de plantillas con sus metadatos y componente Vue asociado.
    |
    */
    'available' => [
        'modern' => [
            'name' => 'Moderna',
            'description' => 'Diseño limpio y profesional con gradientes sutiles',
            'component' => 'TemplateModern',
            'thumbnail' => '/img/templates/modern.png',
        ],
        'classic' => [
            'name' => 'Clásica',
            'description' => 'Estilo tradicional y elegante',
            'component' => 'TemplateClassic',
            'thumbnail' => '/img/templates/classic.png',
        ],
        'minimal' => [
            'name' => 'Minimalista',
            'description' => 'Diseño limpio, enfocado en el contenido y la legibilidad.',
            'component' => 'TemplateMinimal',
            'thumbnail' => '/img/templates/minimal.png',
        ],
        'creative' => [
            'name' => 'Creativa',
            'description' => 'Diseño moderno con efecto glassmorphism y luces ambientales',
            'component' => 'TemplateCreative',
            'thumbnail' => '/img/templates/creative.png',
        ],
        'cyber' => [
            'name' => 'Cyber',
            'description' => 'Diseño tecnológico con efectos de neón y alto contraste',
            'component' => 'TemplateCyber',
            'thumbnail' => '/img/templates/cyber.png',
        ],
        'vibrant' => [
            'name' => 'Vibrante',
            'description' => 'Diseño juvenil con degradados animados y formato Bento Box.',
            'component' => 'TemplateVibrant',
            'thumbnail' => '/img/templates/vibrant.png',
        ],
        'action' => [
            'name' => 'Acción',
            'description' => 'Diseño orientado a la conversión con botones de contacto rápido y video.',
            'component' => 'TemplateAction',
            'thumbnail' => '/img/templates/action.png',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Schemas de Configuración por Plantilla
    |--------------------------------------------------------------------------
    |
    | Define las variables disponibles para personalizar cada plantilla.
    | Estructura: sección > campo > valor por defecto
    |
    | Tipos de campo soportados (usados en el frontend para generar controles):
    | - color: Selector de color (#RRGGBB)
    | - number: Input numérico
    | - select: Dropdown con opciones predefinidas
    | - toggle: Checkbox booleano
    | - range: Slider numérico
    |
    */
    'schemas' => [
        'modern' => [
            // AJUSTES GENERALES
            'general' => [
                '_label' => 'Ajustes Generales',
                'fontFamily' => [
                    'type' => 'select',
                    'label' => 'Tipografía',
                    'value' => 'Montserrat',
                    'options' => ['Montserrat', 'Quicksand', 'Roboto', 'Open Sans', 'Lato', 'Poppins'],
                ],
                'colorFondo' => [
                    'type' => 'color',
                    'label' => 'Color de Fondo',
                    'value' => '#ffffff',
                ],
                'colorFuente' => [
                    'type' => 'color',
                    'label' => 'Color de Fuente (General)',
                    'value' => '#333333',
                ],
            ],

            // CABECERA
            'header' => [
                '_label' => 'Cabecera',
                'contenido' => [
                    'type' => 'select',
                    'label' => 'Mostrar en Cabecera',
                    'value' => 'logo',
                    'options' => ['logo', 'nombre', 'oculto'],
                ],
                'anchoLogo' => [
                    'type' => 'range',
                    'label' => 'Ancho del Logo',
                    'value' => 150,
                    'min' => 80,
                    'max' => 250,
                    'unit' => 'px',
                ],
                'logoBorde' => [
                    'type' => 'range',
                    'label' => 'Grosor Borde Logo',
                    'value' => 0,
                    'min' => 0,
                    'max' => 5,
                    'unit' => 'px',
                ],
                'logoColorBorde' => [
                    'type' => 'color',
                    'label' => 'Color Borde Logo',
                    'value' => '#ffffff',
                ],
                'logoTipoBorde' => [
                    'type' => 'select',
                    'label' => 'Tipo Borde Logo',
                    'value' => 'solid',
                    'options' => ['solid', 'dashed', 'dotted'],
                ],
                'logoRedondeo' => [
                    'type' => 'range',
                    'label' => 'Redondeo Logo',
                    'value' => 0,
                    'min' => 0,
                    'max' => 50,
                    'unit' => 'px',
                ],
                'colorFuente' => [
                    'type' => 'color',
                    'label' => 'Color Nombre Empresa',
                    'value' => '#ffffff',
                ],
                'tamanoFuente' => [
                    'type' => 'range',
                    'label' => 'Tamaño Nombre Empresa',
                    'value' => 1.2,
                    'min' => 0.8,
                    'max' => 2,
                    'step' => 0.1,
                    'unit' => 'em',
                ],
            ],

            // FONDO DE FOTO
            'photoBackground' => [
                '_label' => 'Fondo de Foto',
                'tipo' => [
                    'type' => 'select',
                    'label' => 'Tipo de Fondo',
                    'value' => 'degradado',
                    'options' => ['oculto', 'solido', 'degradado'],
                ],
                'color1' => [
                    'type' => 'color',
                    'label' => 'Color Principal',
                    'value' => '#6366f1',
                ],
                'color2' => [
                    'type' => 'color',
                    'label' => 'Color Secundario (degradado)',
                    'value' => '#8b5cf6',
                ],
                'direccion' => [
                    'type' => 'select',
                    'label' => 'Dirección Degradado',
                    'value' => 'diagonal',
                    'options' => ['horizontal', 'vertical', 'diagonal'],
                ],
                'mostrarPatron' => [
                    'type' => 'toggle',
                    'label' => 'Aplicar Patrón',
                    'value' => false,
                ],
                'patron' => [
                    'type' => 'select',
                    'label' => 'Tipo de Patrón',
                    'value' => 'circulos',
                    'options' => ['circulos', 'lineas', 'puntos', 'ondas', 'geometrico'],
                ],
            ],

            // FOTO DE PERFIL
            'photo' => [
                '_label' => 'Foto de Perfil',
                'mostrar' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar Foto de Perfil',
                    'value' => true,
                ],
                'tamano' => [
                    'type' => 'range',
                    'label' => 'Tamaño',
                    'value' => 120,
                    'min' => 80,
                    'max' => 150,
                    'unit' => 'px',
                ],
                'radio' => [
                    'type' => 'range',
                    'label' => 'Redondeo de Esquinas',
                    'value' => 15,
                    'min' => 0,
                    'max' => 100,
                    'unit' => 'px',
                ],
                'tamanoBorde' => [
                    'type' => 'range',
                    'label' => 'Grosor del Borde',
                    'value' => 3,
                    'min' => 0,
                    'max' => 10,
                    'unit' => 'px',
                ],
                'colorBorde' => [
                    'type' => 'color',
                    'label' => 'Color del Borde',
                    'value' => '#ffffff',
                ],
                'tipoBorde' => [
                    'type' => 'select',
                    'label' => 'Tipo de Borde',
                    'value' => 'solid',
                    'options' => ['none', 'solid', 'dashed', 'dotted'],
                ],
                'sombra' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar Sombra',
                    'value' => true,
                ],
            ],

            // DATOS PERSONALES
            'profile' => [
                '_label' => 'Datos Personales',
                'colorFondo' => [
                    'type' => 'color',
                    'label' => 'Color de Fondo',
                    'value' => 'transparent',
                ],
                'colorBorde' => [
                    'type' => 'color',
                    'label' => 'Color del Borde',
                    'value' => 'transparent',
                ],
                'tipoBorde' => [
                    'type' => 'select',
                    'label' => 'Tipo de Borde',
                    'value' => 'none',
                    'options' => ['none', 'solid', 'dashed', 'dotted'],
                ],
                'tamanoBorde' => [
                    'type' => 'range',
                    'label' => 'Grosor del Borde',
                    'value' => 0,
                    'min' => 0,
                    'max' => 5,
                    'unit' => 'px',
                ],
                'radio' => [
                    'type' => 'range',
                    'label' => 'Radio del Borde',
                    'value' => 0,
                    'min' => 0,
                    'max' => 20,
                    'unit' => 'px',
                ],
                // Nombre
                'nombreTamano' => [
                    'type' => 'range',
                    'label' => 'Tamaño Nombre',
                    'value' => 1.5,
                    'min' => 1,
                    'max' => 3,
                    'step' => 0.1,
                    'unit' => 'em',
                ],
                'nombreColor' => [
                    'type' => 'color',
                    'label' => 'Color Nombre',
                    'value' => '#333333',
                ],
                'nombrePeso' => [
                    'type' => 'select',
                    'label' => 'Peso Nombre',
                    'value' => '600',
                    'options' => ['300', '400', '500', '600', '700', '800'],
                ],
                // Apellido
                'apellidoTamano' => [
                    'type' => 'range',
                    'label' => 'Tamaño Apellido',
                    'value' => 1.5,
                    'min' => 1,
                    'max' => 3,
                    'step' => 0.1,
                    'unit' => 'em',
                ],
                'apellidoColor' => [
                    'type' => 'color',
                    'label' => 'Color Apellido',
                    'value' => '#555555',
                ],
                'apellidoPeso' => [
                    'type' => 'select',
                    'label' => 'Peso Apellido',
                    'value' => '400',
                    'options' => ['300', '400', '500', '600', '700', '800'],
                ],
                // Cargo
                'cargoTamano' => [
                    'type' => 'range',
                    'label' => 'Tamaño Cargo',
                    'value' => 1,
                    'min' => 0.8,
                    'max' => 1.5,
                    'step' => 0.1,
                    'unit' => 'em',
                ],
                'cargoColor' => [
                    'type' => 'color',
                    'label' => 'Color Cargo',
                    'value' => '#666666',
                ],
                'cargoPeso' => [
                    'type' => 'select',
                    'label' => 'Peso Cargo',
                    'value' => '400',
                    'options' => ['300', '400', '500', '600', '700', '800'],
                ],
                'sombra' => [
                    'type' => 'toggle',
                    'label' => 'Sombra en Texto',
                    'value' => false,
                ],
            ],

            // REDES SOCIALES
            'social' => [
                '_label' => 'Redes Sociales',
                'colorIcono' => [
                    'type' => 'color',
                    'label' => 'Color de Iconos',
                    'value' => '#ffffff',
                ],
                'radio' => [
                    'type' => 'range',
                    'label' => 'Radio de Botones',
                    'value' => 50,
                    'min' => 0,
                    'max' => 50,
                    'unit' => '%',
                ],
                'sombra' => [
                    'type' => 'toggle',
                    'label' => 'Sombra en Botones',
                    'value' => false,
                ],
                // Colores de fondo por red social
                'fondoFacebook' => [
                    'type' => 'color',
                    'label' => 'Fondo Facebook',
                    'value' => '#3b5998',
                ],
                'fondoInstagram' => [
                    'type' => 'color',
                    'label' => 'Fondo Instagram',
                    'value' => '#e4405f',
                ],
                'fondoTwitter' => [
                    'type' => 'color',
                    'label' => 'Fondo Twitter/X',
                    'value' => '#1da1f2',
                ],
                'fondoLinkedin' => [
                    'type' => 'color',
                    'label' => 'Fondo LinkedIn',
                    'value' => '#0077b5',
                ],
                'fondoYoutube' => [
                    'type' => 'color',
                    'label' => 'Fondo YouTube',
                    'value' => '#ff0000',
                ],
                'fondoWhatsapp' => [
                    'type' => 'color',
                    'label' => 'Fondo WhatsApp',
                    'value' => '#25d366',
                ],
                'fondoEmail' => [
                    'type' => 'color',
                    'label' => 'Fondo Email',
                    'value' => '#ea4335',
                ],
                'fondoCelular' => [
                    'type' => 'color',
                    'label' => 'Fondo Celular',
                    'value' => '#34b7f1',
                ],
                'fondoWeb' => [
                    'type' => 'color',
                    'label' => 'Fondo Web',
                    'value' => '#4285f4',
                ],
                'fondoUbicacion' => [
                    'type' => 'color',
                    'label' => 'Fondo Ubicación',
                    'value' => '#ff5722',
                ],
            ],

            // ACORDEÓN
            'accordion' => [
                '_label' => 'Acordeón (Secciones)',
                'colorFuente' => [
                    'type' => 'color',
                    'label' => 'Color de Fuente',
                    'value' => '#333333',
                ],
                'colorFuenteEnlace' => [
                    'type' => 'color',
                    'label' => 'Color de Enlaces',
                    'value' => '#ffffff',
                ],
                'pesoFuenteEnlace' => [
                    'type' => 'select',
                    'label' => 'Peso de Enlaces',
                    'value' => '500',
                    'options' => ['300', '400', '500', '600', '700'],
                ],
                'tamanoFuenteEnlace' => [
                    'type' => 'range',
                    'label' => 'Tamaño de Enlaces',
                    'value' => 1,
                    'min' => 0.8,
                    'max' => 1.5,
                    'step' => 0.1,
                    'unit' => 'rem',
                ],
                'colorCuerpo' => [
                    'type' => 'color',
                    'label' => 'Color Fondo Contenido',
                    'value' => '#ffffff',
                ],
                'colorBorde' => [
                    'type' => 'color',
                    'label' => 'Color del Borde',
                    'value' => '#e0e0e0',
                ],
                'tipoBorde' => [
                    'type' => 'select',
                    'label' => 'Tipo de Borde',
                    'value' => 'solid',
                    'options' => ['none', 'solid', 'dashed', 'dotted'],
                ],
                'tamanoBorde' => [
                    'type' => 'range',
                    'label' => 'Grosor del Borde',
                    'value' => 1,
                    'min' => 0,
                    'max' => 5,
                    'unit' => 'px',
                ],
                'radio' => [
                    'type' => 'range',
                    'label' => 'Radio de Esquinas',
                    'value' => 8,
                    'min' => 0,
                    'max' => 20,
                    'unit' => 'px',
                ],
                'sombra' => [
                    'type' => 'toggle',
                    'label' => 'Sombra Interior',
                    'value' => false,
                ],
                // Colores de cabecera por sección
                'colorSeccion1' => [
                    'type' => 'color',
                    'label' => 'Color Sección 1 (Quién Soy)',
                    'value' => '#6366f1',
                ],
                'colorSeccion2' => [
                    'type' => 'color',
                    'label' => 'Color Sección 2 (Contacto)',
                    'value' => '#8b5cf6',
                ],
                'colorSeccion3' => [
                    'type' => 'color',
                    'label' => 'Color Sección 3 (Servicios)',
                    'value' => '#a855f7',
                ],
                'colorSeccion4' => [
                    'type' => 'color',
                    'label' => 'Color Sección 4 (Productos)',
                    'value' => '#d946ef',
                ],
                'colorSeccion5' => [
                    'type' => 'color',
                    'label' => 'Color Sección 5 (Galería)',
                    'value' => '#ec4899',
                ],
            ],

            // PIE DE PÁGINA
            'footer' => [
                '_label' => 'Pie de Página',
                // Propiedades de fondo (similar a photoBackground)
                'tipoFondo' => [
                    'type' => 'select',
                    'label' => 'Tipo de Fondo',
                    'value' => 'solido',
                    'options' => ['transparente', 'solido', 'degradado'],
                ],
                'color1' => [
                    'type' => 'color',
                    'label' => 'Color Principal',
                    'value' => '#f5f5f5',
                ],
                'color2' => [
                    'type' => 'color',
                    'label' => 'Color Secundario',
                    'value' => '#e0e0e0',
                ],
                'direccion' => [
                    'type' => 'select',
                    'label' => 'Dirección Degradado',
                    'value' => 'horizontal',
                    'options' => ['horizontal', 'vertical', 'diagonal'],
                ],
                'mostrarPatron' => [
                    'type' => 'toggle',
                    'label' => 'Aplicar Patrón',
                    'value' => false,
                ],
                'patron' => [
                    'type' => 'select',
                    'label' => 'Tipo de Patrón',
                    'value' => 'circulos',
                    'options' => ['circulos', 'lineas', 'puntos', 'ondas', 'geometrico'],
                ],
                // Altura mínima para centrado vertical
                'alturaMinima' => [
                    'type' => 'range',
                    'label' => 'Altura Mínima',
                    'value' => 60,
                    'min' => 40,
                    'max' => 150,
                    'unit' => 'px',
                ],
                // Propiedades de fuente
                'colorFuente' => [
                    'type' => 'color',
                    'label' => 'Color de Fuente',
                    'value' => '#666666',
                ],
                'tamanoFuente' => [
                    'type' => 'range',
                    'label' => 'Tamaño de Fuente',
                    'value' => 0.9,
                    'min' => 0.7,
                    'max' => 1.3,
                    'step' => 0.1,
                    'unit' => 'em',
                ],
                'pesoFuente' => [
                    'type' => 'select',
                    'label' => 'Peso de Fuente',
                    'value' => '400',
                    'options' => ['300', '400', '500', '600', '700'],
                ],
                // Propiedades de borde
                'colorBorde' => [
                    'type' => 'color',
                    'label' => 'Color del Borde',
                    'value' => '#e0e0e0',
                ],
                'tipoBorde' => [
                    'type' => 'select',
                    'label' => 'Tipo de Borde',
                    'value' => 'solid',
                    'options' => ['none', 'solid', 'dashed', 'dotted'],
                ],
                'tamanoBorde' => [
                    'type' => 'range',
                    'label' => 'Grosor del Borde',
                    'value' => 1,
                    'min' => 0,
                    'max' => 5,
                    'unit' => 'px',
                ],
            ],
        ],

        // Plantilla Clásica (diseño elegante con serif)
        'classic' => [
            'general' => [
                '_label' => 'Ajustes Generales',
                'colorFondo' => [
                    'type' => 'color',
                    'label' => 'Color de Fondo',
                    'value' => '#fdfbf7',
                ],
                'colorTexto' => [
                    'type' => 'color',
                    'label' => 'Color de Texto',
                    'value' => '#2c3e50',
                ],
                'colorAcento' => [
                    'type' => 'color',
                    'label' => 'Color de Acento',
                    'value' => '#b89947',
                ],
                'fuenteTitulos' => [
                    'type' => 'select',
                    'label' => 'Tipografía de Títulos',
                    'value' => 'Playfair Display',
                    'options' => ['Playfair Display', 'Merriweather', 'Lora', 'Crimson Text', 'Libre Baskerville'],
                ],
            ],
            'avatar' => [
                '_label' => 'Foto de Perfil',
                'mostrar' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar Foto',
                    'value' => true,
                ],
                'tamano' => [
                    'type' => 'range',
                    'label' => 'Tamaño',
                    'value' => 140,
                    'min' => 80,
                    'max' => 200,
                    'step' => 5,
                    'unit' => 'px',
                ],
                'radio' => [
                    'type' => 'range',
                    'label' => 'Redondeo',
                    'value' => 0,
                    'min' => 0,
                    'max' => 50,
                    'step' => 1,
                    'unit' => '%',
                ],
            ],
            'perfil' => [
                '_label' => 'Alineación de Contenido',
                'alineacion' => [
                    'type' => 'select',
                    'label' => 'Alineación',
                    'value' => 'center',
                    'options' => ['left', 'center', 'right'],
                ],
            ],
            'botones' => [
                '_label' => 'Botones Sociales',
                'estilo' => [
                    'type' => 'select',
                    'label' => 'Estilo de Botones',
                    'value' => 'borde',
                    'options' => ['solido', 'borde', 'minimalista'],
                ],
            ],
        ],

        // Plantilla Minimalista
        'minimal' => [
            'general' => [
                '_label' => 'Estilo Minimalista',
                'colorFondo' => [
                    'type' => 'color',
                    'label' => 'Color de Fondo',
                    'value' => '#ffffff',
                ],
                'colorTexto' => [
                    'type' => 'color',
                    'label' => 'Color Principal de Texto',
                    'value' => '#1a1a1a',
                ],
                'colorAcento' => [
                    'type' => 'color',
                    'label' => 'Color de Acento (Suave)',
                    'value' => '#888888',
                ],
                'tipografia' => [
                    'type' => 'select',
                    'label' => 'Tipografía',
                    'value' => 'Inter',
                    'options' => ['Inter', 'Roboto', 'Helvetica Neue', 'System UI'],
                ],
                'alineacion' => [
                    'type' => 'select',
                    'label' => 'Alineación Global',
                    'value' => 'center',
                    'options' => ['left', 'center'],
                ],
            ],
            'avatar' => [
                '_label' => 'Fotografía',
                'mostrar' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar Fotografía',
                    'value' => true,
                ],
                'tamano' => [
                    'type' => 'range',
                    'label' => 'Tamaño',
                    'value' => 100,
                    'min' => 60,
                    'max' => 180,
                    'step' => 5,
                    'unit' => 'px',
                ],
                'escalaGrises' => [
                    'type' => 'toggle',
                    'label' => 'Filtro Blanco y Negro',
                    'value' => true,
                ],
                'radio' => [
                    'type' => 'range',
                    'label' => 'Redondeo',
                    'value' => 50,
                    'min' => 0,
                    'max' => 50,
                    'step' => 1,
                    'unit' => '%',
                ],
            ],
            'botones' => [
                '_label' => 'Acciones y Redes',
                'estilo' => [
                    'type' => 'select',
                    'label' => 'Estilo de Iconos',
                    'value' => 'fantasma',
                    'options' => ['fantasma', 'contorno', 'solido'],
                ],
            ],
        ],

        // Plantilla Creativa (Glassmorphism)
        'creative' => [
            'general' => [
                '_label' => 'Diseño General',
                'colorFondo' => [
                    'type' => 'color',
                    'label' => 'Color de Fondo Base',
                    'value' => '#0f172a',
                ],
                'colorAcento' => [
                    'type' => 'color',
                    'label' => 'Color de Acento',
                    'value' => '#38bdf8',
                ],
                'fuentePrincipal' => [
                    'type' => 'select',
                    'label' => 'Tipografía',
                    'value' => 'Poppins',
                    'options' => ['Inter', 'Poppins', 'Space Grotesk'],
                ],
            ],
            'header' => [
                '_label' => 'Logo de Empresa',
                'alturaLogo' => [
                    'type' => 'range',
                    'label' => 'Altura del Logo',
                    'value' => 24,
                    'min' => 16,
                    'max' => 60,
                    'step' => 2,
                    'unit' => 'px',
                ],
            ],
            'hero' => [
                '_label' => 'Portada Flotante',
                'mostrarFondoEfecto' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar efecto de luz de fondo',
                    'value' => true,
                ],
                'colorLuzSecundaria' => [
                    'type' => 'color',
                    'label' => 'Color Luz Secundaria',
                    'value' => '#c084fc',
                ],
                'radioBorde' => [
                    'type' => 'range',
                    'label' => 'Redondeo de Tarjeta',
                    'value' => 24,
                    'min' => 0,
                    'max' => 40,
                    'step' => 1,
                    'unit' => 'px',
                ],
            ],
            'glass' => [
                '_label' => 'Efecto Cristal',
                'opacidad' => [
                    'type' => 'range',
                    'label' => 'Opacidad del fondo',
                    'value' => 0.1,
                    'min' => 0,
                    'max' => 1,
                    'step' => 0.05,
                ],
                'desenfoque' => [
                    'type' => 'range',
                    'label' => 'Nivel de desenfoque',
                    'value' => 12,
                    'min' => 0,
                    'max' => 30,
                    'step' => 1,
                    'unit' => 'px',
                ],
            ],
            'avatar' => [
                '_label' => 'Foto de Perfil',
                'mostrar' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar Foto',
                    'value' => true,
                ],
                'tamano' => [
                    'type' => 'range',
                    'label' => 'Tamaño',
                    'value' => 120,
                    'min' => 80,
                    'max' => 150,
                    'step' => 5,
                    'unit' => 'px',
                ],
                'grosorBorde' => [
                    'type' => 'range',
                    'label' => 'Grosor Borde Degradado',
                    'value' => 5,
                    'min' => 0,
                    'max' => 10,
                    'step' => 1,
                    'unit' => 'px',
                ],
            ],
            'profile' => [
                '_label' => 'Datos Personales',
                'nombreTamano' => [
                    'type' => 'range',
                    'label' => 'Tamaño Nombre',
                    'value' => 1.5,
                    'min' => 1,
                    'max' => 2.5,
                    'step' => 0.1,
                    'unit' => 'rem',
                ],
                'nombreColor' => [
                    'type' => 'color',
                    'label' => 'Color Nombre',
                    'value' => '#ffffff',
                ],
                'cargoTamano' => [
                    'type' => 'range',
                    'label' => 'Tamaño Cargo',
                    'value' => 0.95,
                    'min' => 0.7,
                    'max' => 1.3,
                    'step' => 0.05,
                    'unit' => 'rem',
                ],
            ],
            'acciones' => [
                '_label' => 'Botones de Acción',
                'tamanoBoton' => [
                    'type' => 'range',
                    'label' => 'Tamaño de Botones',
                    'value' => 50,
                    'min' => 40,
                    'max' => 70,
                    'step' => 2,
                    'unit' => 'px',
                ],
                'radioBoton' => [
                    'type' => 'range',
                    'label' => 'Redondeo de Botones',
                    'value' => 50,
                    'min' => 0,
                    'max' => 50,
                    'step' => 5,
                    'unit' => '%',
                ],
            ],
        ],

        // Plantilla Cyber (Neón tecnológico)
        'cyber' => [
            'general' => [
                '_label' => 'Estilo Cyber',
                'colorFondo' => [
                    'type' => 'color',
                    'label' => 'Fondo Principal',
                    'value' => '#0a0a1a',
                ],
                'colorTexto' => [
                    'type' => 'color',
                    'label' => 'Texto Principal',
                    'value' => '#e2e8f0',
                ],
                'fuentePrincipal' => [
                    'type' => 'select',
                    'label' => 'Tipografía',
                    'value' => 'Space Mono',
                    'options' => ['Space Mono', 'Fira Code', 'Roboto Mono', 'Courier New'],
                ],
            ],
            'neon' => [
                '_label' => 'Efectos Neón',
                'colorAcento' => [
                    'type' => 'color',
                    'label' => 'Color Neón (Bordes y Botones)',
                    'value' => '#00ffcc',
                ],
                'brillo' => [
                    'type' => 'range',
                    'label' => 'Intensidad de Brillo',
                    'value' => 10,
                    'min' => 0,
                    'max' => 30,
                    'step' => 1,
                    'unit' => 'px',
                ],
            ],
            'avatar' => [
                '_label' => 'Foto de Perfil',
                'mostrar' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar Foto',
                    'value' => true,
                ],
                'tamano' => [
                    'type' => 'range',
                    'label' => 'Tamaño',
                    'value' => 130,
                    'min' => 80,
                    'max' => 200,
                    'step' => 5,
                    'unit' => 'px',
                ],
                'radio' => [
                    'type' => 'range',
                    'label' => 'Redondeo',
                    'value' => 15,
                    'min' => 0,
                    'max' => 50,
                    'step' => 1,
                    'unit' => '%',
                ],
            ],
            'botones' => [
                '_label' => 'Redes Sociales',
                'estiloBoton' => [
                    'type' => 'select',
                    'label' => 'Estilo de Botón',
                    'value' => 'solido',
                    'options' => ['solido', 'borde'],
                ],
                'radio' => [
                    'type' => 'range',
                    'label' => 'Redondeo de Botones',
                    'value' => 8,
                    'min' => 0,
                    'max' => 50,
                    'step' => 1,
                    'unit' => '%',
                ],
            ],
        ],

        // Plantilla Vibrante (Bento Box juvenil)
        'vibrant' => [
            'general' => [
                '_label' => 'Fondo Dinámico',
                'color1' => [
                    'type' => 'color',
                    'label' => 'Color Gradiente 1',
                    'value' => '#ff00cc',
                ],
                'color2' => [
                    'type' => 'color',
                    'label' => 'Color Gradiente 2',
                    'value' => '#333399',
                ],
                'tipografia' => [
                    'type' => 'select',
                    'label' => 'Tipografía',
                    'value' => 'Outfit',
                    'options' => ['Outfit', 'Poppins', 'Syne', 'Quicksand'],
                ],
            ],
            'tarjetas' => [
                '_label' => 'Tarjetas Internas (Bento)',
                'colorFondo' => [
                    'type' => 'color',
                    'label' => 'Fondo de Tarjetas',
                    'value' => '#ffffff',
                ],
                'colorTexto' => [
                    'type' => 'color',
                    'label' => 'Color de Texto',
                    'value' => '#1e1e2f',
                ],
                'radio' => [
                    'type' => 'range',
                    'label' => 'Redondeo de Tarjetas',
                    'value' => 24,
                    'min' => 0,
                    'max' => 40,
                    'step' => 1,
                    'unit' => 'px',
                ],
            ],
            'avatar' => [
                '_label' => 'Foto de Perfil',
                'mostrar' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar Foto',
                    'value' => true,
                ],
                'tamano' => [
                    'type' => 'range',
                    'label' => 'Tamaño',
                    'value' => 120,
                    'min' => 80,
                    'max' => 200,
                    'step' => 5,
                    'unit' => 'px',
                ],
                'animacion' => [
                    'type' => 'select',
                    'label' => 'Animación Continua',
                    'value' => 'flotar',
                    'options' => ['flotar', 'latir', 'ninguna'],
                ],
            ],
            'botones' => [
                '_label' => 'Acentos y Botones',
                'colorBoton' => [
                    'type' => 'color',
                    'label' => 'Color Primario (Botones)',
                    'value' => '#1e1e2f',
                ],
                'colorTextoBoton' => [
                    'type' => 'color',
                    'label' => 'Texto en Botones',
                    'value' => '#ffffff',
                ],
            ],
        ],
        // Plantilla Action (orientada a conversión)
        'action' => [
            'general' => [
                '_label' => 'Estilo General',
                'fuentePrincipal' => [
                    'type' => 'select',
                    'label' => 'Tipografía',
                    'value' => 'Montserrat',
                    'options' => ['Montserrat', 'Roboto', 'Open Sans', 'Lato'],
                ],
                'colorFondo' => [
                    'type' => 'color',
                    'label' => 'Color de Fondo',
                    'value' => '#ffffff',
                ],
            ],
            'hero' => [
                '_label' => 'Cabecera',
                'imagenFondo' => [
                    'type' => 'image',
                    'label' => 'Imagen de Fondo',
                    'value' => '',
                    'aspectRatio' => 16 / 9,
                ],
                'mostrarLogo' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar Logo / Nombre',
                    'value' => true,
                ],
                'mostrarEslogan' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar Lema',
                    'value' => true,
                ],
                'opacidadOverlay' => [
                    'type' => 'range',
                    'label' => 'Oscurecer Fondo',
                    'value' => 0.6,
                    'min' => 0,
                    'max' => 0.9,
                    'step' => 0.1,
                ],
                'eslogan' => [
                    'type' => 'text',
                    'label' => 'Texto del Lema',
                    'value' => 'Tu lema aquí',
                ],
            ],
            'cta' => [
                '_label' => 'Botón Principal',
                'texto' => [
                    'type' => 'text',
                    'label' => 'Texto del Botón',
                    'value' => 'Solicita información',
                ],
                'colorFondo' => [
                    'type' => 'color',
                    'label' => 'Color del Botón',
                    'value' => '#5cb85c',
                ],
                'colorTexto' => [
                    'type' => 'color',
                    'label' => 'Color del Texto',
                    'value' => '#ffffff',
                ],
                'tamanoTexto' => [
                    'type' => 'range',
                    'label' => 'Tamaño del Texto',
                    'value' => 1.2,
                    'min' => 0.8,
                    'max' => 2,
                    'step' => 0.1,
                    'unit' => 'rem',
                ],
                'radio' => [
                    'type' => 'range',
                    'label' => 'Redondeo de Esquinas',
                    'value' => 50,
                    'min' => 0,
                    'max' => 50,
                    'unit' => 'px',
                ],
                'tipoBorde' => [
                    'type' => 'select',
                    'label' => 'Borde',
                    'value' => 'none',
                    'options' => ['none', 'solid', 'dashed', 'dotted'],
                ],
                'colorBorde' => [
                    'type' => 'color',
                    'label' => 'Color del Borde',
                    'value' => '#ffffff',
                ],
                'grosorBorde' => [
                    'type' => 'range',
                    'label' => 'Grosor del Borde',
                    'value' => 2,
                    'min' => 1,
                    'max' => 6,
                    'unit' => 'px',
                ],
                'sombra' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar Sombra',
                    'value' => true,
                ],
            ],
            'contactoRapido' => [
                '_label' => 'Botones de Contacto',
                'radio' => [
                    'type' => 'range',
                    'label' => 'Redondeo de Esquinas',
                    'value' => 6,
                    'min' => 0,
                    'max' => 30,
                    'unit' => 'px',
                ],
                'tamanoTexto' => [
                    'type' => 'range',
                    'label' => 'Tamaño Texto e Iconos',
                    'value' => 0.9,
                    'min' => 0.7,
                    'max' => 1.4,
                    'step' => 0.1,
                    'unit' => 'rem',
                ],
                'colorLlamar' => [
                    'type' => 'color',
                    'label' => 'Color Llamar',
                    'value' => '#5b7cfa',
                ],
                'colorWhatsapp' => [
                    'type' => 'color',
                    'label' => 'Color WhatsApp',
                    'value' => '#10c469',
                ],
                'colorEmail' => [
                    'type' => 'color',
                    'label' => 'Color Email',
                    'value' => '#f05050',
                ],
            ],
            'video' => [
                '_label' => 'Video Promocional',
                'mostrar' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar Video',
                    'value' => true,
                ],
                'urlId' => [
                    'type' => 'text',
                    'label' => 'ID del video de YouTube',
                    'value' => 'dQw4w9WgXcQ',
                ],
                'radio' => [
                    'type' => 'range',
                    'label' => 'Redondeo de Esquinas',
                    'value' => 8,
                    'min' => 0,
                    'max' => 20,
                    'unit' => 'px',
                ],
            ],
            'actionButtons' => [
                '_label' => 'Botones de Acción',
                'colorFondo' => [
                    'type' => 'color',
                    'label' => 'Color de Fondo',
                    'value' => '#ffffff',
                ],
                'colorTexto' => [
                    'type' => 'color',
                    'label' => 'Color del Texto',
                    'value' => '#000000',
                ],
                'colorBorde' => [
                    'type' => 'color',
                    'label' => 'Color del Borde',
                    'value' => '#000000',
                ],
                'radio' => [
                    'type' => 'range',
                    'label' => 'Redondeo de Esquinas',
                    'value' => 8,
                    'min' => 0,
                    'max' => 20,
                    'unit' => 'px',
                ],
                'textoBoton1' => [
                    'type' => 'text',
                    'label' => 'Texto Botón 1',
                    'value' => 'Sobre nosotros',
                ],
                'iconoBoton1' => [
                    'type' => 'select',
                    'label' => 'Ícono Botón 1',
                    'value' => 'bi-people-fill',
                    'options' => ['bi-people-fill', 'bi-building', 'bi-info-circle-fill', 'bi-person-lines-fill', 'bi-briefcase-fill', 'bi-house-fill', 'bi-award-fill', 'bi-star-fill', 'bi-heart-fill', 'bi-shield-check'],
                ],
                'colorIconoBoton1' => [
                    'type' => 'color',
                    'label' => 'Color Ícono Botón 1',
                    'value' => '#1a237e',
                ],
                'textoBoton2' => [
                    'type' => 'text',
                    'label' => 'Texto Botón 2',
                    'value' => 'Nuestros servicios',
                ],
                'iconoBoton2' => [
                    'type' => 'select',
                    'label' => 'Ícono Botón 2',
                    'value' => 'bi-journal-text',
                    'options' => ['bi-journal-text', 'bi-gear-fill', 'bi-tools', 'bi-wrench-adjustable', 'bi-clipboard-check', 'bi-list-check', 'bi-grid-fill', 'bi-box-seam', 'bi-cart-fill', 'bi-tag-fill'],
                ],
                'colorIconoBoton2' => [
                    'type' => 'color',
                    'label' => 'Color Ícono Botón 2',
                    'value' => '#455a64',
                ],
                'textoBoton3' => [
                    'type' => 'text',
                    'label' => 'Texto Botón 3',
                    'value' => 'Abrir QR',
                ],
                'iconoBoton3' => [
                    'type' => 'select',
                    'label' => 'Ícono Botón 3',
                    'value' => 'bi-qr-code',
                    'options' => ['bi-qr-code', 'bi-share-fill', 'bi-link-45deg', 'bi-send-fill', 'bi-download', 'bi-upload', 'bi-camera-fill', 'bi-phone-fill', 'bi-envelope-fill', 'bi-globe'],
                ],
                'colorIconoBoton3' => [
                    'type' => 'color',
                    'label' => 'Color Ícono Botón 3',
                    'value' => '#000000',
                ],
            ],
            'social' => [
                '_label' => 'Botones Sociales',
                'colorIcono' => [
                    'type' => 'color',
                    'label' => 'Color de Íconos',
                    'value' => '#ffffff',
                ],
                'tamano' => [
                    'type' => 'range',
                    'label' => 'Tamaño de Botones',
                    'value' => 40,
                    'min' => 30,
                    'max' => 56,
                    'unit' => 'px',
                ],
                'tamanoIcono' => [
                    'type' => 'range',
                    'label' => 'Tamaño de Íconos',
                    'value' => 1.2,
                    'min' => 0.8,
                    'max' => 2,
                    'step' => 0.1,
                    'unit' => 'rem',
                ],
                'radio' => [
                    'type' => 'range',
                    'label' => 'Redondeo de Esquinas',
                    'value' => 50,
                    'min' => 0,
                    'max' => 50,
                    'unit' => '%',
                ],
                'sombra' => [
                    'type' => 'toggle',
                    'label' => 'Sombra en Botones',
                    'value' => true,
                ],
                'mostrarFacebook' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar Facebook',
                    'value' => true,
                ],
                'fondoFacebook' => [
                    'type' => 'color',
                    'label' => 'Fondo Facebook',
                    'value' => '#3b5998',
                ],
                'mostrarInstagram' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar Instagram',
                    'value' => true,
                ],
                'fondoInstagram' => [
                    'type' => 'color',
                    'label' => 'Fondo Instagram',
                    'value' => '#e1306c',
                ],
                'mostrarYoutube' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar YouTube',
                    'value' => true,
                ],
                'fondoYoutube' => [
                    'type' => 'color',
                    'label' => 'Fondo YouTube',
                    'value' => '#ff0000',
                ],
                'mostrarTiktok' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar TikTok',
                    'value' => true,
                ],
                'fondoTiktok' => [
                    'type' => 'color',
                    'label' => 'Fondo TikTok',
                    'value' => '#000000',
                ],
                'mostrarLinkedin' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar LinkedIn',
                    'value' => true,
                ],
                'fondoLinkedin' => [
                    'type' => 'color',
                    'label' => 'Fondo LinkedIn',
                    'value' => '#0077b5',
                ],
                'mostrarUbicacion' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar Ubicación',
                    'value' => true,
                ],
                'fondoUbicacion' => [
                    'type' => 'color',
                    'label' => 'Fondo Ubicación',
                    'value' => '#0000ed',
                ],
                'mostrarWeb' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar Web',
                    'value' => true,
                ],
                'fondoWeb' => [
                    'type' => 'color',
                    'label' => 'Fondo Web',
                    'value' => '#0000ed',
                ],
                'mostrarVcard' => [
                    'type' => 'toggle',
                    'label' => 'Mostrar vCard',
                    'value' => true,
                ],
                'fondoVcard' => [
                    'type' => 'color',
                    'label' => 'Fondo vCard',
                    'value' => '#87ceeb',
                ],
            ],
            'footer' => [
                '_label' => 'Pie de Página',
                'texto' => [
                    'type' => 'text',
                    'label' => 'Texto',
                    'value' => 'by NexosCard.com',
                ],
                'colorFondo' => [
                    'type' => 'color',
                    'label' => 'Color de Fondo',
                    'value' => '#ffffff',
                ],
                'colorTexto' => [
                    'type' => 'color',
                    'label' => 'Color del Texto',
                    'value' => '#666666',
                ],
                'tamanoTexto' => [
                    'type' => 'range',
                    'label' => 'Tamaño de Texto',
                    'value' => 0.9,
                    'min' => 0.7,
                    'max' => 1.3,
                    'step' => 0.1,
                    'unit' => 'rem',
                ],
            ],
        ],
    ],
];
