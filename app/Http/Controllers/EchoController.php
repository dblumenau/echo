<?php

namespace App\Http\Controllers;

use App\Models\EchoRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EchoController extends Controller
{
    public function store(Request $request)
    {
        EchoRequest::create([
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'query_params' => $request->query(),
            'body' => $request->getContent(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'response_status' => 200,
        ]);

        return response()->json([
            'message' => 'Request stored successfully',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }

    public function index()
    {
        $requests = EchoRequest::latest()->paginate(20)->through(function ($request) {
            return $request;
        })->withQueryString();
        
        return Inertia::render('Dashboard', [
            'requests' => $requests,
        ]);
    }

    public function threeDSMethodCallback(Request $request)
    {
        // Store the request first
        EchoRequest::create([
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'query_params' => $request->query(),
            'body' => $request->getContent(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'response_status' => 200,
        ]);

        // Return HTML that posts a message to the parent window
        return response('<html><body><script>window.parent.postMessage("3DSMethodComplete", "*");</script></body></html>')
            ->header('Content-Type', 'text/html');
    }
}
