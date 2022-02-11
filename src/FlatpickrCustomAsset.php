<?php

namespace sandritsch91\yii2\flatpickr;

class FlatpickrCustomAsset extends \yii\web\AssetBundle
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
