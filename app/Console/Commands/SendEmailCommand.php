<?php

namespace App\Console\Commands;

use App\Services\EmailService;
use Illuminate\Console\Command;

class SendEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send
                        {recipients : The recipients of the email. If more than one recipient, each email should be separarted by a comma.}
                        {subject : The subject of the email}
                        {body : The body of the email}
                        {format : The format of the email. Allowed values are "plain", "html" and "markdown"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send transactional emails';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(EmailService $emailService)
    {
        $emailParams = [
            'subject' => $this->argument('subject'),
            'body' => $this->argument('body'),
            'format' => $this->argument('format'),
            'recipients' => explode(',', $this->argument('recipients'))
        ];
        
        $response = $emailService->enqueueEmail($emailParams);
        
        if ($response['status'] == 'success') {
            $this->info($response['message']);
        } elseif ($response['status'] == 'validation_error') {
            foreach ($response['message'] as $error) {
                $this->error($error);
            }
        } else {
            $this->error($response['message']);
        }
    }
}
