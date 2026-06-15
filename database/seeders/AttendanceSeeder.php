<?php

namespace Database\Seeders;

use App\Enums\AttendanceType;
use App\Models\EmployeeAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $teamEmails = [
            'sarah.chen@botjourney.ai',
            'james.wilson@botjourney.ai',
            'emma.rodriguez@botjourney.ai',
            'liam.thompson@botjourney.ai',
            'olivia.davis@botjourney.ai',
            'noah.martinez@botjourney.ai',
            'ava.johnson@botjourney.ai',
            'william.brown@botjourney.ai',
            'isabella.garcia@botjourney.ai',
            'lucas.anderson@botjourney.ai',
            'mia.taylor@botjourney.ai',
        ];

        $users = User::whereIn('email', $teamEmails)->get();
        $startDate = now()->subWeeks(2)->startOfWeek(Carbon::MONDAY);
        $endDate   = now()->subDay()->endOfDay();

        $count = 0;
        foreach ($users as $user) {
            $day = $startDate->copy();

            while ($day->lte($endDate)) {
                if ($day->isWeekend()) {
                    $day->addDay();
                    continue;
                }

                $checkInHour   = rand(8, 9);
                $checkInMinute = rand(0, 59);

                EmployeeAttendance::create([
                    'user_id'   => $user->id,
                    'type'      => AttendanceType::CheckIn->value,
                    'time_date' => $day->copy()->setTime($checkInHour, $checkInMinute),
                    'notes'     => null,
                ]);

                EmployeeAttendance::create([
                    'user_id'   => $user->id,
                    'type'      => AttendanceType::BreakStart->value,
                    'time_date' => $day->copy()->setTime(12, rand(0, 30)),
                    'notes'     => null,
                ]);

                EmployeeAttendance::create([
                    'user_id'   => $user->id,
                    'type'      => AttendanceType::BreakEnd->value,
                    'time_date' => $day->copy()->setTime(rand(12, 13), rand(30, 59)),
                    'notes'     => null,
                ]);

                EmployeeAttendance::create([
                    'user_id'   => $user->id,
                    'type'      => AttendanceType::CheckOut->value,
                    'time_date' => $day->copy()->setTime(rand(17, 18), rand(0, 59)),
                    'notes'     => null,
                ]);

                $count += 4;
                $day->addDay();
            }
        }

        $this->command->info("Attendance records seeded: {$count}");
    }
}
