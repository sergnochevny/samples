<?php

namespace Popuper\Editor\Templates\Elements\Formatters;

use Popuper\Repositories\ObjectItems\SavingItem;

/**
 * Interface FormatterInterface
 *
 * @package Popuper\Editor\Templates\Elements\Formatters
 */
interface FormatterInterface
{
    /**
     * @return array
     */
    public function getTemplateData();

    /**
     * @param array $data
     *
     * @return array
     */
    public function formatCollectedData($data = null);

    /**
     * @param array $data
     *
     * @return SavingItem[]
     */
    public function getSavingData(array $data = []);
}