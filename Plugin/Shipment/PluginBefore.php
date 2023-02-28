<?php
/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Oander\FoxPostShippingMethod\Plugin\Shipment;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Toolbar\Interceptor;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\AbstractBlock;

class PluginBefore
{
    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @param RequestInterface $request
     * @param UrlInterface     $urlBuilder
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface     $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
    }

    /**
     * @param  Interceptor   $subject
     * @param  AbstractBlock $context
     * @param  ButtonList    $buttonList
     * @return void
     */
    public function beforePushButtons(
        Interceptor   $subject,
        AbstractBlock $context,
        ButtonList    $buttonList
    ): void {
        if ($this->request->getFullActionName() == 'adminhtml_order_shipment_view' && $this->getShipmentId()) {
            $url = $this->urlBuilder->getUrl(
                'foxpost/shipment/send',
                [
                    'shipment_id' => $this->getShipmentId()
                ]
            );
            $buttonList->add(
                'send_to_foxpost',
                [
                    'label' => __('Send to Foxpost'),
                    'onclick' => "setLocation('$url')",
                    'class' => 'reset'
                ],
                -1
            );
        }
    }

    /**
     * @return int
     */
    private function getShipmentId(): int
    {
        return (int)$this->request->getParam('shipment_id', null);
    }
}
