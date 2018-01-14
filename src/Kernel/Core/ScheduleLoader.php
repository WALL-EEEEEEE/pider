<?php
namespace Pider\Kernel\Core;

/**
 * @class ScheduleLoader
 * Load Schedules into kernel
 */

class ScheduleLoader {
    private $schedules = [];
    private const DEFAULT_SCHEDULE_PATH = "Schedule";
    private const DEFAULT_NAMESPACE_PREFIX="Pider\\Schedule\\";

    public function __invoke() {
        return $this->init();
    }
    public function init() {
        $this->load();
        return $this->schedules;
    }
    public function load() {
        $schedule_path = PIDER_PATH.'/'.self::DEFAULT_SCHEDULE_PATH;
        $dirs = scandir($schedule_path);
        $classes = [];
        foreach($dirs as $dir) {
            if (!is_dir($dir) && pathinfo($dir,PATHINFO_EXTENSION) == "php" && strpos($dir,'Schedule') != 0) {
                $classname = pathinfo($dir,PATHINFO_FILENAME);
                $fclassname = self::DEFAULT_NAMESPACE_PREFIX.$classname;
                $classes[] = new $fclassname();
            }
        }
        $this->schedules = $classes;
    }

}

