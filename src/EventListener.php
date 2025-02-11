<?php

namespace xeonch\afksystem;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerEmoteEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use xeonch\afksystem\AfkSystem as Main;
use xeonch\afksystem\utils\History;
use xeonch\afksystem\utils\Utils;

class EventListener implements Listener
{

    /**
     * @param PlayerJoinEvent $event
     * @return void
     */
    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $session = Main::getInstance()->getSession($player);
        $session->addSession();
        $session->setTimerSession();
    }

    /**
     * @param PlayerQuitEvent $event
     * @return void
     */
    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $session = Main::getInstance()->getSession($player);
        $reason = $session->getReason() !== '' ? $session->getReason() : 'No reason provided';
        if (Main::getInstance()->getConfig()->get("history")["enable"]) {
            $history = new History();
            $history->addHistory($player, $reason, Utils::getAFKTime($player));
        }
        $session->removeSession();
    }

    /**
     * @param PlayerMoveEvent $event
     * @return void
     */
    public function onMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        $session = Main::getInstance()->getSession($player);
        Main::getInstance()->noLongerAfk($player);
        $session->setTimerSession();
    }

    /**
     * @param PlayerEmoteEvent $event
     * @return void
     */
    public function onEmote(PlayerEmoteEvent $event): void
    {
        $player = $event->getPlayer();
        $session = Main::getInstance()->getSession($player);
        Main::getInstance()->noLongerAfk($player);
        $session->setTimerSession();
    }


    /**
     * @param PlayerDeathEvent $event
     * @return void
     */
    public function onDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();
        $session = Main::getInstance()->getSession($player);
        Main::getInstance()->noLongerAfk($player);
        $session->setTimerSession();
    }

    /**
     * @param PlayerChatEvent $event
     * @return void
     */
    public function onChat(PlayerChatEvent $event): void
    {
        $chat = $event->getMessage();
        $player = $event->getPlayer();

        $sessionP = Main::getInstance()->getSession($player);
        Main::getInstance()->noLongerAfk($player);
        $sessionP->setTimerSession();

        if (str_contains($chat, "@")) {
            preg_match_all('/@(\w+)/', $chat, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $mentionedPlayer) {
                    $target = Server::getInstance()->getplayerExact($mentionedPlayer);
                    if ($target !== null) {
                        $session = Main::getInstance()->getSession($target);
                        $reason = $session->getReason() !== '' ? $session->getReason() : 'No reason provided';
                        if ($session->getAFK()) {
                            if (!Main::getInstance()->getConfig()->get("settings")["can-mention"]) {
                                $event->cancel();
                                Main::getInstance()->sendMessage($player, TextFormat::colorize(str_replace(["{TIME}", "{PLAYER}", "{REASON}"], [Utils::getAFKTime($target), $target->getName(), $reason], Main::getInstance()->getConfig()->get("msg")["mention-when-afk"])));
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param EntityDamageEvent $event
     * @return void
     */
    public function onDamage(EntityDamageEvent $event): void
    {
        $victim = $event->getEntity();
        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            if ($victim instanceof Player) {
                $session = Main::getInstance()->getSession($victim);
                $reason = $session->getReason() !== '' ? $session->getReason() : 'No reason provided';
                if ($session->getAFK()) {
                    if (!Main::getInstance()->getConfig()->get("settings")["can-attack"]) {
                        $event->cancel();
                        if ($damager instanceof Player) {
                            Main::getInstance()->sendMessage($damager, TextFormat::colorize(str_replace(["{TIME}", "{PLAYER}", "{REASON}"], [Utils::getAFKTime($victim), $victim->getName(), $reason], Main::getInstance()->getConfig()->get("msg")["hit-when-afk"])));
                        }
                    }
                }
            }
        }
    }
}