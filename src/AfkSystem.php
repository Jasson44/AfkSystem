<?php

<<<<<<< HEAD
/**
MIT License

Copyright (c) 2025 Jasson44

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

=======
>>>>>>> 894b5eb (Update 1.5.0)
declare(strict_types=1);

namespace xeonch\afksystem;

use CortexPE\Commando\exception\HookAlreadyRegistered;
use CortexPE\Commando\PacketHooker;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use Vecnavium\FormsUI\FormsUI;
use xeonch\afksystem\commands\AfkCommand;
use xeonch\afksystem\task\CheckTask;
use xeonch\afksystem\utils\History;
use xeonch\afksystem\utils\Utils;

class AfkSystem extends PluginBase
{

    use SingletonTrait;

    protected const CONFIG_VERSION = 1;


    public function onLoad(): void
    {
        $this->loadCheck();
    }

    /**
     * @throws HookAlreadyRegistered
     */
    public function onEnable(): void
    {
        self::setInstance($this);
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getScheduler()->scheduleRepeatingTask(new CheckTask(), 20);
        $this->loadVirions();
<<<<<<< HEAD
        $this->getServer()->getCommandMap()->register("afksystem", new AfkCommand($this, "afksystem", "Set your afk status", ["afk"]));
=======
        $this->getServer()->getCommandMap()->register("afk", new AfkCommand($this, "afk", "Set your afk status", ["afksystem"]));
>>>>>>> 894b5eb (Update 1.5.0)
    }

    /**
     * @param Player $player
     * @return Session|null
     */
    public function getSession(Player $player): ?Session
    {
        return new Session($player);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function noLongerAfk(Player $player): void
    {
        $session = $this->getSession($player);
        $reason = $session->getReason() !== '' ? $session->getReason() : 'No reason provided';

        if ($session->getSession($player)) {
            if ($session->getAFK()) {

                $session->setAFK(false);
                $player->setScoreTag("");

                if ($this->getConfig()->get("history")["enable"]) {
                    $history = new History();
                    $history->addHistory($player, $reason, Utils::getAFKTime($player));
                }

                if ($this->getConfig()->get("broadcast-afk")["enable"]) {
                    $this->getServer()->broadcastMessage(TextFormat::colorize(str_replace(["{PLAYER}", "{TIMER}", "{REASON}"], [$player->getName(), Utils::getAFKTime($player), $reason], $this->getConfig()->get("broadcast-afk")["no-longer"])));
                }
                $this->sendMessage($player, TextFormat::colorize(str_replace("{TIME}", Utils::getAFKTime($player), $this->getConfig()->get("msg")["no-longer-afk"])));

                $session->setReason("");
            }
        }
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function isPlayerAFK(Player $player): bool
    {
        $session = $this->getSession($player);
        if (!$session->getSession($player)) {
            return false;
        }

        $lastMove = $session->getTimerSession($player);
        $timeSinceLastMove = time() - $lastMove;

        return $timeSinceLastMove >= $this->getConfig()->get("afk-time");
    }

    /**
     * @param Player $player
     * @param string $message
     * @param string $type
     * @return void
     */
    public function sendMessage(Player $player, string $message, string $type = "msg"): void
    {
        match ($type) {
            "title" => str_contains($message, ':')
                ? $player->sendTitle(explode(':', $message, 2)[0], explode(':', $message, 2)[1])
                : $player->sendTitle($message),
            "tip" => $player->sendTip($message),
            "actionbar" => $player->sendActionBarMessage($message),
            "popup" => $player->sendPopup($message),
            default => $player->sendMessage($message)
        };
    }

    /**
     * @return void
     */
    protected function loadCheck(): void
    {
        if ($this->getConfig()->get("history")["enable"]) {
            if (!is_file($this->getDataFolder() . "history.log")) {
                file_put_contents($this->getDataFolder() . "history.log", "");
            }
        }
        if ((!$this->getConfig()->exists("version")) || ($this->getConfig()->get("version") != self::CONFIG_VERSION)) {
            rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config_old.yml");
            $this->saveResource("config.yml");
            $this->getLogger()->critical("Your configuration file is outdated.");
            $this->getLogger()->notice("Your old configuration has been saved as config_old.yml and a new configuration file has been generated. Please update accordingly.");
        }
    }

    /**
     * @return void
     * @throws HookAlreadyRegistered
     */
    protected function loadVirions(): void
    {
        foreach (
            [
                "Commando" => PacketHooker::class,
                "FormsUI" => FormsUI::class
            ] as $virion => $class) {
            if (!class_exists($class)) {
                $this->getLogger()->error($virion . " virion not found. Please download AfkSystem from Poggit-CI or use DEVirion (not recommended).");
                $this->getServer()->getPluginManager()->disablePlugin($this);
                return;
            }
        }
        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }
    }
}
