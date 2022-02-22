<?php

namespace sandritsch91\yii2\flatpickr;

class Flatpickrl10nAsset extends \yii\web\AssetBundle
{
    /**
     * {@inheritdoc}
     */
    public $sourcePath = '@npm/flatpickr/dist/l10n';

    /**
     * {@inheritDoc}
     */
    public $js = [
        'de.js',
        'fr.js',
        'it.js',
    ];

    /**
     * {@inheritdoc}
     */
    public $publishOptions = [
        'forceCopy' => YII_DEBUG
    ];
}
