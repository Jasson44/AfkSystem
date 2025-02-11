<?php

namespace xeonch\afksystem\libs\discordwebhook;

use pocketmine\Server;
use xeonch\afksystem\libs\discordwebhook\async\SendAsyncTask;
use xeonch\afksystem\libs\discordwebhook\type\Message;

class DiscordWebhook
{

    /** @var string */
    protected string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getURL(): string
    {
        return $this->url;
    }

    public function isValid(): bool
    {
        return filter_var($this->url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * @param Message $message
     * @return void
     */
    public function send(Message $message): void
    {
        Server::getInstance()->getAsyncPool()->submitTask(new SendAsyncTask($this, $message));
    }
}