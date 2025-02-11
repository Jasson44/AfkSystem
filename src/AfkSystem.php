<?php

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
use xeonch\afksystem\libs\discordwebhook\DiscordWebhook;
use xeonch\afksystem\libs\discordwebhook\DiscordWebhookHelper;
use xeonch\afksystem\libs\discordwebhook\exception\DiscordWebhookException;
use xeonch\afksystem\task\CheckTask;
use xeonch\afksystem\utils\History;
use xeonch\afksystem\utils\Utils;

class AfkSystem extends PluginBase
{

    use SingletonTrait;

    protected const CONFIG_VERSION = 1.5;

    public ?DiscordWebhook $discordWebhookAFK = null;
    public ?DiscordWebhook $discordWebhookNoLonger = null;

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
        $this->discordWebhookCheck();
        $this->getServer()->getCommandMap()->register("afk", new AfkCommand($this, "afk", "Set your afk status", ["afksystem"]));
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

                DiscordWebhookHelper::sendMessage(1, [
                    "{DURATION}" => Utils::getAFKTime($player),
                    "{REASON}" => $reason,
                    "{PLAYER}" => $player->getName(),
                    "{TIME}" => date("H:i:s"),
                    "{DATE}" => date("d F Y"),
                    "{X}" => (string)$player->getPosition()->getX(),
                    "{Y}" => (string)$player->getPosition()->getY(),
                    "{Z}" => (string)$player->getPosition()->getZ(),
                ]);

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
     */
    protected function discordWebhookCheck(): void
    {
        // When AFK Discord Webhook Check
        $cfg = $this->getConfig()->get("discord-webhook", []);

        // Cek webhook "when-afk"
        if (!isset($cfg['when-afk']["enable"]) || !$cfg['when-afk']["enable"]) {
            $this->getLogger()->info("Discord webhook when afk is disabled");
        } else {
            try {
                if (!empty($cfg['when-afk']["webhook-url"]) && is_string($cfg['when-afk']["webhook-url"])) {
                    $this->discordWebhookAFK = new DiscordWebhook(trim($cfg['when-afk']["webhook-url"]));
                    $this->getLogger()->info("AFK Discord webhook URL has been set");
                } else {
                    throw new DiscordWebhookException("Discord webhook is active but URL is missing for AFK.");
                }
            } catch (DiscordWebhookException $e) {
                $this->getLogger()->error($e->getMessage());
            }
        }

        // When No Longer AFK Discord Webhook Check
        if (!isset($cfg['no-longer']["enable"]) || !$cfg['no-longer']["enable"]) {
            $this->getLogger()->info("Discord webhook when no longer afk is disabled");
        } else {
            try {
                if (!empty($cfg['no-longer']["webhook-url"]) && is_string($cfg['no-longer']["webhook-url"])) {
                    $this->discordWebhookNoLonger = new DiscordWebhook(trim($cfg['no-longer']["webhook-url"]));
                    $this->getLogger()->info("No longer AFK Discord webhook URL has been set");
                } else {
                    throw new DiscordWebhookException("Discord webhook is active but URL is missing for No Longer AFK.");
                }
            } catch (DiscordWebhookException $e) {
                $this->getLogger()->error($e->getMessage());
            }
        }
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
