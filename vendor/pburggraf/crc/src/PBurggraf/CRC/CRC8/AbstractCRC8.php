<?php

declare(strict_types=1);

namespace PBurggraf\CRC\CRC8;

use PBurggraf\CRC\AbstractCRC;

/**
 * @author Philip Burggraf <philip@pburggraf.de>
 */
abstract class AbstractCRC8 extends AbstractCRC
{
    protected $bitLength = 8;
}
