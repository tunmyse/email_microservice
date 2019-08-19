<?php

namespace App\Http\Controllers;

use App\Email;
use App\Http\Resources\EmailResource;
use App\Http\Resources\RecipientResource;
use App\Services\EmailService;
use App\Services\MailerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        return EmailResource::collection(Email::orderBy('id', 'desc')->get());
    }
    
    /**
     * Get the recipients of an email message.
     *
     * @return Response
     */
    public function emailRecipients($emailId)
    {
        $email = Email::find($emailId);
        return RecipientResource::collection($email->recipients);
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
    
    /**
     * Webhook to receive email events from mailer providers.
     *
     * @param  Request  $request
     * @return Response
     */
    public function webhook(Request $request, MailerService $mailerService, EmailService $emailService, string $provider)
    {
        $mailerProvider = $mailerService->getMailerProvider($provider);
        $events = $mailerProvider->processMailEvents($request->all());
        
        $response = $emailService->updateStatusFromEvent($events);
        if ($response['status'] == 'success') {
            return Response::json([], 200);
        }
    }
}
