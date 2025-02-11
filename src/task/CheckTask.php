<?php

namespace xeonch\afksystem\task;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use xeonch\afksystem\AfkSystem as Main;
use xeonch\afksystem\libs\discordwebhook\DiscordWebhookHelper;
use xeonch\afksystem\utils\Utils;

class CheckTask extends Task
{

    private static array $lastBroadcast = [];

    public function __construct()
    {
    }

    public function onRun(): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($player->isOnline()) {
                $session = Main::getInstance()->getSession($player);
                $playerName = $player->getName();
                if ($session->getSession($player)) {
                    if (!$session->getAFK() and Main::getInstance()->isPlayerAFK($player)) {
                        $session->setAFK(true);
                    }

                    if (!$session->getAFK()) {
                        self::$lastBroadcast[$playerName] = 0;
                        continue;
                    }

                    if ($session->getAFK()) {

                        $reason = $session->getReason() !== '' ? $session->getReason() : 'No reason provided';

                        if (Main::getInstance()->getConfig()->get("kick")["enable"]) {
                            $player->kick(TextFormat::colorize(str_replace(['\n', "{TIME}", "{REASON}"], ["\n", Utils::getAFKTime($player), $reason], Main::getInstance()->getConfig()->get("kick")["message"])));
                        }

                        if (Main::getInstance()->getConfig()->get("broadcast-afk")["enable"]) {
                            if (!isset(self::$lastBroadcast[$playerName])) {
                                self::$lastBroadcast[$playerName] = 0;
                            }
                            self::$lastBroadcast[$playerName]++;

                            if (self::$lastBroadcast[$playerName] == 1) {
                                DiscordWebhookHelper::sendMessage(0, [
                                    "{PLAYER}" => $playerName,
                                    "{REASON}" => $reason,
                                    "{TIME}" => date("H:i:s"),
                                    "{DATE}" => date("d F Y"),
                                    "{X}" => (string)Server::getInstance()->getPlayerExact($playerName)->getPosition()->getX(),
                                    "{Y}" => (string)Server::getInstance()->getPlayerExact($playerName)->getPosition()->getY(),
                                    "{Z}" => (string)Server::getInstance()->getPlayerExact($playerName)->getPosition()->getZ(),
                                ]);
                                Server::getInstance()->broadcastMessage(TextFormat::colorize(str_replace(
                                    ["{PLAYER}", "{TIMER}", "{REASON}"],
                                    [$playerName, Utils::getAFKTime($player), $reason],
                                    Main::getInstance()->getConfig()->get("broadcast-afk")["message"]
                                )));
                            }
                        }

                        if (Main::getInstance()->getConfig()->get("namedtag")["enable"]) {
                            $player->setScoreTag(TextFormat::colorize(str_replace(["{TIME}", "{PLAYER}", "{REASON}", "\n"], [Utils::getAFKTime($player), $player->getName(), $reason, "\n"], Main::getInstance()->getConfig()->get("namedtag")["name"])));
                        }

                        if (Main::getInstance()->getConfig()->get("notify")["enable"]) {
                            Main::getInstance()->sendMessage(
                                $player,
                                TextFormat::colorize(str_replace("{TIME}", Utils::getAFKTime($player), Main::getInstance()->getConfig()->get("notify")["message"])),
                                Main::getInstance()->getConfig()->get("notify")["type"]);
                        }
                    }
                }
            }
        }
    }
}
