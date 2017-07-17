<?php

namespace Gedmo\Translatable\Hydrator\ORM;

use Gedmo\Translatable\TranslatableListener;
use Gedmo\Exception\RuntimeException;
use Doctrine\ORM\Internal\Hydration\ObjectHydrator as BaseObjectHydrator;

/**
 * If query uses TranslationQueryWalker and is hydrating
 * objects - when it requires this custom object hydrator
 * in order to skip onLoad event from triggering retranslation
 * of the fields
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class ObjectHydrator extends BaseObjectHydrator
{

    private $savedSkipOnLoad;

    /**
     * {@inheritdoc}
     */
    protected function prepare()
    {
        $listener = $this->getTranslatableListener();
        $this->savedSkipOnLoad = $listener->isSkipOnLoad();
        $listener->setSkipOnLoad(true);
        parent::prepare();
    }

    /**
     * {@inheritdoc}
     */
    protected function cleanup()
    {
        parent::cleanup();
        $listener = $this->getTranslatableListener();
        $listener->setSkipOnLoad($this->savedSkipOnLoad !== null ? $this->savedSkipOnLoad : false);
    }

    /**
     * Get the currently used TranslatableListener
     *
     * @throws \Gedmo\Exception\RuntimeException - if listener is not found
     * @return TranslatableListener
     */
    protected function getTranslatableListener()
    {
        foreach ($this->_em->getEventManager()->getListeners() as $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof TranslatableListener) {
                    return $listener;
                }
            }
        }
        throw new RuntimeException('The translation listener could not be found');
    }
}
