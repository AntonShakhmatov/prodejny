<?php

namespace app\modules\api\modules\shoptet\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\Cors;
use yii\filters\ContentNegotiator;
use app\models\Stocks;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class StoresController extends ActiveController
{
    public $modelClass = Stocks::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => ['application/json' => Response::FORMAT_JSON],
        ];

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Max-Age' => 3600,
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Allow-Headers' => ['*'],
            ],
        ];        

        return $behaviors;
    }

    // public function actions()
    // {
    //     $actions = parent::actions();

    //     $actions['index']['prepareDataProvider'] = function () {
    //         return new \yii\data\ActiveDataProvider([
    //             'query' => Stocks::filteredQuery()->orderBy('title'),
    //         ]);
    //     };

    //     return $actions;
    // }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }

    private $baseImageUrl = 'https://www.kitos.cz';

    public function actionIndex()
    {
        $query = Stocks::filteredQuery()->orderBy('title');
    
        // filtrace visible
        $visible = Yii::$app->request->get('visible', null);
        if ($visible !== null && $visible !== '') {
            // 1/0 nebo int
            $query->andWhere(['visible' => (int)$visible]);
        }
    
        $models = $query->all();
        $result = [];
    
        foreach ($models as $model) {
            $address = $this->extractAddressParts($model->delivery_point_address);
    
            $imageRelative = $model->image_url ?: '';
            $image = $imageRelative ? $this->baseImageUrl . $imageRelative : '';
    
            $opening = [];
            if ($model->opening_hours) {
                $opening = json_decode($model->opening_hours, true);
                $opening = array_replace([
                    "0" => null, "1" => null, "2" => null,
                    "3" => null, "4" => null, "5" => null, "6" => null,
                ], $opening ?: []);
            }
    
            $result[] = [
                "Type"        => "prodejna",
                "Region"      => $model->region ?? '',
                "Title"       => $model->title,
                "Street"      => $address['street'] ?? '',
                "City"        => $address['city'] ?? '',
                "Phone"       => $model->phone,
                "gpsLatitude" => $model->gps_latitude !== null ? (float)$model->gps_latitude : null,
                "gpsLongitude"=> $model->gps_longitude !== null ? (float)$model->gps_longitude : null,
                "Url"         => $model->shop_url,
                "Email"       => $model->email,
                "image"       => $image,
                "description" => $model->description ?: $model->note,
                "opening"     => $opening,
                "note"        => $model->note,
                "extra_note"  => $model->extra_note, 
            ];
        }
    
        return $result;
    }    

    protected function extractAddressParts($address)
    {
        $parts = explode(',', $address, 2);
        return [
            'street' => trim($parts[0] ?? ''),
            'city' => trim($parts[1] ?? ''),
        ];
    }

    protected function findModel($id)
    {
        $model = Stocks::filteredQuery()->andWhere(['id' => $id])->one();

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException("Prodejna nebyla nalezena nebo nesplňuje podmínky.");
    }

    // public function actionCreate()
    // {
    //     $data = Yii::$app->request->bodyParams;

    //     $model = new Stocks();
    //     $model->load($data, '');

    //     if ($model->validate()) {
    //         $model->save(false);
    //         return ['status' => 'ok', 'id' => $model->id];
    //     }

    //     return $model->getErrors();
    // }

    public function actionCreate()
    {
        $data = Yii::$app->request->bodyParams;
        $model = new Stocks();
        $model->load($data, '');

        if ($model->save()) {
            return ['status' => 'ok', 'id' => $model->id];
        }

        return $model->getErrors();
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $data = Yii::$app->request->bodyParams;
        $model->load($data, '');

        if ($model->save()) {
            return ['status' => 'ok', 'id' => $model->id];
        }

        return $model->getErrors();
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        return ['status' => 'deleted', 'id' => $id];
    }

    public function actionUploadImage($id)
    {
        $model = $this->findModel($id);
        $file = UploadedFile::getInstanceByName('image');

        // $basePath = '/var/www/html/web';
        $basePath = 'https://www.kitos.cz';

        $dir = Yii::getAlias($basePath . '/docs/assets/img/');
        $filename = uniqid('store_') . '.' . $file->extension;
        $fullPath = $dir . $filename;

        // if (!is_dir($dir)) {
        //     mkdir($dir, 0777, true);
        // }

        // if ($file->saveAs($fullPath)) {
        //     $model->image_url = '/docs/assets/img/' . $filename;
        //     $model->save(false);
        //     return ['status' => 'ok', 'url' => $model->image_url];
        // }

        // return ['error' => 'Upload failed'];
        return [
            'debug_uploaded_files' => $_FILES,
            'error' => 'No file uploaded',
        ];
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        if (!$model) {
            throw new NotFoundHttpException("Store not found with ID: $id");
        }

        return [
            'id' => $model->id,
            'title' => $model->title,
            'region' => $model->region,
            'address' => $model->delivery_point_address,
            'latitude' => $model->gps_latitude,
            'longitude' => $model->gps_longitude,
            'opening_hours' => $this->extractOpeningHours($model->delivery_point_address),
            'phone' => $model->phone,
            'email' => $model->email,
            'image_url' => $model->image_url,
            'note' => $model->note,
            'extra_note' => $model->extra_note,
        ];
    }

    // protected function extractAddress($address)
    // {
    //     $pattern = '/(.+?)(tel\.\:|e-mail\:|Po)/ui';
    //     if (preg_match($pattern, $address, $matches)) {
    //         return trim($matches[1]);
    //     }

    //     return $address;
    // }



    protected function extractOpeningHours($address)
    {
        $pattern = '/e-mail:\s*[\w\.\-]+@[\w\-]+\.[\w\.]+\s*(Po.*)$/ui';

        if (preg_match($pattern, $address, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

}

