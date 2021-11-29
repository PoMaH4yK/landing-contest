<?php

/* @var $this yii\web\View */
/* @var \app\utils\JsonRpcDataProvider $provider */

use yii\grid\GridView;
use yii\bootstrap4\LinkPager;

$this->title = 'Admin';
$this->params['breadcrumbs'][] = $this->title;
?>
<?= GridView::widget([
    'dataProvider' => $provider,
    'columns' => [
        'url',
        [
            'attribute' => 'lastVisit',
            'value' => function ($data) {
                return (new DateTime($data['lastVisit']))->format('d.m.Y');
            }
        ],
        'count'
    ],
    'pager' => [
        'class' => LinkPager::class
    ]
]) ?>
