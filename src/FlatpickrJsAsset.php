<?php

namespace sandritsch91\yii2\flatpickr;

use yii\web\AssetBundle;

class FlatpickrJsAsset extends AssetBundle
{
    /**
     * {@inheritdoc}
     */
    public $sourcePath = '@npm/flatpickr/dist';

    /**
     * {@inheritDoc}
     */
    public $js = [
        'flatpickr.js',
        'plugins/confirmDate/confirmDate.js',
        'plugins/labelPlugin/labelPlugin.js',
        'plugins/monthSelect/index.js',
        'plugins/weekSelect/weekSelect.js',
        'plugins/minMaxTimePlugin.js',
        'plugins/momentPlugin.js',
        'plugins/rangePlugin.js',
        'plugins/scrollPlugin.js',
    ];

    /**
     * {@inheritDoc}
     */
    public $css = [
        'plugins/confirmDate/confirmDate.css',
        'plugins/monthSelect/style.css',
    ];

    /**
     * {@inheritdoc}
     */
    public $publishOptions = [
        'forceCopy' => YII_DEBUG
    ];
}
