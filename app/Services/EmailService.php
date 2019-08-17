<?php

namespace App\Services;

use App\Email;
use App\Jobs\EmailJob;
use App\Recipient;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
}
