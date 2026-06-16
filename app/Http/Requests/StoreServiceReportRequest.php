<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_uuid'        => ['nullable', 'uuid'],
            'report_type'        => ['required', Rule::in(['RSTP', 'RSTC', 'RSTE'])],
            'equipment_id'       => ['required', 'exists:equipment,id'],
            'service_date'       => ['required', 'date'],
            'customer_order_ref' => ['nullable', 'string', 'max:100'],
            'time_in'            => ['nullable', 'date_format:H:i'],
            'time_out'           => ['nullable', 'date_format:H:i'],
            'secondary_technician_id' => ['nullable', 'exists:users,id'],

            // Condiciones iniciales
            'initial_conditions'                  => ['nullable', 'array'],
            'initial_conditions.*.condition_key'   => ['required_with:initial_conditions', 'string', 'max:50'],
            'initial_conditions.*.value'           => ['required_with:initial_conditions', Rule::in(['si', 'no', 'na'])],
            'initial_conditions.*.observation'     => ['nullable', 'string'],

            // RSTP: Actividades
            'rstp_activities'                => ['nullable', 'array'],
            'rstp_activities.*.group_key'    => ['required_with:rstp_activities', Rule::in(['cuarto_maquinas', 'cabina', 'pozo_foso'])],
            'rstp_activities.*.activity_key' => ['required_with:rstp_activities', 'string', 'max:50'],
            'rstp_activities.*.is_ok'        => ['nullable', 'boolean'],
            'rstp_activities.*.observation'  => ['nullable', 'string'],

            // RSTP: Mes del mantenimiento
            'rstp_month'       => ['nullable', 'array'],
            'rstp_month.year'  => ['required_with:rstp_month', 'integer', 'min:2020', 'max:2099'],
            'rstp_month.month' => ['required_with:rstp_month', 'integer', 'min:1', 'max:12'],

            // RSTC: Detalles de análisis
            'rstc_details'                        => ['nullable', 'array'],
            'rstc_details.call_time'              => ['nullable', 'string'],
            'rstc_details.entry_time'             => ['nullable', 'string'],
            'rstc_details.exit_time'              => ['nullable', 'string'],
            'rstc_details.response_time_hh'       => ['nullable', 'integer', 'min:0'],
            'rstc_details.response_time_mm'       => ['nullable', 'integer', 'min:0', 'max:59'],
            'rstc_details.fault_location'         => ['nullable', Rule::in(['sistema_puertas', 'control_maniobras', 'maquinaria', 'recorrido', 'otra'])],
            'rstc_details.fault_location_other'   => ['nullable', 'string'],
            'rstc_details.fault_cause'            => ['nullable', Rule::in(['energia_externa', 'inundacion_humedad', 'tercero', 'tecnica_equipo', 'otra'])],
            'rstc_details.fault_cause_other'      => ['nullable', 'string'],
            'rstc_details.fault_solution_area'    => ['nullable', Rule::in(['control_maniobras', 'perifericos', 'puertas_piso', 'operador_puertas_cabina', 'activacion_interruptores', 'otra'])],
            'rstc_details.fault_solution_other'   => ['nullable', 'string'],
            'rstc_details.analysis_notes'         => ['nullable', 'string'],
            'rstc_details.cause_notes'            => ['nullable', 'string'],
            'rstc_details.solution_notes'         => ['nullable', 'string'],

            // Códigos de falla (RSTC y RSTE)
            'fault_codes'                => ['nullable', 'array'],
            'fault_codes.*.code'         => ['required_with:fault_codes', 'string', 'max:10'],
            'fault_codes.*.description'  => ['nullable', 'string'],
            'fault_codes.*.severity'     => ['nullable', Rule::in(['baja', 'media', 'alta'])],

            // RSTE: Trabajos
            'rste_works'                  => ['nullable', 'array'],
            'rste_works.*.group_key'      => ['required_with:rste_works', Rule::in(['cuarto_maquinas', 'cabina', 'pozo_foso'])],
            'rste_works.*.work_key'       => ['required_with:rste_works', 'string', 'max:50'],
            'rste_works.*.is_ok'          => ['nullable', 'boolean'],
            'rste_works.*.observation'    => ['nullable', 'string'],

            // Conclusión
            'equipment_functional'  => ['nullable', 'boolean'],
            'generates_quotation'   => ['nullable', 'boolean'],
            'requires_parts_change' => ['nullable', 'boolean'],
            'conclusion_notes'      => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'report_type.required'  => 'El tipo de reporte es obligatorio.',
            'equipment_id.required' => 'El equipo es obligatorio.',
            'equipment_id.exists'   => 'El equipo seleccionado no existe.',
            'service_date.required' => 'La fecha del servicio es obligatoria.',
        ];
    }
}
