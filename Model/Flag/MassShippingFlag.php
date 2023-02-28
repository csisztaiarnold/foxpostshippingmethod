<?php
/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Oander\FoxPostShippingMethod\Model\Flag;

class MassShippingFlag
{
    /**
     * @var bool $flag
     */
    private bool $flag;

    /**
     * @return bool
     */
    public function getFlag(): bool
    {
        return (bool)$this->flag;
    }

    /**
     * @param  bool $flag
     * @return void
     */
    public function setFlag(bool $flag): void
    {
        $this->flag = $flag;
    }
}
