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

    public function hashrateFilter($rate)
    {
        $units = ['h/s', 'Kh/s', 'Mh/s', 'Gh/s', 'Th/s', 'Ph/s', 'Eh/s', 'Zh/s', 'Yh/s'];
	$unit = intval(log(abs(intval($rate)), 1024));

	if (array_key_exists($unit, $units))
		return sprintf('%.2f %s', $rate / pow(1024, $unit), $units[$unit]);

	return $rate;
    }
}
