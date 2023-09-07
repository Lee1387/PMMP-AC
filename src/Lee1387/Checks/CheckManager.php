<?php

namespace Lee1387\Checks;

use Lee1387\Checks\Combat\AutoClicker;
use Lee1387\Checks\Combat\Hitbox;
use Lee1387\Checks\Combat\ImpossibleRotations;
use Lee1387\Checks\Combat\Reach;
use Lee1387\Checks\Movement\Timer;
use Lee1387\Checks\Packets\BadPacketsA;
use Lee1387\Checks\World\GhostHand;

class CheckManager
{

    /**
     * @var Check[]
     */
    public array $Checks = [];

    public function __construct()
    {
        $this->Checks[] = new Reach();
        $this->Checks[] = new Hitbox();
        $this->Checks[] = new Timer();
        $this->Checks[] = new AutoClicker();
        $this->Checks[] = new BadPacketsA();
        $this->Checks[] = new GhostHand();
        $this->Checks[] = new ImpossibleRotations();
    }

    /**
     * @return Check[]
     */
    public function getChecks() : array
    {
        return $this->Checks;
    }

    public function getCheckByName(string $name) : ?Check
    {
        foreach ($this->getChecks() as $check){
            if ($check->getName() == $name){
                return $check;
            }
        }
        return null;
    }

}