<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UpdateXlsxLog;

/**
 * UpdateXlsxLog_Search represents the model behind the search form about `backend\models\UpdateXlsxLog`.
 */
class UpdateXlsxLogSearch extends UpdateXlsxLog
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'success_num', 'error_num', 'update_time', 'update_person_id', 'update_type', 'update_from'], 'integer'],
            [['update_file', 'error_file', 'update_person_name'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = UpdateXlsxLog::find()->orderBy('id desc');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'success_num' => $this->success_num,
            'error_num' => $this->error_num,
            'update_time' => $this->update_time,
            'update_person_id' => $this->update_person_id,
            'update_type' => $this->update_type,
            'update_from' => $this->update_from,
        ]);

        $query->andFilterWhere(['like', 'update_file', $this->update_file])
            ->andFilterWhere(['like', 'error_file', $this->error_file])
            ->andFilterWhere(['like', 'update_person_name', $this->update_person_name]);

        return $dataProvider;
    }
}
