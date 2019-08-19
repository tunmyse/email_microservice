<?php

namespace App\Services;

use App\Contracts\EmailEvent;
use App\Email;
use App\Jobs\EmailJob;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Iterator;

class EmailService
{
    private $validation = [
        'subject' => 'required|string',
        'body' => 'required|string',
        'recipients' => 'required|recipients',
        'format' => 'required|string|in:plain,html,markdown',
    ];

    private function validateData($emailParams, $validations)
    {
        $validator = Validator::make($emailParams, $validations);
        if ($validator->fails()) {
            return $validator->errors()->all();
        }

        return [];
    }

    public function enqueueEmail($emailParams)
    {
        $response = [
            'status' => 'failed',
            'message' => 'Unable to process your email!'
        ];
        
        $status = $this->validateData($emailParams, $this->validation);
        if (!empty($status)) {
            return [
                'status' => 'validation_error',
                'message' => $status
            ];
        }

        try {
            DB::beginTransaction();
            $email = Email::create([
                'subject' => $emailParams['subject'],
                'body' => $emailParams['body'],
                'format' => $emailParams['format']
            ]);
            
            $email->recipients()->createMany(
                array_map(function ($email) {
                    return ['address' => $email];
                }, $emailParams['recipients'])
            );

            EmailJob::dispatch($email);
            DB::commit();
            $response['status'] = 'success';
            $response['message'] = 'Your email has been queued and will be sent shortly!';
        } catch (Exception $ex) {
            DB::rollback();
        }

        return $response;
    }
    
    public function updateStatusFromEvent(Iterator $events)
    {
        $query = 'REPLACE INTO recipients (email_id, address, status, provider) VALUES %s;';
        $entryTemplate = '(?, ?, ?, ?)';
        $updateList = [];
        $updateValues = [];
        
        /** @var EmailEvent $event */
        foreach ($events as $event) {
            $updateList[] = $entryTemplate;
            $updateValues[] = $event->getMessageId();
            $updateValues[] = $event->getEmail();
            $updateValues[] = $event->getStatus();
            $updateValues[] = $event->getProviderName();
        }
                
        return DB::statement(sprintf($query, implode(',', $updateList)), $updateValues);
    }
}
