<?php
declare(strict_types = 1);

namespace Yiisoft\Yii\Bootstrap4;

use Yiisoft\Arrays\ArrayHelper;

/**
 * NavBar renders a navbar HTML component.
 *
 * Any content enclosed between the {@see begin()} and {@see end()} calls of NavBar is treated as the content of the
 * navbar. You may use widgets such as {@see Nav} or {@see \Yiisoft\Widget\Menu} to build up such content. For example,
 *
 * ```php
 *    if ($user->getId() !== null) {
 *        $menuItems = [
 *            [
 *                'label' => 'About',
 *                'url' => '/about',
 *            ],
 *            [
 *                'label' => 'Contact',
 *                'url' => '/contact',
 *            ],
 *            [
 *                'label' => 'Logout' . ' ' . '(' . $user->getUsername() . ')',
 *                'url' => '/logout'
 *            ],
 *        ];
 *    } else {
 *        $menuItems = [
 *            [
 *                'label' => 'About',
 *                'url' => '/about',
 *            ],
 *            [
 *                'label' => 'Contact',
 *                'url' => '/contact',
 *            ],
 *            [
 *                'label' => 'Login',
 *                'url' => '/login',
 *            ],
 *        ];
 *    }
 *
 *    <?php NavBar::begin()
 *        ->brandLabel('My Application Basic')
 *        ->brandUrl('/')
 *        ->options([
 *            'class' => 'navbar navbar-dark bg-dark navbar-expand-lg text-white',
 *        ])
 *        ->init();
 *
 *        echo Nav::widget()
 *            ->currentPath($currentPath)
 *            ->items($menuItems)
 *            ->options([
 *                'class' => 'navbar-nav float-right ml-auto'
 *            ]);
 *
 *        NavBar::end(); ?>
 * ```
 * Note: $currentPath it must be injected from each controller to the main controller.
 *
 * SiteController.php
 *
 * ```php
 *
 *    public function index(ServerRequestInterface $request): ResponseInterface
 *    {
 *        $response = $this->responseFactory->createResponse();
 *        $currentPath = $request->getUri()->getPath();
 *        $output = $this->render('index', ['currentPath' => $currentPath]);
 *        $response->getBody()->write($output);
 *
 *        return $response;
 *    }
 * ```
 *
 * Controller.php
 *
 * ```php
 *    private function renderContent($content, array $parameters = []): string
 *    {
 *        $user = $this->user->getIdentity();
 *        $layout = $this->findLayoutFile($this->layout);
 *
 *        if ($layout !== null) {
 *            return $this->view->renderFile(
 *                $layout,
 *                    [
 *                        'aliases' => $this->aliases,
 *                        'content' => $content,
 *                        'user' => $user,
 *                        'params' => $this->params,
 *                        'currentPath' => !isset($parameters['currentPath']) ?: $parameters['currentPath']
 *                    ],
 *                $this
 *            );
 *        }
 *
 *        return $content;
 *    }
 * ```
 *
 * @property-write array $containerOptions
 *
 */
class NavBar extends Widget
{
    /**
     * @var array the HTML attributes for the container tag. The following special options are recognized:
     *
     * - tag: string, defaults to "div", the name of the container tag.
     *
     * {@see \Yiisoft\Html\Html::renderTagAttributes()} for details on how attributes are being rendered.
     */
    private $collapseOptions = [];

    /**
     * @var string the text of the brand or empty if it's not used. Note that this is not HTML-encoded.
     *
     * {@see https://getbootstrap.com/docs/4.2/components/navbar/}
     */
    private $brandLabel;

    /**
     * @var string src of the brand image or empty if it's not used. Note that this param will override
     *             `$this->brandLabel` param.
     *
     * {@see https://getbootstrap.com/docs/4.2/components/navbar/}
     */
    private $brandImage;

    /**
     * @var string $url the URL for the brand's hyperlink tag and will be used for the "href" attribute of the brand
     *             link. Default value is '/' will be used. You may set it to `null` if you want to have no link at all.
     */
    private $brandUrl = '/';

    /**
     * @var array the HTML attributes of the brand link.
     *
     * {@see \Yiisoft\Html\Html::renderTagAttributes()} for details on how attributes are being rendered.
     */
    private $brandOptions = [];

    /**
     * @var string text to show for screen readers for the button to toggle the navbar.
     */
    private $screenReaderToggleText = 'Toggle navigation';

    /**
     * @var string the toggle button content. Defaults to bootstrap 4 default `<span class="navbar-toggler-icon"></span>`
     */
    private $togglerContent = '<span class="navbar-toggler-icon"></span>';

    /**
     * @var array the HTML attributes of the navbar toggler button.
     *
     * {@see \Yiisoft\Html\Html::renderTagAttributes()} for details on how attributes are being rendered.
     */
    private $togglerOptions = [];

    /**
     * @var bool whether the navbar content should be included in an inner div container which by default adds left and
     *           right padding. Set this to false for a 100% width navbar.
     */
    private $renderInnerContainer = true;

    /**
     * @var array the HTML attributes of the inner container.
     *
     * {@see \Yiisoft\Html\Html::renderTagAttributes()} for details on how attributes are being rendered.
     */
    private $innerContainerOptions = [];

    /**
     * @var bool $clientOptions
     */
    public $clientOptions = false;

    /**
     * @var array the HTML attributes for the widget container tag. The following special options are recognized:
     *
     * - tag: string, defaults to "nav", the name of the container tag.
     *
     * {@see \Yiisoft\Html\Html::renderTagAttributes()} for details on how attributes are being rendered.
     */
    private $options = [];

