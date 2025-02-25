<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LogEntry;
use Carbon\Carbon;

class LogController extends Controller
{
    public function filter(Request $request)
        //необходима валидация для усиления защиты, к примеру "недостаточные бизнес-требования" и т.д
        //во избежание инъекций и прочего вмешательства в бд
    {
        $query = LogEntry::query();

        if ($request->has('client_ip')) {
            $query->where('client_ip', $request->client_ip);
        }

        if ($request->has('http_info')) {
            $query->where('http_info', 'like', '%' . $request->http_info . '%');
        }

        if ($request->has('error_code')) {
            $query->where('error_code', $request->error_code);
        }

        //тут был большой кусок кода if/elseif который был сокращен при помощи match
        if ($request->has('response_size')) {
            $responseSize = $request->response_size;

            $operator = match (true) {
                str_contains($responseSize, '>=') => '>=',
                str_contains($responseSize, '<=') => '<=',
                str_contains($responseSize, '!=') => '!=',
                str_contains($responseSize, '>')  => '>',
                str_contains($responseSize, '<')  => '<',
                default => '=',
            };

            $value = (int) explode($operator, $responseSize)[1];
            $query->where('response_size', $operator, $value);
        }

        if ($request->has('user_agent')) {
            $query->where('user_agent', 'like', '%' . $request->user_agent . '%');
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $count = $query->count();
        $data = $query->get();

        return response()->json([
            'count' => $count,
            'data' => $data,
        ]);
    }
}
