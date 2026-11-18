<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gestion de proveedores (fase 1): directorio (proveedor, sedes, asesores) y la
 * base del catalogo (items propios + la oferta de cada proveedor con su precio
 * fechado e historial).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('business_name');
            $table->string('trade_name')->nullable();
            // Sin indice unico: la unicidad se valida entre los no borrados, para
            // que eliminar un proveedor no impida volver a registrarlo.
            $table->string('nit', 30)->nullable()->index();
            $table->string('supplier_type', 30)->default('distribuidor');
            $table->json('categories')->nullable();
            $table->json('brands')->nullable();
            $table->string('website')->nullable();
            $table->string('payment_terms')->nullable();
            $table->unsignedSmallInteger('credit_days')->nullable();
            $table->char('default_currency', 3)->default('COP');
            $table->unsignedSmallInteger('default_lead_time_days')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            // Datos de demostracion: se cargan con SupplierExampleSeeder y el
            // equipo los borra a medida que registra los reales.
            $table->boolean('is_example')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('supplier_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_main')->default(false);
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('business_hours')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('supplier_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('position')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('extension', 10)->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // "supply_items" y no "products": products/services ya son de las
        // tarjetas digitales y catalogs son las listas de opciones.
        Schema::create('supply_items', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->nullable()->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('item_type', 20)->default('repuesto');
            $table->string('category', 80)->nullable();
            $table->string('manufacturer_ref', 100)->nullable();
            $table->json('compatible_brands')->nullable();
            $table->string('compatible_models')->nullable();
            $table->string('unit', 20)->default('und');
            $table->decimal('default_markup_percent', 5, 2)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(19);
            $table->decimal('base_price', 14, 2)->nullable();
            $table->string('image_path')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('is_example')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('supplier_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supply_item_id')->constrained()->cascadeOnDelete();
            $table->string('supplier_ref', 100)->nullable();
            $table->decimal('price', 14, 2); // sin IVA
            $table->char('currency', 3)->default('COP');
            $table->decimal('min_quantity', 10, 2)->default(1);
            $table->unsignedSmallInteger('lead_time_days')->nullable();
            $table->string('price_date', 10); // "Y-m-d" pelado, ver el accessor del modelo
            $table->string('valid_until', 10)->nullable();
            $table->string('source', 20)->default('cotizacion');
            $table->boolean('is_preferred')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['supplier_id', 'supply_item_id']);
        });

        Schema::create('supplier_price_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 14, 2);
            $table->char('currency', 3);
            $table->string('price_date', 10);
            $table->string('source', 20)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_price_logs');
        Schema::dropIfExists('supplier_items');
        Schema::dropIfExists('supply_items');
        Schema::dropIfExists('supplier_contacts');
        Schema::dropIfExists('supplier_branches');
        Schema::dropIfExists('suppliers');
    }
};
