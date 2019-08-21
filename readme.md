# Email Microservice

This service is designed to send transactional emails with a high degree of certainty.

## Installation

- Clone this repository
- Copy the .env.example file to .env
- Add the credentials for both sendgrid and mailjet to the environment file

## Running the microservice

Start the containers
```console
docker-compose up
```

Run database migrations
```console
docker-compose run --rm app php artisan migrate
```

The application runs at `{app_container_ip_or_url}:8080`.

To send an email using the API, send a POST request to the send email endpoint `/api/sendemail` with the following fields 
- `subject`, the subject of the email
- `body`, the body of the email 
- `recipients`, an array of valid email addresses
- `format`, must be either of plain, html or markdown

Sample post request data
```json
{
    "subject": "Test subject",
    "body": "This is the body of the email",
    "recipients": ["test@example.com", "test2@example.com"],
    "format": "plain"
}
```

To send an email using the CLI, run the `php artisan email:send` command 
- `subject`, the subject of the email
- `body`, the body of the email 
- `recipients`, a comma separated list of valid email addresses
- `format`, must be either of plain, html or markdown

Sample cli command
```console
php artisan email:send {recipients} {subject} {body} {format}
```

To access the VueJS app, open `{app_container_ip_or_url}:8080` in a web browser.

To recieve email status events from external email service, enable webhooks on the email service and register the webhook url `{app_container_ip_or_url}:8080/api/webhook/{service_name}`, where `service_name` is the name of the email service as specified in the implementation.

## Design Choices

- This microservice depends on several softwares/components that are each built into in separate docker containers. In order to automatically run all the containers, `docker-compose` is used.
- To use multiple external email services to ensure a high degree of email delivery, all implementation of an external email service must implement an interface (MailerProvider), be registered as a service and tagged with `provider`. This enables the mailer service aggregate all the email service implementations and attempt to use them to send emails.
- A webhook is used to receive email statuses from the external email services as they happen, this removes the need to constantly poll the email service for email statuses.

