<?php

namespace App\Http\Controllers;

use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class EmailController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function send(Request $request, EmailService $emailService)
    {
        $statusCode = 500;

        $requestParams = $request->only(['subject', 'body', 'recipients', 'format']);

        $response = $emailService->enqueueEmail($requestParams);

        if ($response['status'] == 'validation_error') {
            $statusCode = 422;
        } elseif ($response['status'] == 'success') {
            $statusCode = 202;
        }

        return Response::json($response, $statusCode);
    }
}
