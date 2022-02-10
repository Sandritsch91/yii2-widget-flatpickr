<?php

namespace sandritsch91\yii2\flatpickr;

use yii\web\AssetBundle;

class FlatpickrAsset extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $css = [
        'flatpickr.css'
    ];

    /**
     * {@inheritDoc}
     */
    public $js = [
        'flatpickr.js'
    ];

    /**
     * {@inheritDoc}
     */
    public $depends = [
        'npm-asset/flatpickr'
    ];

    /**
     * {@inheritdoc}
     */
    public $sourcePath = '@vendor/npm-asset/flatpickr/dist';
}
