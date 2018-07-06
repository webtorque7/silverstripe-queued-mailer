<?php

/**
 * Created by PhpStorm.
 * User: Carey
 * Date: 7/5/2018
 * Time: 3:53 PM
 */

class BounceHandler extends Controller
{
    private static $allowed_actions = array(
        'bounce'
    );

    /**
     * @throws ValidationException
     * The end point to point the SendInBlue webhook to
     * The API sends a json response to events set on the webhook, in this case we only care about bounces.
     */
    public function bounce()
    {
        $jsonResponse = file_get_contents("php://input");
        $data = json_decode($jsonResponse, true);
        $tag = Config::inst()->get('QueueProcessor', 'application_identifier');

        if (isset($data['event']) && isset($data['email']) && isset($data['tag'])) {
            //record it
            if ($data['tag'] == $tag) {
                $response = SendInBlueBounced::create();
                $response->Event = $data['event'];
                $response->Email = $data['email'];
                $response->TimeStamp = isset($data['date']) ? $data['date'] : '';
                $response->RawResponse = $jsonResponse;
                $response->write();
            }
        }
    }
}

class SendInBlueBounced extends DataObject
{
    private static $db = array(
        'Event' => 'Text',
        'Email' => 'Text',
        'TimeStamp' => 'SS_DateTime',
        'RawResponse' => 'Text',
        'Forwarded' => 'Boolean',
        'ForwardedTo' => 'Text'
    );

    private static $summary_fields = array(
        'Event' => 'Event',
        'Email' => 'Email',
        'ForwardedTo' => 'Forwarded To',
        'TimeStamp.Nice' => 'Timestamp'
    );
}

class SendInBlueAdmin extends ModelAdmin
{
    private static $url_segment = 'email-bounces';
    private static $menu_title = 'Email Bounces';
    private static $managed_models = array('SendInBlueBounced');
}