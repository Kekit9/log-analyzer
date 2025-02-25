<?php

namespace Database\Factories;

use App\Models\LogEntry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class LogEntryFactory extends Factory
{
    protected $model = LogEntry::class;

    public function definition()
    {
        return [
            'client_ip' => $this->faker->ipv4,
            'referer_ip' => $this->faker->ipv4,
            'referer_ip_host' => 'referer_ip',
            'http_info' => $this->faker->sentence,
            'error_code' => $this->faker->randomElement([200, 404, 500]),
            'response_size' => $this->faker->numberBetween(100, 1000),
            'user_agent' => $this->faker->userAgent,
            'date' => Carbon::now()->subDays($this->faker->numberBetween(1, 30)),
        ];
    }
}
