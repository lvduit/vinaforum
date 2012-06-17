<?php 
vF_Check();

# ----------------------
# Main Function 
# Author: Yplit
# Date: 13/6/2012
#-----------------------

function arrayMapMerge( array $first, array $second )
{
	$args = func_get_args();
	unset($args[0]);

	foreach ($args AS $arg)
	{
		if (!is_array($arg) || !$arg)
		{
			continue;
		}
		foreach ($arg AS $key => $value)
		{
			if (array_key_exists($key, $first) && is_array($value) && is_array($first[$key]))
			{
				$first[$key] = self::mapMerge($first[$key], $value);
			}
			else
			{
				$first[$key] = $value;
			}
		}
	}

	return $first;
}
