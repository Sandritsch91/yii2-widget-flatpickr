<?php

namespace sandritsch91\yii2\flatpickr;

use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\FormatConverter;
use yii\helpers\Html;
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
     * @var string|boolean|AssetBundle class of custom css AssetBundle. Set to false if not wanted
     */
    public string|bool|AssetBundle $customAssetBundle = '';


    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        if (!array_key_exists('autocomplete', $this->options)) {
            $this->options['autocomplete'] = 'off';
        }
        if ($this->customAssetBundle === '') {
            $this->customAssetBundle = str_replace('widgets\\', '', static::class . 'CustomAsset');
        }
    }

    /**
     * {@inheritDoc}
     * @throws InvalidConfigException
     */
    public function run()
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
    protected function registerPlugin(?string $pluginName = null, ?string $selector = null): void
    {
        $view = $this->view;
        $id = $this->options['id'];


        // register JS
        FlatpickrJsAsset::register($view);
        Flatpickrl10nAsset::register($view);

        // register Css
        if ($this->theme !== '') {
            // flatpickr plugin theme
            $langUrl = Yii::$app->assetManager->publish('@npm/flatpickr/dist/themes/' . $this->theme . '.css');
            $view->registerCssFile($langUrl[1], ['depends' => FlatpickrJsAsset::class]);
        } elseif ($this->customAssetBundle) {
            // own theme
            $view->registerAssetBundle($this->customAssetBundle);
        } else {
            // flatpickr default theme
            FlatpickrCssAsset::register($view);
        }


        if (empty($pluginName)) {
            $pluginName = strtolower(StringHelper::basename(static::class));
        }
        if (empty($selector)) {
            $selector = "#$id";
        }

        $options = empty($this->clientOptions) ? '' : Json::htmlEncode($this->clientOptions);
        $js = "$pluginName('$selector', $options);";
        $view->registerJs($js);


        $this->registerClientEvents($selector);
    }

    /**
     * Registers JS event handlers that are listed in [[clientEvents]].
     *
     * @param string|null $selector optional javascript selector for the plugin initialization. Defaults to widget id.
     */
    protected function registerClientEvents(?string $selector = null): void
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
     * @throws InvalidConfigException|Exception
     */
    protected function getClientOptions(): array
    {
        $dateFormat = ArrayHelper::remove($this->clientOptions, 'dateFormat', FormatConverter::convertDateIcuToPhp(Yii::$app->formatter->dateFormat));
        $allowInput = ArrayHelper::remove($this->clientOptions, 'allowInput', true);
        $time_24hr = ArrayHelper::remove($this->clientOptions, 'time_24hr', true);

        $value = $this->value;
        try {
            if ($this->model && $this->model->{$this->attribute}) {
                $value = Yii::$app->formatter->asDate($this->model->{$this->attribute}, 'php:' . $dateFormat);
            }
        } catch (Exception $e) {
        }

        return ArrayHelper::merge($this->clientOptions, [
            'defaultDate' => $value,
            'locale' => $this->locale,
            'dateFormat' => $dateFormat,
            'allowInput' => $allowInput,
            'time_24hr' => $time_24hr,
        ]);
    }
}
