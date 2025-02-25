<?php

namespace Tests\Feature;

use App\Http\Controllers\LogController;
use App\Models\LogEntry;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\Request;

class LogEntryTest extends TestCase
{
    use RefreshDatabase;

    public function testFilterByClientIp()
    {
        LogEntry::factory()->create(['client_ip' => '192.168.1.1']);
        LogEntry::factory()->create(['client_ip' => '192.168.1.2']);

        $request = new Request(['client_ip' => '192.168.1.1']);

        $response = (new LogController())->filter($request);

        $this->assertEquals(1, $response->getData()->count);
        $this->assertEquals('192.168.1.1', $response->getData()->data[0]->client_ip);
    }

    public function testFilterByResponseSizeGreaterThan()
    {
        LogEntry::factory()->create(['response_size' => 100]);
        LogEntry::factory()->create(['response_size' => 200]);

        $request = new Request(['response_size' => '>=150']);

        $response = (new LogController())->filter($request);

        $this->assertEquals(1, $response->getData()->count);
        $this->assertEquals(200, $response->getData()->data[0]->response_size);
    }

    public function testFilterByDateRange()
    {
        LogEntry::factory()->create(['date' => Carbon::parse('2023-10-01')]);
        LogEntry::factory()->create(['date' => Carbon::parse('2023-10-05')]);
        LogEntry::factory()->create(['date' => Carbon::parse('2023-10-10')]);

        $request = new Request([
            'start_date' => '2023-10-02',
            'end_date' => '2023-10-09',
        ]);

        $response = (new LogController())->filter($request);

        $this->assertEquals(1, $response->getData()->count);

        $date = Carbon::parse($response->getData()->data[0]->date);
        $this->assertEquals('2023-10-05', $date->toDateString());
    }

    public function testFilterByUserAgent()
    {
        LogEntry::factory()->create(['user_agent' => 'Mozilla/5.0']);
        LogEntry::factory()->create(['user_agent' => 'curl/7.64.1']);

        $request = new Request(['user_agent' => 'Mozilla']);

        $response = (new LogController())->filter($request);

        $this->assertEquals(1, $response->getData()->count);
        $this->assertStringContainsString('Mozilla', $response->getData()->data[0]->user_agent);
    }
}
