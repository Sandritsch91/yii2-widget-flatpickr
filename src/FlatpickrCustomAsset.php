<?php

namespace sandritsch91\yii2\flatpickr;

use yii\web\AssetBundle;

class FlatpickrCustomAsset extends AssetBundle
{
    /**
     * {@inheritdoc}
     */
    public $sourcePath = '@sandritsch91/yii2/flatpickr/assets';

    /**
     * {@inheritDoc}
     */
    public $css = [
        'css/custom.css'
    ];

    /**
     * {@inheritdoc}
     */
    public $publishOptions = [
        'forceCopy' => YII_DEBUG
    ];
}
