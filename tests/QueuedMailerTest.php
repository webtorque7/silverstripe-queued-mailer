<?php

/**
 * @todo Create a Mock object for SendinBlue api so can better test all email fields etc (and not actually require sending
 * emails)
 *
 * Class QueuedMailerTest
 */
class QueuedMailerTest extends FunctionalTest
{
    public function testMailQueue()
    {
        $to = '123456@sendto.com';
        $from = '123456@sendfrom.com';
        $subject = 'This is my test subject';
        $bcc = '123456@bcc.com';
        $cc = '123456@cc.com';
        $body = 'This is my body';

        Injector::inst()->registerService(new \WebTorque\QueuedMailer\Mailer\QueuedMailer(), 'Mailer');
        Email::set_mailer(Injector::inst()->get('Mailer'));

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
        $this->assertEquals($bcc, $email->BCC);
        $this->assertEquals($cc, $email->CC);
        $this->assertContains($body, $email->HTML);
        $this->assertEquals('Queued', $email->Status, 'Mail should go into queued status');

        $email->delete();
    }

    public function testQueueProcessor()
    {
        $to = 'Conrad Dobbs<conrad@webtorque.co.nz>';
        $from = 'test@webtorque.co.nz';
        $subject = 'This is my test subject';
        $body = 'This is my body';

        Injector::inst()->registerService(new \WebTorque\QueuedMailer\Mailer\QueuedMailer(), 'Mailer');
        Email::set_mailer(Injector::inst()->get('Mailer'));

        $email = Email::create();
        $email->setTo($to);
        $email->setFrom($from);
        $email->setSubject($subject);;
        $email->setBody($body);
        $email->send();

        $processor = Injector::inst()->get('QueueProcessor');
        $processor->process();

        $sentEmail = \WebTorque\QueuedMailer\Queue\QueuedEmail::get()->filter('To', $to)->first();

        $this->assertNotNull($sentEmail, 'Should return a QueuedEmail');
        $this->assertEquals('Sent', $sentEmail->Status);
        $this->assertNotEmpty($sentEmail->Identifier);
    }
}