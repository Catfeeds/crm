<?php
/**
 * Created by PhpStorm.
 * User: xjun
 * Date: 2017/3/9
 * Time: 11:21
 */

namespace common\server;


use yii\base\Arrayable;
use yii\base\Model;
use yii\data\Pagination;
use yii\debug\models\timeline\DataProvider;
use yii\data\DataProviderInterface;

class Serializer extends \yii\rest\Serializer
{
    public $collectionEnvelope = 'items';


    /**
     * Serializes the given data into a format that can be easily turned into other formats.
     * This method mainly converts the objects of recognized types into array representation.
     * It will not do conversion for unknown object types or non-object data.
     * The default implementation will handle [[Model]] and [[DataProviderInterface]].
     * You may override this method to support more object types.
     * @param mixed $data the data to be serialized.
     * @return mixed the converted data.
     */
    public function serialize($data)
    {
        if ($data instanceof Model && $data->hasErrors()) {
            return $this->serializeModelErrors($data);
        } elseif ($data instanceof Arrayable) {
            return $this->serializeModel($data);
        } elseif ($data instanceof DataProviderInterface) {
            return $this->serializeDataProvider($data);
        } else {
            return $this->_return($data);
        }
    }

    /**
     * Serializes the validation errors in a model.
     * @param Model $model
     * @return array the array representation of the errors
     */
    protected function serializeModelErrors($model)
    {
        $this->response->setStatusCode(422, 'Data Validation Failed.');
        $result = [];
        foreach ($model->getFirstErrors() as $name => $message) {
            $result[] = [
                'field' => $name,
                'message' => $message,
            ];
        }
        return $this->_return($result);
    }

    /**
     * Serializes a model object.
     * @param Arrayable $model
     * @return array the array representation of the model
     */
    protected function serializeModel($model)
    {
        if ($this->request->getIsHead()) {
            return $this->_return([]);
        } else {
            list ($fields, $expand) = $this->getRequestedFields();
            return $this->_return($model->toArray($fields, $expand));
        }
    }

    /**
     * Serializes a data provider.
     * @param DataProvider $dataProvider
     * @return array the array representation of the data provider.
     */
    protected function serializeDataProvider($dataProvider)
    {
        if ($this->preserveKeys) {
            $models = $dataProvider->getModels();
        } else {
            $models = array_values($dataProvider->getModels());
        }
        $models = $this->serializeModels($models);

        if (($pagination = $dataProvider->getPagination()) !== false) {
            $this->addPaginationHeaders($pagination);
        }

        if ($this->request->getIsHead()) {
            return $this->_return([]);
        } elseif ($this->collectionEnvelope === null) {
            return $this->_return($models);
        } else {
            $result = [
                $this->collectionEnvelope => $models,
            ];
            if ($pagination !== false) {
                return $this->_return(array_merge($result, $this->serializePagination($pagination)));
            } else {
                return $this->_return($result);
            }
        }
    }

    /**
     * Serializes a pagination into an array.
     * @param Pagination $pagination
     * @return array the array representation of the pagination
     * @see addPaginationHeaders()
     */
    protected function serializePagination($pagination)
    {
        return [
            $this->metaEnvelope => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->getPageCount(),
                'currentPage' => $pagination->getPage() + 1,
                'perPage' => $pagination->getPageSize(),
            ],
        ];
    }


    /**
     * 统一返回格式
     * @param array $data
     * @return array
     */
    public function _return($data = null)
    {
        if (!$data) $data = null;
        return [
            'code' => \Yii::$app->params['code'],
            'message' => \Yii::$app->params['message'],
            'data' => $data
        ];
    }
}