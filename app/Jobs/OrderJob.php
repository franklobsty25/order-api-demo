<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderJob implements ShouldQueue
{
    use Queueable;

    public $tries = 5;

    public $queuetimeout = 120;

    public $failOnTimeout = true;

    public $maxExceptions = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(public Order $order)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
       
    }
}
