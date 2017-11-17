<?php
include_once('../../app.php');
use Module\IpSchedule\IpToolkit;


var_dump(IpToolkit::pingTimes('119.29.111.92',10));
