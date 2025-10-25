<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        // clear existing demo rows (safe delete to avoid FK issues)
        DB::table('invoices')->delete();
        DB::table('payrolls')->delete();
        DB::table('attendance')->delete();
        DB::table('employees')->delete();
        DB::table('clients')->delete();
        DB::table('agencies')->delete();

        DB::transaction(function () use ($faker) {
            // Agencies
            $agencyIds = [];
            for ($i = 0; $i < 5; $i++) {
                $agencyIds[] = DB::table('agencies')->insertGetId([
                    'name' => $faker->company,
                    'details' => $faker->address . "\nPhone: " . $faker->phoneNumber,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            // Clients
            $clientIds = [];
            for ($i = 0; $i < 10; $i++) {
                $clientIds[] = DB::table('clients')->insertGetId([
                    'tenant_id' => null,
                    'name' => $faker->name,
                    'email' => $faker->unique()->safeEmail,
                    'phone' => $faker->phoneNumber,
                    'address' => $faker->address,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            // Employees
            $employeeIds = [];
            for ($i = 0; $i < 10; $i++) {
                $employeeIds[] = DB::table('employees')->insertGetId([
                    'tenant_id' => null,
                    'agency_id' => $faker->randomElement($agencyIds),
                    'first_name' => $faker->firstName,
                    'last_name' => $faker->lastName,
                    'email' => $faker->unique()->safeEmail,
                    'phone' => $faker->phoneNumber,
                    'position' => $faker->jobTitle,
                    'hired_at' => $faker->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            // Attendance (random days for employees)
            for ($i = 0; $i < 30; $i++) {
                $emp = $faker->randomElement($employeeIds);
                $date = $faker->dateTimeBetween('-60 days', 'now')->format('Y-m-d');
                DB::table('attendance')->insert([
                    'tenant_id' => null,
                    'employee_id' => $emp,
                    'date' => $date,
                    'status' => $faker->randomElement(['present', 'absent', 'leave']),
                    'check_in' => $faker->optional(0.9)->dateTimeBetween($date . ' 07:00', $date . ' 10:00'),
                    'check_out' => $faker->optional(0.8)->dateTimeBetween($date . ' 15:00', $date . ' 19:00'),
                    'notes' => $faker->optional()->sentence,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            // Payrolls
            for ($i = 0; $i < 6; $i++) {
                $emp = $faker->randomElement($employeeIds);
                $start = Carbon::now()->subMonths($i + 1)->startOfMonth();
                $end = (clone $start)->endOfMonth();
                $gross = $faker->randomFloat(2, 1000, 8000);
                $tax = round($gross * 0.12, 2);
                $net = round($gross - $tax, 2);

                DB::table('payrolls')->insert([
                    'tenant_id' => null,
                    'employee_id' => $emp,
                    'period_start' => $start->format('Y-m-d'),
                    'period_end' => $end->format('Y-m-d'),
                    'gross' => $gross,
                    'tax' => $tax,
                    'net' => $net,
                    'status' => $faker->randomElement(['paid', 'pending']),
                    'paid_at' => $faker->optional(0.6)->dateTimeBetween($start, $end),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            // Invoices
            for ($i = 0; $i < 8; $i++) {
                $client = $faker->randomElement($clientIds);
                $date = $faker->dateTimeBetween('-120 days', 'now')->format('Y-m-d');
                $total = $faker->randomFloat(2, 50, 5000);

                DB::table('invoices')->insert([
                    'tenant_id' => null,
                    'client_id' => $client,
                    'invoice_number' => strtoupper($faker->bothify('INV-####-??')),
                    'date' => $date,
                    'due_date' => Carbon::parse($date)->addDays(30)->format('Y-m-d'),
                    'total' => $total,
                    'status' => $faker->randomElement(['draft', 'sent', 'paid']),
                    'notes' => $faker->optional()->sentence,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        });
    }
}
