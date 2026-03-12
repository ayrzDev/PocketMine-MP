<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\entity\Living;

class Elytra extends Armor
{

    public function onTickWorn(Living $entity) : bool
    {

        if ($entity->isGliding()) {
            if (($entity->ticksLived % 20) === 0) {
                if ($this->applyDamage(1)) {
                    return true;
                }
            }
        }

        return false;
    }
}