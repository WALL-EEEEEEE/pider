<?php
/**
 * @interface Schedule 
 * A interface of Schedule 
 */
namespace Pider\Kernel;

interface Schedule {
    /**
     * Add task to Schedule 
     */
    public function add($item);
    /**
     * Schedule tasks and Generate a priority task lists
     */
    public function schedule();

    /**
     * Start the schedule process
     */
    public function run();
}
