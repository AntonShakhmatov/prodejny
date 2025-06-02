<?php

namespace app\modules\admin\modules\kitos\controllers;

use Yii;
use yii\web\Controller;

/**
 * Default controller for the `kitos` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/auth/login']);
        }
        return $this->render('index');
    }
}
