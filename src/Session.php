<?php

namespace xeonch\afksystem;

use pocketmine\player\Player;

final class Session
{
    private const DEFAULT_SESSION = [
        "timer" => 0,
        "reason" => '',
        "afk" => false,
    ];

    private static array $sessions = [];

    public function __construct(private Player $player)
    {
    }

    /**
     * @return Player|null
     */
    public function getPlayer(): ?Player
    {
        return $this->player ?? null;
    }

    /**
     * @return void
     */
    public function addSession(): void
    {
        if (!isset(self::$sessions[$this->player->getName()])) {
            self::$sessions[$this->player->getName()] = self::DEFAULT_SESSION;
        }
    }

    /**
     * @return void
     */
    public function removeSession(): void
    {
        if (isset(self::$sessions[$this->player->getName()])) {
            unset(self::$sessions[$this->player->getName()]);
        }
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function getSession(Player $player): bool
    {
        if (isset(self::$sessions[$player->getName()])) {
            return true;
        }
        return false;
    }


    /**
     * @param string $reason
     * @return void
     */
    public function setReason(string $reason): void
    {
        self::$sessions[$this->player->getName()]["reason"] = $reason;
    }


    /**
     * @return string
     */
    public function getReason(): string
    {
        return self::$sessions[$this->player->getName()]["reason"];
    }

    /**
     * @param bool $afk
     * @return void
     */
    public function setAFK(bool $afk): void
    {
        self::$sessions[$this->player->getName()]["afk"] = $afk;
    }

    /**
     * @return bool
     */
    public function getAFK(): bool
    {
        return self::$sessions[$this->player->getName()]["afk"];
    }

    /**
     * @return void
     */
    public function setTimerSession(): void
    {
        self::$sessions[$this->player->getName()]["timer"] = time();
    }

    /**
     * @param Player $player
     * @return int
     */
    public function getTimerSession(Player $player): int
    {
        return $this->getSession($player) ? self::$sessions[$player->getName()]["timer"] : 0;
    }
}