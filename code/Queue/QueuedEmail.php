<?php

namespace WebTorque\QueuedMailer\Queue;

class QueuedEmail extends \DataObject
{
    private static $db = array(
        'To' => 'Varchar(500)',
        'From' => 'Varchar(500)',
        'Subject' => 'Varchar(500)',
        'CC' => 'Varchar(500)',
        'BCC' => 'Varchar(500)',
        'HTML' => 'HTMLText',
        'Plain' => 'Text',
        'ReplyTo' => 'Varchar(500)',
        'Attachments' => 'Text',
        'Headers' => 'Text',
        'LastAttempt' => 'SS_Datetime',
        'Status' => 'Enum("Queued, Processing, Sent, Failed, Retry", "Queued")',
        'Identifier' => 'Varchar(100)'
    );

    /**
     * Adds arrays, should be in the form:
     * array(
     *  'filename' => 'filedata'
     * )
     *
     * Contents of attachments are base64 encoded in DB
     *
     * @param array $attachments array attachments
     * @return $this
     */
    public function addAttachments(array $attachments)
    {
        $encodedContents = array();
        //base64 encode contents otherwise json_encode fails
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $attachment['contents'] = base64_encode($attachment['contents']);
                $encodedContents[] = $attachment;
            }
        }

        $this->Attachments = json_encode($encodedContents);
        return $this;
    }

    /**
     * Returns an array of attachments in the form:
     * array(
     *  'filename' => 'base64encoded file data'
     * )
     *
     * @return array
     */
    public function loadAttachments()
    {
        $return = array();
        if ($this->Attachments) {
            $attachments = json_decode($this->Attachments, true);

            foreach ($attachments as $attachment) {
                $return[$attachment['filename']] = $attachment['contents'];
            }
        }

        return $return;
    }

    /**
     * Add an array of headers, should be in the form:
     * array(
     *  'headername' => 'headervalue'
     * )
     *
     * @param array $headers
     * @return $this
     */
    public function addHeaders(array $headers)
    {
        $this->Headers = json_encode($headers);
        return $this;
    }

    /**
     * Returns the headers in an array in the format:
     * array(
     *  'headername' => 'headervalue'
     * )
     *
     * @return array
     */
    public function loadHeaders()
    {
        if ($this->Headers) {
            return json_decode($this->Headers, true);
        }
    }
}