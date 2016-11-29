<?php

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
        $email->addCustomHeader('queue', 1);
        $email->send();

        $email = \WebTorque\QueuedMailer\Queue\QueuedEmail::get()->filter('To', $to)->first();

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
        $email->addCustomHeader('queue', 1);
        $email->send();

        $processor = Injector::inst()->get('QueueProcessor');
        $processor->process();

        $sentEmail = \WebTorque\QueuedMailer\Queue\QueuedEmail::get()->filter('To', $to)->first();

        $this->assertNotNull($sentEmail, 'Should return a QueuedEmail');
        $this->assertEquals('Sent', $sentEmail->Status);
        $this->assertNotEmpty($sentEmail->Identifier);
    }

    public function testAttachments()
    {
        $to = 'Conrad Dobbs<conrad.test.1@webtorque.co.nz>';
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
        $email->addCustomHeader('queue', 1);
        $email->attachFileFromString('This is my file', 'test.txt');
        $email->send();

        $processor = Injector::inst()->get('QueueProcessor');
        $processor->process();

        $sentEmail = \WebTorque\QueuedMailer\Queue\QueuedEmail::get()->filter('To', $to)->first();

        $this->assertNotNull($sentEmail, 'Should return a QueuedEmail');
        $this->assertEquals('Sent', $sentEmail->Status);
        $this->assertNotEmpty($sentEmail->Attachments);

        $attachments = $sentEmail->loadAttachments();
        $this->assertNotEmpty($attachments, 'Should return an array of attachments');
        $this->assertNotEmpty($attachments['test.txt'], 'Filename should be present in attachments array');
    }

    /**
     * Todo: refactor transport so this can be tested better
     */
    public function testMultipleToAddresses() {
        $to = 'conrad.test.2@webtorque.co.nz,conrad.test.1@webtorque.co.nz';
        $from = 'test@webtorque.co.nz';
        $subject = 'This is my test subject';
        $body = 'This is my body with multiple to addresses';

        /**
         * @var \WebTorque\QueuedMailer\Transport\SendinBlueTransport
         */
        $transport = Injector::inst()->get('MailTransport');

        $result = $transport->send('ss-default', 'test', $to, $from, $subject, $body, $body, null, null, null, null);

        Debug::dump($result);
        $this->assertNotEmpty($result, 'Should return a message id');
    }
}