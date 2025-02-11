<?php

namespace xeonch\afksystem\utils;

use pocketmine\player\Player;
use xeonch\afksystem\Session;

class Utils
{

    /**
     * @param Player $player
     * @return string
     */
    public static function getAFKTime(Player $player): string
    {
        $session = new Session($player);
        if (!$session->getSession($player)) {
            return "0 Second";
        }

        $lastMove = $session->getTimerSession($player);

        $timeSinceLastMove = time() - $lastMove;
        if ($timeSinceLastMove < 60) {
            return $timeSinceLastMove . " Second";
        }

        if ($timeSinceLastMove < 3600) {
            $minutes = floor($timeSinceLastMove / 60);
            $seconds = $timeSinceLastMove % 60;
            return $minutes . " Minute " . $seconds . " Second";
        }

        $hours = floor($timeSinceLastMove / 3600);
        $minutes = floor(($timeSinceLastMove % 3600) / 60);
        $seconds = $timeSinceLastMove % 60;
        return $hours . " Hours " . $minutes . " Minute " . $seconds . " Second";
    }
}