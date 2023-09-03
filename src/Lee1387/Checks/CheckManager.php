<?php

namespace Lee1387\Checks;

use Lee1387\Checks\Combat\Hitbox;
use Lee1387\Checks\Combat\Reach;

class CheckManager {

    /**
     * @var Check[]
     */
    public array $Checks = [];

    public function __construct() {
        $this->Checks[] = new Reach();
        $this->Checks[] = new Hitbox();
    }

    /**
     * @return Check[]
     */
    public function getChecks(): array {
        return $this->Checks;
    }

    public function getCheckByName(string $name): ?Check {
        foreach ($this->getChecks() as $check) {
            if ($check->getName() == $name) {
                return $check;
            }
        }
        return null;
    }
    
}