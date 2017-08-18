<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Intention;

/**
 * IntentionSearch represents the model behind the search form about `backend\models\Intention`.
 */
class IntentionSearch extends Intention
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'frequency_day', 'total_times', 'has_today_task', 'is_special', 'status'], 'integer'],
            [['name', 'des'], 'safe'],
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
        $query = Intention::find();

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
            'frequency_day' => $this->frequency_day,
            'total_times' => $this->total_times,
            'has_today_task' => $this->has_today_task,
            'is_special' => $this->is_special,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'des', $this->des]);

        return $dataProvider;
    }
}
