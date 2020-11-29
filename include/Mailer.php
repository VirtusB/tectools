<?php

declare(strict_types=1);

class Mailer {
    /**
     * @var RCMS $RCMS
     */
    public RCMS $RCMS;

    /**
     * @var bool $disableAutoLoading
     * Forhindrer RCMS at loade denne klasse automatisk
     */
    public static bool $disableAutoLoading;


    public function __construct(RCMS $RCMS) {
        $this->RCMS = $RCMS;
    }

    /**
     * Sender en email.
     * Benytter konfiguration sat i config.php
     * Benytter SwiftMailer til at sende mailen.
     * @param string $fromEmail Afsender e-mail adresse
     * @param string $fromName Afsender navn
     * @param string $recepientEmail Modtager e-mail adresse
     * @param string $recepientName Modtager navn
     * @param string $subject Mailemne
     * @param string $body Mailbrødtekst, mulighed for HTML
     * @param array $physicalFiles Array af fysiske filer der eksisterer på filsystemet som vedhæftes
     * @param array $dynamicPDFFiles Array med arrays af data som laves om til PDF filer og vedhæftes
     * @param string $logoID ID'et på logoet, til brug i brødteksten
     * @param string $logoPath Stien til logoet der skal bruges i mailen
     * @return bool
     */
    public static function sendEmail(string $fromEmail, string $fromName, string $recepientEmail, string $recepientName, string $subject, string $body, array $physicalFiles = [], array $dynamicPDFFiles = [], string $logoID = '', string $logoPath = ''): bool {
        $transport = (new Swift_SmtpTransport(SMTP_HOST, SMTP_PORT, 'ssl'))
                    ->setAuthMode('LOGIN')
                    ->setUsername(SMTP_USERNAME)
                    ->setPassword(SMTP_PASSWORD);

        $mailer = new Swift_Mailer($transport);

        $message = (new Swift_Message())
            ->setSubject($subject)

            ->setFrom([$fromEmail => $fromName])

            ->setTo([$recepientEmail => $recepientName])

            ->setBody($body, 'text/html')
        ;

        if (!empty($logoPath)) {
            $attachment = Swift_Attachment::fromPath($logoPath)->setDisposition('inline');
            $attachment->getHeaders()->addTextHeader('Content-ID', "<$logoID>");
            $attachment->getHeaders()->addTextHeader('X-Attachment-Id', $logoID);
            $cid = $message->embed($attachment);

            $message->setBody($body, 'text/html');
        }

        foreach ($physicalFiles as $physicalFile) {
            $message->attach(Swift_Attachment::fromPath($physicalFile));
        }

        foreach ($dynamicPDFFiles as $dynamicPDFFile) {
            $attachment = new Swift_Attachment($dynamicPDFFile['data'], $dynamicPDFFile['filename'], 'application/pdf');
            $message->attach($attachment);
        }

        if ($mailer->send($message)) {
            return true;
        }

        return false;
    }
}