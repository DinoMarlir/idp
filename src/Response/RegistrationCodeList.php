<?php

namespace App\Response;

use JMS\Serializer\Annotation as Serializer;

class RegistrationCodeList {

    /**
     * @param string[] $codes
     */
    public function __construct(
        /**
         * List of UUIDs of all registration codes
         * @Serializer\SerializedName("codes")
         * @Serializer\Type("array<string>")
         */
        private array $codes
    )
    {
    }
}