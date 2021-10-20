<?php

namespace Leads\Update\Common;

use Countable;
use Iterator;
use Leads\Update\Actions\ActionInterface;
use Leads\Update\Actions\Throwable\ThrowableInterface;
use Leads\Update\Field\FieldSetInterface;

/**
 * Class ActionsQueue
 *
 * @package Leads\Update\Common
 */
class ActionsQueue implements Iterator, Countable
{
    /**
     * @var \SplPriorityQueue
     */
    protected $_actionsQueue;

    /**
     * @var \SplObjectStorage
     */
    protected $_actionsStorage;

    /**
     * @var int
     */
    protected $_queueOrder = PHP_INT_MAX;

    /**
     * ActionsQueue constructor.
     */
    public function __construct()
    {
        $this->_actionsQueue = new \SplPriorityQueue();
        $this->_actionsStorage = new \SplObjectStorage();

        $this->_actionsQueue->setExtractFlags(\SplPriorityQueue::EXTR_DATA);
    }

    /**
     * @param ActionInterface   $action
     * @param FieldSetInterface $fieldSet
     */
    public function insert(ActionInterface $action, FieldSetInterface $fieldSet)
    {
        $fieldSets = [];

        if ($this->_actionsStorage->contains($action)) {
            $fieldSets = $this->_actionsStorage[$action];
        } else {
            $this->_actionsQueue->insert($action, $this->_getActionPriorityValues($action));
        }

        $fieldSets[] = $fieldSet;

        $this->_actionsStorage->attach($action, $fieldSets);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        $action = $this->_actionsQueue->current();

        return $this->_actionsStorage[$action];
    }

    /**
     *
     */
    public function next()
    {
        $this->_actionsQueue->next();
    }

    /**
     * @return bool|float|int|string|void|null
     */
    public function key()
    {
        return $this->_actionsQueue->current();
    }

    /**
     *
     */
    public function top()
    {
        return $this->_actionsQueue->top();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->_actionsQueue->valid();
    }

    /**
     *
     */
    public function rewind()
    {
        $this->_actionsQueue->rewind();
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->_actionsQueue->count();
    }

    /**
     * @param \Leads\Update\Actions\ActionInterface $action
     *
     * @return array
     */
    protected function _getActionPriorityValues(ActionInterface $action): array
    {
        return [
            // 1st lvl. All ThrowableInterface must be done 1st, NonThrowableInterface - 2nd
            intval($action instanceof ThrowableInterface),

            // 2nd lvl. Custom level set between classes with same parent
            $action->getInnerPriority(),

            // 3d lvl. Order that was used in FieldSet builder on actions adding
            $this->_queueOrder--,
        ];
    }
}