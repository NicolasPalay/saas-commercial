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
    
    }
       