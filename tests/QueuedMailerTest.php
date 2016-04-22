<?php

class QueuedMailerTest extends SapphireTest
{
    public function testMailQueue()
    {
        $to = '123456@sendto.com';
        $from = '123456@sendfrom.com';
        $subject = 'This is my test subject';
        $bcc = '123456@bcc.com';
        $cc = '123456@cc.com';
        $body = 'This is my body';

        $email = Email::create();
        $email->setTo($to);
        $email->setFrom($from);
        $email->setSubject($subject);
        $email->setBcc($bcc);
        $email->setCc($cc);
        $email->setBody($body);
        $email->send();

        $email = \WebTorque\QueuedMailer\Queue\QueuedEmail::get()->filter('To', '123456@sendto.com')->first();

        $this->assertNotNull($email, 'Should return a QueuedEmail');
        $this->assertEquals($to, $email->To);
        $this->assertEquals($from, $email->From);
        $this->assertEquals($subject, $email->Subject);
        $this->assertEquals($bcc, $email->Bcc);
        $this->assertEquals($cc, $email->Cc);
        $this->assertEquals($body, $email->HTML);
        $this->assertEquals('Queued', $email->Status, 'Mail should go into queued status');
    }
}