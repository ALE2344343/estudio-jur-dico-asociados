<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Cliente;
use App\Models\Caso;
use App\Models\Ley;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── USUARIOS (firstOrCreate evita duplicados) ──
        $admin = Usuario::firstOrCreate(
            ['username' => 'admin'],
            [
                'nombre'      => 'Administrador',
                'apellido'    => 'Sistema',
                'email'       => 'admin@estudiojuridico.bo',
                'password'    => Hash::make('admin123'),
                'rol'         => 'administrador',
                'telefono'    => '72000001',
                'especialidad'=> 'Administración',
                'activo'      => true,
            ]
        );

        $abogado1 = Usuario::firstOrCreate(
            ['username' => 'cmamani'],
            [
                'nombre'      => 'Carlos Alberto',
                'apellido'    => 'Mamani Quispe',
                'email'       => 'cmamani@estudiojuridico.bo',
                'password'    => Hash::make('abogado123'),
                'rol'         => 'abogado',
                'telefono'    => '72000002',
                'especialidad'=> 'Derecho Laboral',
                'matricula'   => 'MAT-001-2020',
                'activo'      => true,
            ]
        );

        $abogado2 = Usuario::firstOrCreate(
            ['username' => 'mvargas'],
            [
                'nombre'      => 'María Fernanda',
                'apellido'    => 'Vargas Chávez',
                'email'       => 'mvargas@estudiojuridico.bo',
                'password'    => Hash::make('abogado123'),
                'rol'         => 'abogado',
                'telefono'    => '72000003',
                'especialidad'=> 'Derecho Civil y Familia',
                'matricula'   => 'MAT-002-2019',
                'activo'      => true,
            ]
        );

        // ── CLIENTES (firstOrCreate evita duplicados) ──
        $cliente1 = Cliente::firstOrCreate(
            ['ci' => '4567891'],
            [
                'codigo'       => 'CLI-0001',
                'nombre'       => 'Juan Pedro',
                'apellido'     => 'Flores Mendoza',
                'ci_expedido'  => 'LP',
                'telefono'     => '71234567',
                'email'        => 'jpflores@gmail.com',
                'ciudad'       => 'La Paz',
                'departamento' => 'La Paz',
                'ocupacion'    => 'Empleado',
                'empresa'      => 'Industrias XYZ S.A.',
                'estado'       => 'activo',
                'usuario_id'   => $abogado1->id,
            ]
        );

        $cliente2 = Cliente::firstOrCreate(
            ['ci' => '7891234'],
            [
                'codigo'       => 'CLI-0002',
                'nombre'       => 'Ana María',
                'apellido'     => 'Condori Tito',
                'ci_expedido'  => 'CB',
                'telefono'     => '79876543',
                'email'        => 'acondori@hotmail.com',
                'ciudad'       => 'Cochabamba',
                'departamento' => 'Cochabamba',
                'estado'       => 'activo',
                'usuario_id'   => $abogado2->id,
            ]
        );

        // ── CASOS (firstOrCreate evita duplicados) ──
        Caso::firstOrCreate(
            ['numero_caso' => 'CASO-2025-0001'],
            [
                'titulo'       => 'Demanda por Despido Injustificado — Flores vs. Industrias XYZ',
                'descripcion'  => 'El trabajador fue despedido sin causa justificada después de 5 años de servicio.',
                'tipo'         => 'Laboral',
                'estado'       => 'activo',
                'prioridad'    => 'alta',
                'fecha_inicio' => '2025-01-15',
                'fecha_vencimiento' => now()->addDays(30)->format('Y-m-d'),
                'juzgado'      => 'Juzgado de Partido en lo Laboral N° 2 de La Paz',
                'contraparte'  => 'Industrias XYZ S.A.',
                'honorarios'   => 3500.00,
                'cliente_id'   => $cliente1->id,
                'abogado_id'   => $abogado1->id,
            ]
        );

        Caso::firstOrCreate(
            ['numero_caso' => 'CASO-2025-0002'],
            [
                'titulo'       => 'Divorcio por Mutuo Acuerdo — Condori / Vásquez',
                'tipo'         => 'Familia',
                'estado'       => 'en_proceso',
                'prioridad'    => 'media',
                'fecha_inicio' => '2025-02-10',
                'honorarios'   => 2000.00,
                'cliente_id'   => $cliente2->id,
                'abogado_id'   => $abogado2->id,
            ]
        );

        // LEYES
        $leyes = [
            [
                'numero'            => 'Ley General del Trabajo (LGT)',
                'titulo'            => 'Ley General del Trabajo — 8 de diciembre de 1942',
                'descripcion'       => 'La ley madre del derecho laboral boliviano. Establece las normas fundamentales sobre el contrato de trabajo, jornada laboral, salarios, licencias, desahucio e indemnización por tiempo de servicios. Ha sido modificada y complementada por numerosos decretos supremos a lo largo de los años.',
                'fecha_promulgacion'=> '1942-12-08',
                'categoria'         => 'Laboral',
                'vigente'           => true,
                'notas'             => 'Artículos clave: Art. 12 (Desahucio), Art. 13 (Indemnización), Art. 44 (Vacaciones), Art. 57 (Prima anual)',
            ],
            [
                'numero'            => 'D.S. 28699',
                'titulo'            => 'Decreto Supremo 28699 — Garantías Laborales (1 mayo 2006)',
                'descripcion'       => 'Establece garantías laborales para los trabajadores bolivianos. Prohíbe el despido injustificado y establece el proceso de reinserción laboral. Refuerza el inamovilidad laboral para mujeres embarazadas y personas con discapacidad.',
                'fecha_promulgacion'=> '2006-05-01',
                'categoria'         => 'Laboral',
                'vigente'           => true,
                'notas'             => 'Inamovilidad laboral: mujer embarazada (1 año después del parto), padre (hasta que el hijo cumpla 1 año).',
            ],
            [
                'numero'            => 'D.S. 23932',
                'titulo'            => 'Decreto Supremo 23932 — Bono de Antigüedad (21 diciembre 1994)',
                'descripcion'       => 'Establece la escala del bono de antigüedad basada en años de servicio y el Salario Mínimo Nacional. Se paga mensualmente junto al salario. El porcentaje varía desde 5% (2 años) hasta 100% (25+ años) sobre el Salario Mínimo Nacional vigente.',
                'fecha_promulgacion'=> '1994-12-21',
                'categoria'         => 'Laboral',
                'vigente'           => true,
                'notas'             => 'SMN 2024-2025: Bs. 2,362. Escala: 2 años=5%, 4=11%, 6=18%, 8=26%, 10=34%, 12=42%, 14=50%, 16=58%, 18=66%, 20=74%, 22=82%, 24=90%, 25+=100%.',
            ],
            [
                'numero'            => 'D.S. 1802',
                'titulo'            => 'Decreto Supremo 1802 — Aguinaldo de Navidad (2013)',
                'descripcion'       => 'Regula el pago del aguinaldo de navidad (equivalente a un salario mensual) que debe pagarse hasta el 20 de diciembre de cada año. También establece el "segundo aguinaldo" (Esfuerzo por Bolivia) cuando el crecimiento económico supera el 4.5% del PIB.',
                'fecha_promulgacion'=> '2013-11-20',
                'categoria'         => 'Laboral',
                'vigente'           => true,
            ],
            [
                'numero'            => 'Ley 321',
                'titulo'            => 'Ley 321 — Trabajadores del Hogar (18 diciembre 2012)',
                'descripcion'       => 'Incorpora a los trabajadores asalariados del hogar al Seguro de Salud y al Seguro Social de Largo Plazo administrado por el sistema de Pensiones. Establece los derechos laborales específicos para este sector.',
                'fecha_promulgacion'=> '2012-12-18',
                'categoria'         => 'Seguridad Social',
                'vigente'           => true,
            ],
            [
                'numero'            => 'Ley 548 (CDN)',
                'titulo'            => 'Código Niña, Niño y Adolescente — 17 julio 2014',
                'descripcion'       => 'Regula la protección integral de niñas, niños y adolescentes. En materia laboral, establece las condiciones para el trabajo de adolescentes, prohibición de trabajo infantil y protecciones especiales.',
                'fecha_promulgacion'=> '2014-07-17',
                'categoria'         => 'Laboral',
                'vigente'           => true,
                'notas'             => 'Edad mínima de trabajo: 14 años con autorización. Art. 126-137: trabajo de adolescentes.',
            ],
            [
                'numero'            => 'Ley 004',
                'titulo'            => 'Ley Marcelo Quiroga Santa Cruz — Lucha contra la Corrupción (31 marzo 2010)',
                'descripcion'       => 'Establece mecanismos y procedimientos para prevenir, investigar, procesar y sancionar actos de corrupción cometidos por servidores públicos y ex servidores públicos en el ejercicio de sus funciones.',
                'fecha_promulgacion'=> '2010-03-31',
                'categoria'         => 'Administrativo',
                'vigente'           => true,
            ],
        ];

        foreach ($leyes as $ley) {
            Ley::firstOrCreate(
                ['numero' => $ley['numero']],
                collect($ley)->except('numero')->toArray()
            );
        }

        $this->command->info('Base de datos inicializada con datos de prueba.');
        $this->command->info('');
        $this->command->info('CREDENCIALES DE ACCESO:');
        $this->command->info('   ADMINISTRADOR → usuario: admin | contraseña: admin123');
        $this->command->info('   ABOGADO 1     → usuario: cmamani | contraseña: abogado123');
        $this->command->info('   ABOGADO 2     → usuario: mvargas | contraseña: abogado123');
    }
}
