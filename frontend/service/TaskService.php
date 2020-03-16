<?php


namespace frontend\service;

use frontend\models\Files;
use frontend\models\tasks\TasksCreateForm;
use frontend\models\tasks\Tasks;
use frontend\models\tasks\TasksFile;
use yii\base\Model;
use yii;

/**
 * Task service
 */
class TaskService extends Model
{

    public function create(TasksCreateForm $model): ?string
    {
        $transaction = Yii::$app->db->beginTransaction();

        $task = new Tasks();
        $task->name = $model->name;
        $task->description = $model->description;
        $task->category_id = $model->categoryId;
        $task->price = $model->price;
        $task->deadline_time = $model->deadlineTime;
        $task->customer_id = Yii::$app->user->getIdentity()->id;
        $task->status = Tasks::STATUS_NEW;

        if (!$task->save()) {
            $transaction->rollBack();
            return null;
        }

        if (!$this->uploadFiles($model, $task->id)) {
            $transaction->rollBack();
            return null;
        }

        $transaction->commit();
        return $task->getTaskLink();
    }

    private function uploadFiles(TasksCreateForm $model, int $taskId): bool
    {
        foreach ($model->files as $file) {
            $filePath = 'taskfiles/' . $taskId . '_' . $file->name;
            if (!$file->saveAs($filePath)) {
                return false;
            }
            $fileInDb = new Files();
            $fileInDb->link = $filePath;
            if (!$fileInDb->save()) {
                return false;
            }
            $taskFile = new TasksFile();
            $taskFile->task_id = $taskId;
            $taskFile->file_id = $fileInDb->id;
            if (!$taskFile->save()) {
                return false;
            }
        }
        return true;
    }

}