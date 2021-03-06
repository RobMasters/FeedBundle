<?php
/*
 * This file is part of the Eko\FeedBundle Symfony bundle.
 *
 * (c) Vincent Composieux <vincent.composieux@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eko\FeedBundle\Feed;

use Eko\FeedBundle\Formatter\AtomFormatter;
use Eko\FeedBundle\Item\ProxyItem;
use Eko\FeedBundle\Item\RoutedItemInterface;
use Eko\FeedBundle\Formatter\RssFormatter;
use Eko\FeedBundle\Item\Field;
use Eko\FeedBundle\Item\ItemInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Feed
 *
 * This is the main feed class
 *
 * @author Vincent Composieux <vincent.composieux@gmail.com>
 */
class Feed
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var array $config  Configuration settings
     */
    protected $config;

    /**
     * @var array $items  Items of the feed
     */
    protected $items = array();

    /**
     * @var array $fields  Contain Field instances for this formatter
     */
    protected $fields = array();

    /**
     * @param array $config  Configuration settings
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Set the router service
     *
     * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Set or redefine a configuration value
     *
     * @param string $parameter  A configuration parameter name
     * @param mixed  $value      A value
     * @return \Eko\FeedBundle\Feed\Feed
     */
    public function set($parameter, $value)
    {
        $this->config[$parameter] = $value;

        return $this;
    }

    /**
     * Returns config parameter value
     *
     * @param string     $parameter  A configuration parameter name
     * @param mixed|null $default    A default value if not found
     * @return mixed
     */
    public function get($parameter, $default = null)
    {
        return isset($this->config[$parameter]) ? $this->config[$parameter] : $default;
    }

    /**
     * Add an item (an entity which implements ItemInterface instance)
     *
     * @param ItemInterface|RoutedItemInterface $item An entity instance
     * @return \Eko\FeedBundle\Feed\Feed
     * @throws \RuntimeException
     */
    public function add($item)
    {
        if ($item instanceof RoutedItemInterface) {
            $item = new ProxyItem($item, $this->router);
        }

        if (!$item instanceof ItemInterface) {
            throw new \RuntimeException("Invalid item type");
        }

        $this->items[] = $item;

        return $this;
    }

    /**
     * Add items from array
     *
     * @param array|ItemInterface[] $items  Array of items to add to the feed
     * @return \Eko\FeedBundle\Feed\Feed
     */
    public function addFromArray(array $items)
    {
        foreach ($items as $item) {
            $this->add($item);
        }

        return $this;
    }

    /**
     * Set items from array. Note that this method will override any existing items
     *
     * @param array|ItemInterface[] $items  Array of items to set
     * @return \Eko\FeedBundle\Feed\Feed
     */
    public function setItems(array $items)
    {
        $this->items = array();

        return $this->addFromArray($items);
    }

    /**
     * Returns feed items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Add a new field to render
     *
     * @param Field $field  A Field instance
     * @return \Eko\FeedBundle\Feed\Feed
     */
    public function addField(Field $field)
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * Return custom fields which will be added to the feed
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Render the feed in specified format
     *
     * @param string $format  A format (RSS, Atom, ...)
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function render($format)
    {
        switch ($format) {
            case 'rss':
                $formatter = new RssFormatter($this);
                break;

            case 'atom':
                $formatter = new AtomFormatter($this);
                break;

            default:
                throw new \InvalidArgumentException(
                    sprintf("Format '%s' is not available. Please see documentation.", $format)
                );
                break;
        }

        return $formatter->render();
    }
}
