<?php
/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Oander\FoxPostShippingMethod\Enum;

final class Attributes
{
    /*for 4 table */
    const FOXPOST_DATA_ATTRIBUTES = "foxpost_data";
    const FOX_POST_SHIPPING_METHOD_CODE = "foxpost_foxpost";

    /*JON ATTRIBUTES*/
    const JSON_ATTRIBUTE_NAME = "name";
    const JSON_ATTRIBUTE_CODE = "code";
    const JSON_ATTRIBUTE_ADDRESS = "address";
    const JSON_ATTRIBUTE_ZIP = "address_zip";
    const JSON_ATTRIBUTE_PHONENUMBER = "phonenumber";

    /* EMAIL VARIABLE */
    const EMAIL_VARIABLE = "fox_post_email";
}
