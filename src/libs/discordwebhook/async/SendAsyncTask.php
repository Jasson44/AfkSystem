<?php

namespace xeonch\afksystem\libs\discordwebhook\async;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\thread\NonThreadSafeValue;
use xeonch\afksystem\libs\discordwebhook\DiscordWebhook;
use xeonch\afksystem\libs\discordwebhook\type\Message;

class SendAsyncTask extends AsyncTask
{

    /** @var NonThreadSafeValue $webhook */
    protected NonThreadSafeValue $webhook;
    /** @var NonThreadSafeValue $message */
    protected NonThreadSafeValue $message;

    /**
     * @param DiscordWebhook $webhook
     * @param Message $message
     */
    public function __construct(DiscordWebhook $webhook, Message $message)
    {
        $this->webhook = new NonThreadSafeValue($webhook);
        $this->message = new NonThreadSafeValue($message);
    }

    /**
     * @return void
     */
    public function onRun(): void
    {
        $ch = curl_init($this->webhook->deserialize()->getURL());
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->message->deserialize()));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        $this->setResult([curl_exec($ch), curl_getinfo($ch, CURLINFO_RESPONSE_CODE)]);
        curl_close($ch);
    }

    /**
     * @return void
     */
    public function onCompletion(): void
    {
        $response = $this->getResult();
        if (!in_array($response[1], [200, 204])) {
            Server::getInstance()->getLogger()->error("[DiscordWebhookAPI] Got error ($response[1]): " . $response[0]);
        }
    }
}