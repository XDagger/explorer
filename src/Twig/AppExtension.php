<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return array(
            new TwigFilter('format_hashrate', array($this, 'hashrateFilter')),
        );
    }

    public function hashrateFilter($number)
    {
        $base = log($number) / log(1000);
        $suffix = array('MH/s', 'GH/s', 'TH/s', 'PH/s')[floor($base)];
        return number_format(pow(1000, $base - floor($base)), 2, '.', '') . $suffix;
    }
}
