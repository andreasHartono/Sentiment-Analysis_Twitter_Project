<?php

declare(strict_types=1);

namespace Phpml\Math\Distance;

use Phpml\Exception\InvalidArgumentException;
use Phpml\Math\Distance;

class Overlap implements Distance
{
    /**
     * @throws InvalidArgumentException
     */
    public function distance(array $a, array $b): float
    {
        if (count($a) !== count($b)) {
            throw new InvalidArgumentException('Size of given arrays does not match');
        }

        $count = count($a);
        $hasil = 0;
        $pembilang = 0;
        $wkq = 0;
        $wkj = 0;
    
        for ($i = 0; $i < $count; $i++) {
            $pembilang += ($a[$i] * $b[$i]);
            $wkq += pow($a[$i], 2);
            $wkj += pow($b[$i], 2);
        }
        $penyebut = min($wkq, $wkj);
    
        if ($penyebut > 0) {
            $hasil = $pembilang / $penyebut;
        } else {
            $hasil = 0;
        }
    
        return ($hasil);
    }
}
