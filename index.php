<?php

require_once __DIR__.'/vendor/autoload.php';

$tz = new \Realodix\Timezone\Timezone;
echo $tz->toSelectBox('timezone_default');

$tz = new \Realodix\Timezone\Timezone;
echo '<br><br>';
echo 'toSelectBox(\'timezone_default\', \'America/New_York\') <br>';
echo $tz->toSelectBox('timezone_default', 'America/New_York');

$tz = new \Realodix\Timezone\Timezone;
echo '<br><br>';
echo 'disableGrouping() <br>';
echo $tz->disableGrouping()
    ->toSelectBox('timezone_default', 'America/New_York');

$tz = new \Realodix\Timezone\Timezone;
echo '<br><br>';
echo 'onlyGroups([\'America\', \'Asia\']) <br>';
echo $tz->onlyGroups(['America', 'Asia'])
    ->toSelectBox('timezone_default');

$tz = new \Realodix\Timezone\Timezone;
echo '<br><br>';
echo 'disableGrouping()->onlyGroups([\'America\', \'Asia\']) <br>';
echo $tz->disableGrouping()->onlyGroups(['America', 'Asia'])
    ->toSelectBox('timezone_default', 'America/New_York');

$tz = new \Realodix\Timezone\Timezone;
echo '<br><br>';
echo 'onlyGroups([\'Arctic\']) <br>';
echo $tz->onlyGroups(['Arctic'])
    ->toSelectBox('timezone_only_america');

$tz = new \Realodix\Timezone\Timezone;
echo '<br><br>';
echo $tz->excludeGroups([
    'Africa', 'America', 'Antarctica', 'Arctic', 'Asia',
    'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacificz', 'foo',
    'General',
])->toSelectBox('exclude_all');
