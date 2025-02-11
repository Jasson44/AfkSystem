<?php

namespace xeonch\afksystem\libs\discordwebhook;

use xeonch\afksystem\AfkSystem;
use xeonch\afksystem\libs\discordwebhook\type\Embed;
use xeonch\afksystem\libs\discordwebhook\type\Message;

class DiscordWebhookHelper
{
    private const TYPE_AFK = 0;
    private const TYPE_NO_LONGER = 1;

    private static function replaceString(string $text, array $replacements): string
    {
        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    private static function createEmbed(array $embedConfig, array $replacements): Embed
    {
        $embed = new Embed();

        if ($embedConfig['title'] !== '') {
            $embed->setTitle(self::replaceString($embedConfig['title'], $replacements));
        }

        if ($embedConfig['description'] !== '') {
            $embed->setDescription(self::replaceString($embedConfig['description'], $replacements));
        }

        if ($embedConfig['color'] !== '') {
            $embed->setColor($embedConfig['color']);
        }

        if ($embedConfig['author']['name'] !== '') {
            $embed->setAuthor(
                self::replaceString($embedConfig['author']['name'], $replacements),
                self::replaceString($embedConfig['author']['url'], $replacements),
                self::replaceString($embedConfig['author']['icon-url'], $replacements)
            );
        }

        if ($embedConfig['field'] !== []) {
            foreach ($embedConfig['field'] as $field) {
                $embed->addField(
                    self::replaceString($field['title'], $replacements),
                    self::replaceString($field['description'], $replacements),
                    $field['inline'] ?? false
                );
            }
        }

        if ($embedConfig['thumbnail'] !== '') {
            $embed->setThumbnail(self::replaceString($embedConfig['thumbnail'], $replacements));
        }

        if ($embedConfig['image'] !== '') {
            $embed->setImage(self::replaceString($embedConfig['image'], $replacements));
        }

        if ($embedConfig['footer']['content'] !== '') {
            $embed->setFooter(
                self::replaceString($embedConfig['footer']['content'], $replacements),
                self::replaceString($embedConfig['footer']['icon-url'], $replacements)
            );
        }

        return $embed;
    }

    private static function createMessage(array $messageConfig, array $embedConfig, array $replacements): Message
    {
        $message = new Message();

        if ($messageConfig['username'] !== '') {
            $message->setUsername(self::replaceString($messageConfig['username'], $replacements));
        }

        if ($messageConfig['avatar-url'] !== '') {
            $message->setAvatarURL(self::replaceString($messageConfig['avatar-url'], $replacements));
        }

        if ($messageConfig['content']) {
            $message->setContent(self::replaceString($messageConfig['content'], $replacements));
        }

        if ($embedConfig['enable']) {
            $message->addEmbed(self::createEmbed($embedConfig, $replacements));
        }

        return $message;
    }

    private static function getWebhookConfig(int $type): ?array
    {
        $config = AfkSystem::getInstance()->getConfig()->get("discord-webhook");

        return match ($type) {
            self::TYPE_AFK => [
                'webhook' => AfkSystem::getInstance()->discordWebhookAFK,
                'config' => $config['when-afk']
            ],
            self::TYPE_NO_LONGER => [
                'webhook' => AfkSystem::getInstance()->discordWebhookNoLonger,
                'config' => $config['no-longer']
            ],
            default => null
        };
    }

    public static function sendMessage(int $type, array $replacements = []): void
    {
        $webhookData = self::getWebhookConfig($type);

        if ($webhookData === null || $webhookData['webhook'] === null) {
            return;
        }

        $message = self::createMessage(
            $webhookData['config']['message'],
            $webhookData['config']['embed'],
            $replacements
        );

        $webhookData['webhook']->send($message);
    }
}