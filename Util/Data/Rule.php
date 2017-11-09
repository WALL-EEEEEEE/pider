<?php
namespace Util\Data;

interface Rule {
    public function detect();
    public function execute();
}

