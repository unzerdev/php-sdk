<?php

namespace UnzerSDK\Apis;

interface ApiConfig
{
    public static function getDomain(): string;
    public static function getTestDomain(): string ;
    public static function getIntegrationDomain(): string;
}