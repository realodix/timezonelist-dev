<?php

require_once __DIR__.'/vendor/autoload.php';

$tz = new \Realodix\Timezonelist\Timezonelist;
echo $tz->toSelectBox('timezone_default');

$tz = new \Realodix\Timezonelist\Timezonelist;
echo '<br><br>';
echo 'toSelectBox(\'timezone_default\', \'America/New_York\') <br>';
echo $tz->toSelectBox('timezone_default', 'America/New_York');

$tz = new \Realodix\Timezonelist\Timezonelist;
echo '<br><br>';
echo 'splitGroup(false) <br>';
echo $tz->splitGroup(false)
    ->toSelectBox('timezone_default', 'America/New_York');

$tz = new \Realodix\Timezonelist\Timezonelist;
echo '<br><br>';
echo 'onlyGroups([\'America\', \'Asia\']) <br>';
echo $tz->onlyGroups(['America', 'Asia'])
    ->toSelectBox('timezone_default');

$tz = new \Realodix\Timezonelist\Timezonelist;
echo '<br><br>';
echo 'splitGroup(false)->onlyGroups([\'America\', \'Asia\']) <br>';
echo $tz->splitGroup(false)->onlyGroups(['America', 'Asia'])
    ->toSelectBox('timezone_default', 'America/New_York');

$tz = new \Realodix\Timezonelist\Timezonelist;
echo '<br><br>';
echo 'onlyGroups([\'Arctic\']) <br>';
echo $tz->onlyGroups(['Arctic'])
    ->toSelectBox('timezone_only_america');
