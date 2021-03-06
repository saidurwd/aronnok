<?php

class LocationController extends BackEndController {

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout = '//layouts/column2';
    
    protected function beforeAction($action) {
        $access = $this->checkAccess(Yii::app()->controller->id, Yii::app()->controller->action->id);
        if ($access == 1) {
            return true;
        } else {
            Yii::app()->user->setFlash('error', "You are not authorized to perform this action!");
            $this->redirect(array('/site/noaccess'));
        }
    }

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow', // allow all users to perform 'index' and 'view' actions
                'actions' => array('index', 'view'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('admin', 'delete', 'create', 'update'),
                'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('admin', 'delete', 'create', 'update'),
                'users' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $this->render('view', array(
            'model' => $this->loadModel($id),
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model = new Location;
        $path = Yii::app()->basePath . '/../uploads/images';
        if (!is_dir($path)) {
            mkdir($path);
        }

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Location'])) {
            $model->attributes = $_POST['Location'];
            if ($model->validate()) {
                //Picture upload script
                if (@!empty($_FILES['Location']['name']['picture'])) {
                    $model->picture = $_POST['Location']['picture'];

                    if ($model->validate(array('picture'))) {
                        $model->picture = CUploadedFile::getInstance($model, 'picture');
                    } else {
                        $model->picture = null;
                    }
                    $model->picture->saveAs($path . '/' . time() . '_' . str_replace(' ', '_', strtolower($model->picture)));
                    $model->picture = time() . '_' . str_replace(' ', '_', strtolower($model->picture));
                }
                if ($model->save()) {
                    Yii::app()->user->setFlash('success', 'Location has been created successfully');
                    $this->redirect(array('admin'));
                }
            }
        }

        $this->render('create', array(
            'model' => $model,
        ));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model = $this->loadModel($id);
        $previuosFileName = $model->picture;
        $path = Yii::app()->basePath . '/../uploads/images';
        if (!is_dir($path)) {
            mkdir($path);
        }
        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Location'])) {
            $model->attributes = $_POST['Location'];
            if ($model->validate()) {
                //Picture upload script
                if (@!empty($_FILES['Location']['name']['picture'])) {
                    $model->picture = $_POST['Location']['picture'];

                    if ($model->validate(array('picture'))) {
                        $myFile = $path . '/' . $previuosFileName;
                        if ((is_file($myFile)) && (file_exists($myFile))) {
                            unlink($myFile);
                        }
                        $model->picture = CUploadedFile::getInstance($model, 'picture');
                    } else {
                        $model->picture = null;
                    }
                    $model->picture->saveAs($path . '/' . time() . '_' . str_replace(' ', '_', strtolower($model->picture)));
                    $model->picture = time() . '_' . str_replace(' ', '_', strtolower($model->picture));
                } else {
                    $model->picture = $previuosFileName;
                }
                if ($model->save()) {
                    Yii::app()->user->setFlash('success', 'Location has been saved successfully');
                    $this->redirect(array('admin'));
                }
            }
        }

        $this->render('update', array(
            'model' => $model,
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id) {
        if (Yii::app()->request->isPostRequest) {
            // we only allow deletion via POST request
            $this->loadModel($id)->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if (!isset($_GET['ajax'])) {
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
            }
        } else {
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {
        $dataProvider = new CActiveDataProvider('Location');
        $this->render('index', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model = new Location('search');
        $model->unsetAttributes();  // clear any default values
        if (isset($_GET['Location'])) {
            $model->attributes = $_GET['Location'];
        }

        $this->render('admin', array(
            'model' => $model,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return Location the loaded model
     * @throws CHttpException
     */
    public function loadModel($id) {
        $model = Location::model()->findByPk($id);
        if ($model === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param Location $model the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'location-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

}
