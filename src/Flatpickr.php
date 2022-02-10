<?php

namespace sandritsch91\yii2\flatpickr;

use ReflectionClass;
use ReflectionException;
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
     * @var string
     */
    public string $lang = 'de';
    /**
     * @var array the options for the underlying JS plugin.
     */
    public array $clientOptions = [];
    /**
     * @var array the event handlers for the underlying JS plugin.
     */
    public array $clientEvents = [];

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException|ReflectionException
     */
    public function init()
    {
        parent::init();

        if (!isset($this->lang)) {
            $this->lang = substr(Yii::$app->language, 0, 2);
        }

        $this->registerTranslations();
    }

    /**
     * {@inheritDoc}
     */
    public function run(): string
    {
        $this->clientOptions = $this->getClientOptions();

        $selector = null;
        $this->registerPlugin('dateDropper', $selector);

        return ($this->hasModel())
            ? Html::activeInput('text', $this->model, $this->attribute, $this->options)
            : Html::input('text', $this->name, $this->value, $this->options);
    }

    /**
     * Init translations
     */
    public function registerTranslations()
    {
        $reflector = new ReflectionClass(static::class);
        $dir = rtrim(dirname($reflector->getFileName()), '\\/');
        $dir = rtrim(preg_replace('#widgets$#', '', $dir), '\\/') . DIRECTORY_SEPARATOR . 'messages';
        $category = str_replace(StringHelper::basename(static::class), '', static::class);
        $category = rtrim(str_replace(['\\', 'yii2/', 'widgets', 'models'], ['/', ''], $category), '/') . '*';

        if (!is_dir($dir)) {
            return;
        }

        Yii::$app->i18n->translations[$category] = [
            'class' => 'yii\i18n\GettextMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => $dir,
            'forceTranslation' => true
        ];
    }

    /**
     * Registers a specific plugin and the related events
     *
     * @param string|null $pluginName optional plugin name
     * @param string|null $selector optional javascript selector for the plugin initialization. Defaults to widget id.
     */
    protected function registerPlugin(string $pluginName = null, string $selector = null)
    {
        $view = $this->view;
        $id = $this->options['id'];

        $className = static::class;
        $assetClassName = str_replace('widgets\\', '', $className . "Asset");
        if (empty($pluginName)) {
            $pluginName = strtolower(StringHelper::basename($className));
        }
        if (empty($selector)) {
            $selector = "#$id";
        }
        if (class_exists($assetClassName)) {
            /**
             * @var AssetBundle $assetClassName
             */
            $assetClassName::register($view);
        }

        if ($this->clientOptions !== false) {
            $options = empty($this->clientOptions) ? '' : Json::htmlEncode($this->clientOptions);
            $js = "jQuery('$selector').$pluginName($options);";
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
     *
     * @return array
     */
    protected function getClientOptions(): array
    {
        $format = ArrayHelper::remove($this->clientOptions, 'format', FormatConverter::convertDateIcuToPhp(Yii::$app->formatter->dateFormat));

        return ArrayHelper::merge($this->clientOptions, [
            'format' => $format,
            'lang' => $this->lang
        ]);
    }
}
