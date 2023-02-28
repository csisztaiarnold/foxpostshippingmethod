<?php
/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Oander\FoxPostShippingMethod\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class LabelSizes implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('-- Select an Option --')],
            ['value' => 'A5', 'label' => __('A5')],
            ['value' => 'A6', 'label' => __('A6')],
            ['value' => 'A7', 'label' => __('A7')],
            ['value' => '_85X85', 'label' => __('85x85 mm')]
        ];
    }
}
