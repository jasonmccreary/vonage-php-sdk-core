<?php
declare(strict_types=1);

namespace Nexmo\Voice;

use Nexmo\Client\APIClient;
use Nexmo\Voice\NCCO\NCCO;
use Nexmo\Client\APIResource;
use Nexmo\Entity\Filter\FilterInterface;
use Nexmo\Entity\Hydrator\ArrayHydrator;
use Nexmo\Entity\IterableAPICollection;
use Nexmo\Voice\NCCO\Action\Talk;

class Client implements APIClient
{
    /**
     * @var APIResource
     */
    protected $api;

    public function __construct(APIResource $api)
    {
        $this->api = $api;
    }

    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    /**
     * @return array{uuid: string, conversation_uuid: string, status: string, direction: string}
     */
    public function createOutboundCall(OutboundCall $call) : array
    {
        $json = [
            'to' => [$call->getTo()],
            'from' => $call->getFrom(),
        ];

        if (null !== $call->getAnswerWebhook()) {
            $json['answer_url'] = $call->getAnswerWebhook()->getUrl();
            $json['answer_method'] = $call->getAnswerWebhook()->getMethod();
        }

        if (null !== $call->getEventWebhook()) {
            $json['event_url'] = $call->getEventWebhook()->getUrl();
            $json['event_method'] = $call->getEventWebhook()->getMethod();
        }

        if (null !== $call->getNCCO()) {
            $json['ncco'] = $call->getNCCO();
        }

        $json['machine_detection'] = $call->getMachineDetection();
        $json['length_timer'] = $call->getLengthTimer();
        $json['ringing_timer'] = $call->getRingingTimer();

        $response = $this->api->create($json);
        return $response;
    }

    public function earmuffCall(string $callId) : void
    {
        $this->modifyCall($callId, CallAction::EARMUFF);
    }

    public function get(string $callId) : Call
    {
        $data = $this->api->get($callId);
        $call = (new CallFactory())->create($data);

        return $call;
    }

    public function hangupCall(string $callId) : void
    {
        $this->modifyCall($callId, CallAction::HANGUP);
    }

    public function modifyCall(string $callId, string $action) : void
    {
        $this->api->update($callId, [
            'action' => $action,
        ]);
    }

    public function muteCall(string $callId) : void
    {
        $this->modifyCall($callId, CallAction::MUTE);
    }

    /**
     * @return array{uuid: string, message: string}
     */
    public function playDTMF(string $callId, string $digits) : array
    {
        $response = $this->api->update($callId . '/dtmf', [
            'digits' => $digits
        ]);

        return $response;
    }

    /**
     * @return array{uuid: string, message: string}
     */
    public function playTTS(string $callId, Talk $action) : array
    {
        $response = $this->api->update($callId . '/talk', [
            'text' => $action->getText(),
            'voice_name' => $action->getVoiceName(),
            'loop' => $action->getLoop(),
            'level' => $action->getLevel(),
        ]);

        return $response;
    }

    public function search(FilterInterface $filter = null) : IterableAPICollection
    {
        $response = $this->api->search($filter);
        $response->setApiResource(clone $this->api);

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new Call());

        $response->setHydrator($hydrator);
        return $response;
    }

    /**
     * @return array{uuid: string, message: string}
     */
    public function stopStreamAudio(string $callId) : array
    {
        return $this->api->delete($callId . '/stream');
    }

    /**
     * @return array{uuid: string, message: string}
     */
    public function stopTTS(string $callId) : array
    {
        return $this->api->delete($callId . '/talk');
    }

    /**
     * @return array{uuid: string, message: string}
     */
    public function streamAudio(string $callId, string $url, int $loop = 1, float $volumeLevel = 0.0) : array
    {
        return $this->api->update($callId . '/stream', [
            'stream_url' => [$url],
            'loop' => $loop,
            'level' => $volumeLevel,
        ]);
    }

    public function transferCall(string $callId, NCCO $ncco) : void
    {
        $this->api->update($callId, [
            'action' => 'transfer',
            'destination' => [
                'type' => 'ncco',
                'ncco' => $ncco,
            ]
        ]);
    }

    public function unearmuffCall(string $callId) : void
    {
        $this->modifyCall($callId, CallAction::UNEARMUFF);
    }

    public function unmuteCall(string $callId) : void
    {
        $this->modifyCall($callId, CallAction::UNMUTE);
    }
}