    /**
     * Initializes the widget.
     *
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        if (!isset($this->options['id'])) {
            $this->options['id'] = "{$this->getId()}-navbar";
        }

        if (!isset($this->options['class']) || empty($this->options['class'])) {
            Html::addCssClass($this->options, ['widget' => 'navbar', 'navbar-expand-lg', 'navbar-light', 'bg-light']);
        } else {
            Html::addCssClass($this->options, ['widget' => 'navbar']);
        }

        $navOptions = $this->options;
        $navTag = ArrayHelper::remove($navOptions, 'tag', 'nav');
        $brand = '';

        if (!isset($this->innerContainerOptions['class'])) {
            Html::addCssClass($this->innerContainerOptions, 'container');
        }

        if (!isset($this->collapseOptions['id'])) {
            $this->collapseOptions['id'] = "{$this->getId()}-collapse";
        }

        if (!empty($this->brandImage)) {
            $this->brandLabel = Html::img($this->brandImage);
        }

        if ($this->brandLabel !== false) {
            Html::addCssClass($this->brandOptions, ['widget' => 'navbar-brand']);
            if (empty($this->brandUrl)) {
                $brand = Html::tag('span', $this->brandLabel, $this->brandOptions);
            } else {
                $brand = Html::a(
                    $this->brandLabel,
                    $this->brandUrl === false ? Yii::$app->homeUrl : $this->brandUrl,
                    $this->brandOptions
                );
            }
        }

        Html::addCssClass($this->collapseOptions, ['collapse' => 'collapse', 'widget' => 'navbar-collapse']);
        $collapseOptions = $this->collapseOptions;
        $collapseTag = ArrayHelper::remove($collapseOptions, 'tag', 'div');

        echo Html::beginTag($navTag, $navOptions) . "\n";

        if ($this->renderInnerContainer) {
            echo Html::beginTag('div', $this->innerContainerOptions)."\n";
        }

        echo $brand . "\n";
        echo $this->renderToggleButton() . "\n";
        echo Html::beginTag($collapseTag, $collapseOptions) . "\n";
    }

    /**
     * Renders the widget.
     */
    public function run(): string
    {
        BootstrapPluginAsset::register($this->getView());

        $tag = ArrayHelper::remove($this->collapseOptions, 'tag', 'div');

        echo Html::endTag($tag) . "\n";

        if ($this->renderInnerContainer) {
            echo Html::endTag('div') . "\n";
        }

        $tag = ArrayHelper::remove($this->options, 'tag', 'nav');

        return Html::endTag($tag);
    }

    /**
     * Renders collapsible toggle button.
     *
     * @return string the rendering toggle button.
     */
    protected function renderToggleButton(): string
    {
        $options = $this->togglerOptions;

        Html::addCssClass($options, ['widget' => 'navbar-toggler']);

        return Html::button(
            $this->togglerContent,
            ArrayHelper::merge($options, [
                'type' => 'button',
                'data' => [
                    'toggle' => 'collapse',
                    'target' => '#' . $this->collapseOptions['id'],
                ],
                'aria-controls' => $this->collapseOptions['id'],
                'aria-expanded' => 'false',
                'aria-label' => $this->screenReaderToggleText,
            ])
        );
    }

    /**
     * {@see collapseOptions}
     *
     * @param array $collapseOptions
     *
     * @return $this
     */
    public function collapseOptions(array $value): self
    {
        $this->collapseOptions = $value;

        return $this;
    }

    /**
     * {@see brandLabel}
     *
     * @param array $brandLabel
     *
     * @return $this
     */
    public function brandLabel(string $value): self
    {
        $this->brandLabel = $value;

        return $this;
    }

    /**
     * {@see brandImage}
     *
     * @param array $brandImage
     *
     * @return $this
     */
    public function brandImage(string $value): self
    {
        $this->brandImage = $value;

        return $this;
    }

    /**
     * {@see brandUrl}
     *
     * @param array $brandUrl
     *
     * @return $this
     */
    public function brandUrl(string $value): self
    {
        $this->brandUrl = $value;

        return $this;
    }

    /**
     * {@see brandOptions}
     *
     * @param array $brandOptions
     *
     * @return $this
     */
    public function brandOptions(array $value): self
    {
        $this->brandOptions = $value;

        return $this;
    }

    /**
     * {@see screenReaderToggleText}
     *
     * @param array $screenReaderToggleText
     *
     * @return $this
     */
    public function screenReaderToggleText(string $value): self
    {
        $this->screenReaderToggleText = $value;

        return $this;
    }

    /**
     * {@see togglerContent}
     *
     * @param array $togglerContent
     *
     * @return $this
     */
    public function togglerContent(string $value): self
    {
        $this->togglerContent = $value;

        return $this;
    }

    /**
     * {@see togglerOptions}
     *
     * @param array $togglerOptions
     *
     * @return $this
     */
    public function togglerOptions(array $value): self
    {
        $this->togglerOptions = $value;

        return $this;
    }

    /**
     * {@see renderInnerContainer}
     *
     * @param array $renderInnerContainer
     *
     * @return $this
     */
    public function renderInnerContainer(bool $value): self
    {
        $this->renderInnerContainer = $value;

        return $this;
    }

    /**
     * {@see innerContainerOptions}
     *
     * @param array $innerContainerOptions
     *
     * @return $this
     */
    public function innerContainerOptions(array $value): self
    {
        $this->innerContainerOptions = $value;

        return $this;
    }

    /**
     * {@see clientOptions}
     *
     * @param array $clientOptions
     *
     * @return $this
     */
    public function clientOptions(bool $value): self
    {
        $this->clientOptions = $value;

        return $this;
    }

    /**
     * {@see options}
     *
     * @param array $options
     *
     * @return $this
     */
    public function options(array $value): self
    {
        $this->options = $value;

        return $this;
    }
}
