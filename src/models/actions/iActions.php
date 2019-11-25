<?php


namespace app\models\actions;

use app\models\Task;

interface iActions
{
    public static function getNameClass();
    public static function getActionName();
    public static function verifyAction(Task $task) :bool;
}
