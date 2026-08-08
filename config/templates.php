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
                    'value' => 'Poppins',
                    'options' => ['Poppins', 'Montserrat', 'Quicksand', 'Roboto', 'Open Sans', 'Lato'],
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
                'colorFondo' => [
                    'type' => 'color',
                    'label' => 'Color Fondo Cabecera',
                    'value' => 'transparent',
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
                    'value' => '#30ab0a',
                ],
                'colorSeccion2' => [
                    'type' => 'color',
                    'label' => 'Color Sección 2 (Contacto)',
                    'value' => '#279208',
                ],
                'colorSeccion3' => [
                    'type' => 'color',
                    'label' => 'Color Sección 3 (Servicios)',
                    'value' => '#1f7506',
                ],
                'colorSeccion4' => [
                    'type' => 'color',
                    'label' => 'Color Sección 4 (Productos)',
                    'value' => '#ba2831',
                ],
                'colorSeccion5' => [
                    'type' => 'color',
                    'label' => 'Color Sección 5 (Galería)',
                    'value' => '#9e2229',
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
                    'value' => '#30ab0a',
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
                    'value' => '#ba2831',
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
                    'value' => '#30ab0a',
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
                    'value' => '#30ab0a',
                ],
                'color2' => [
                    'type' => 'color',
                    'label' => 'Color Gradiente 2',
                    'value' => '#ba2831',
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
                    'value' => '#2d2d2d',
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
                    'value' => '#2d2d2d',
                ],
                'colorTextoBoton' => [
                    'type' => 'color',
                    'label' => 'Texto en Botones',
                    'value' => '#ffffff',
                ],
            ],
        ],
    ],
];
