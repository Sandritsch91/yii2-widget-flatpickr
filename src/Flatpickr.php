<?php

namespace sandritsch91\yii2\flatpickr;

use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\FormatConverter;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\web\AssetBundle;
use yii\widgets\InputWidget;

class Flatpickr extends InputWidget
{
    /**
     * @var string language, empty for en
     */
    public string $locale = 'de';
    /**
     * @var string the theme to use
     */
    public string $theme = '';
    /**
     * @var array the options for the underlying JS plugin.
     */
    public array $clientOptions = [];
    /**
     * @var array the event handlers for the underlying JS plugin.
     */
    public array $clientEvents = [];
    /**
     * @var string|boolean|AssetBundle class of custom css AssetBundle
     */
    public $customAssetBundle = '';


    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (!array_key_exists('autocomplete', $this->options)) {
            $this->options['autocomplete'] = 'off';
        }
        if (false !== $this->customAssetBundle && $this->customAssetBundle === '') {
            $this->customAssetBundle = str_replace('widgets\\', '', static::class . "CustomAsset");
        }
    }

    /**
     * {@inheritDoc}
     * @throws InvalidConfigException
     */
    public function run(): string
    {
        $this->clientOptions = $this->getClientOptions();

        $selector = null;
        $this->registerPlugin('flatpickr', $selector);

        return ($this->hasModel())
            ? Html::activeInput('text', $this->model, $this->attribute, $this->options)
            : Html::input('text', $this->name, $this->value, $this->options);
    }

    /**
     * Registers a specific plugin and the related events
     *
     * @param string|null $pluginName optional plugin name
     * @param string|null $selector optional javascript selector for the plugin initialization. Defaults to widget id.
     * @throws InvalidConfigException
     */
    protected function registerPlugin(string $pluginName = null, string $selector = null)
    {
        $view = $this->view;
        $id = $this->options['id'];


        // register JS
        if ($this->locale !== '') {
            $langUrl = Yii::$app->assetManager->publish('@npm/flatpickr/dist/l10n/' . $this->locale . '.js');
            $view->registerJsFile($langUrl[1], ['depends' => FlatpickrJsAsset::class]);
        }
        else {
            FlatpickrJsAsset::register($view);
        }

        // register Css
        if ($this->theme !== '') {
            // flatpickr plugin theme
            $langUrl = Yii::$app->assetManager->publish('@npm/flatpickr/dist/themes/' . $this->theme . '.css');
            $view->registerCssFile($langUrl[1], ['depends' => FlatpickrJsAsset::class]);
        }
        elseif ($this->customAssetBundle) {
            // own theme
            $this->customAssetBundle::register($view);
        }
        else {
            // flatpickr default theme
            FlatpickrCssAsset::register($view);
        }


        if (empty($pluginName)) {
            $pluginName = strtolower(StringHelper::basename(static::class));
        }
        if (empty($selector)) {
            $selector = "#$id";
        }

        if ($this->clientOptions !== false) {
            $options = empty($this->clientOptions) ? '' : Json::htmlEncode($this->clientOptions);
            $js = "$pluginName('$selector', $options);";
            $view->registerJs($js);
        }

        $this->registerClientEvents($selector);
    }

    /**
     * Registers JS event handlers that are listed in [[clientEvents]].
     *
     * @param string|null $selector optional javascript selector for the plugin initialization. Defaults to widget id.
     */
    protected function registerClientEvents(string $selector = null)
    {
        if (!empty($this->clientEvents)) {
            $id = $this->options['id'];

            if (empty($selector)) {
                $selector = "#$id";
            }

            $js = [];
            foreach ($this->clientEvents as $event => $handler) {
                $js[] = "jQuery('$selector').on('$event', $handler);";
            }
            $this->view->registerJs(implode("\n", $js));
        }
    }

    /**
     * Get client options
     * Set some defaults, if not in options
     *
     * @return array
     */
    protected function getClientOptions(): array
    {
        $dateFormat = ArrayHelper::remove($this->clientOptions, 'dateFormat', FormatConverter::convertDateIcuToPhp(Yii::$app->formatter->dateFormat));
        $allowInput = ArrayHelper::remove($this->clientOptions, 'allowInput', true);
        $time_24hr = ArrayHelper::remove($this->clientOptions, 'time_24hr', true);

        return ArrayHelper::merge($this->clientOptions, [
            'defaultDate' => $this->value,
            'locale' => $this->locale,
            'dateFormat' => $dateFormat,
            'allowInput' => $allowInput,
            'time_24hr' => $time_24hr,
        ]);
    }
}
