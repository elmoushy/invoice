<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Repositories\Cashier\CashierRepository;
use App\Models\Request;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            // Logic to check for available barbers and assign them to requests

            $cashierRepo = new CashierRepository();

            // Get all requests with status 'Pending'
            $waitingRequests = Request::with('user')
                ->whereIn('status', ['Waiting'])
                ->get();

            foreach ($waitingRequests as $request) {
                $branch_id = $request->user->branch_id;
                $assignedBarber = $cashierRepo->checkAndAssignBarber($branch_id);

                if ($assignedBarber) {
                    $cashierRepo->assignBarberToRequest($request->id, $request->user_id);
                }
                // If no barber is available, the request remains in 'Waiting'status
            }
        })->everySecond();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
