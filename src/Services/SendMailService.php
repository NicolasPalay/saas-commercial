<?php

namespace App\Services;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SendMailService
{

    public function __construct(private MailerInterface $mailer)
    {
    }
       
    public function send(string $from, string $to, string $subject,string $template,  array $content): void
    {
    $email = (new TemplatedEmail())
        ->from($from)
        ->to($to)
         ->subject($subject)
         ->htmlTemplate("emails/$template.html.twig")
         ->context($content);


    $this->mailer->send($email);
        }

        public function sendAttachment(
    string $from,
    string $to,
    string $subject,
    string $template,
    array $content = [],
    array $attachments = []
): void {
    $email = (new TemplatedEmail())
        ->from($from)
        ->to($to)
        ->subject($subject)
        ->htmlTemplate("emails/$template.html.twig")
        ->context($content);

    // 🔥 Gestion des pièces jointes
    foreach ($attachments as $attachment) {
        $email->attach(
            $attachment['data'],
            $attachment['name'],
            $attachment['type'] ?? 'application/octet-stream'
        );
    }

    $this->mailer->send($email);
}
    
    }
       