<?php
/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Oander\FoxPostShippingMethod\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Oander\FoxPostShippingMethod\Enum\Attributes;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tables = [
            $installer->getTable('quote_address'),
            $installer->getTable('sales_order_address'),
            $installer->getTable('quote'),
            $installer->getTable('sales_order'),
        ];
        $connection = $installer->getConnection();
        foreach ($tables as $table) {
            $connection->addColumn(
                $table, Attributes::FOXPOST_DATA_ATTRIBUTES, [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'foxpost Data',
                ]
            );

        }
        $installer->endSetup();
    }
}
