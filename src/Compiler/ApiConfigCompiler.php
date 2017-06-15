<?php

namespace MattJanssen\ApiResponseBundle\Compiler;

use MattJanssen\ApiResponseBundle\Annotation\ApiResponse;
use MattJanssen\ApiResponseBundle\Model\ApiConfig;
use MattJanssen\ApiResponseBundle\Model\ApiConfigInterface;
use MattJanssen\ApiResponseBundle\Model\ApiPathConfig;
use Symfony\Component\HttpFoundation\Request;

/**
 * API Config Compiler
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class ApiConfigCompiler
{
    /**
     * Default Config
     *
     * @var ApiConfig
     */
    private $defaultConfig;

    /**
     * API Path Configurations
     *
     * Each relative URL path has its own CORS configuration settings in this array.
     *
     * @var ApiPathConfig[]
     */
    private $pathConfigs;

    /**
     * Constructor
     *
     * @param array $defaultConfigArray
     * @param array[] $pathConfigArrays
     */
    public function __construct(
        array $defaultConfigArray,
        array $pathConfigArrays
    ) {
        $defaultConfig = $this->generateApiConfig($defaultConfigArray);

        $pathConfigs = [];
        foreach ($pathConfigArrays as $configArray) {
            $pathConfigs[] = $this->generateApiPathConfig($configArray);
        }

        $this->defaultConfig = $defaultConfig;
        $this->pathConfigs = $pathConfigs;
    }

    /**
     * Generate an API Config for this Request
     *
     * Based on the following, with highest priority first:
     * 1) @ApiResponse() annotation.
     * 2) Matched path config (config.yml).
     * 3) Default config (config.yml).
     *
     * @param Request $request
     *
     * @return ApiConfigInterface
     */
    public function compileApiConfig(Request $request)
    {
        // This boolean is flipped only if the request matches a path as specified in config.yml,
        // or if its controller action has the @ApiResponse() annotation.
        $pathServed = false;

        // Start with a copy of the default config.
        $compiledConfig = clone $this->defaultConfig;

        // Try to match the request origin to a path in the config.yml.
        $originPath = $request->getPathInfo();
        foreach ($this->pathConfigs as $pathConfig) {
            $pathRegex = $pathConfig->getPattern();
            if ($pathRegex !== null && !preg_match('#' . str_replace('#', '\#', $pathRegex) . '#', $originPath)) {
                // No path match.
                continue;
            }

            $pathPrefix = $pathConfig->getPrefix();
            if ($pathPrefix !== null && strpos($originPath, $pathPrefix) !== 0) {
                // No path match.
                continue;
            }

            // Merge any path-specified configs over the defaults.
            $pathServed = true;
            $this->mergeConfig($compiledConfig, $pathConfig);

            // After the first path match, don't process the rest.
            break;
        }

        /** @var ApiResponse $attribute */
        $attribute = $request->attributes->get('_' . ApiResponse::ALIAS_NAME);

        // Check if the matching controller action has an @ApiResponse annotation.
        if (null !== $attribute) {
            $pathServed = true;

            // Merge any annotation-specified configs over the defaults.
            $this->mergeConfig($compiledConfig, $attribute);
        }

        if (!$pathServed) {
            // If there was neither a path match nor an @ApiResponse annotation, then don't handle an API response.
            return null;
        }

        return $compiledConfig;
    }

    /**
     * Merge Non-null Options from a Config into Another Config
     *
     * @param ApiConfig $compiledConfig
     * @param ApiConfigInterface $configToMerge
     */
    private function mergeConfig(ApiConfig $compiledConfig, ApiConfigInterface $configToMerge)
    {
        if (null !== $configToMerge->getSerializer()) {
            $compiledConfig->setSerializer($configToMerge->getSerializer());
        }
        if (null !== $configToMerge->getGroups()) {
            $compiledConfig->setGroups($configToMerge->getGroups());
        }
        if (null !== $configToMerge->getCorsAllowOriginRegex()) {
            $compiledConfig->setCorsAllowOriginRegex($configToMerge->getCorsAllowOriginRegex());
        }
        if (null !== $configToMerge->getCorsAllowHeaders()) {
            $compiledConfig->setCorsAllowHeaders($configToMerge->getCorsAllowHeaders());
        }
        if (null !== $configToMerge->getCorsMaxAge()) {
            $compiledConfig->setCorsMaxAge($configToMerge->getCorsMaxAge());
        }
    }

    /**
     * @param array $configArray
     *
     * @return ApiConfig $this
     */
    private function generateApiConfig(array $configArray)
    {
        $apiConfig = new ApiConfig();

        $this->applyApiConfig($configArray, $apiConfig);

        return $apiConfig;
    }

    /**
     * @param array $configArray
     *
     * @return ApiConfig $this
     */
    private function generateApiPathConfig(array $configArray)
    {
        $apiPathConfig = new ApiPathConfig();

        $this->applyApiConfig($configArray, $apiPathConfig);

        // @TODO Use PHP 7 null coalescing operator.
        $apiPathConfig->setPattern(isset($configArray['pattern']) ? $configArray['pattern'] : null);
        $apiPathConfig->setPrefix(isset($configArray['prefix']) ? $configArray['prefix'] : null);

        return $apiPathConfig;
    }

    /**
     * @param array $configArray
     * @param ApiConfig $apiConfig
     */
    private function applyApiConfig(array $configArray, ApiConfig $apiConfig)
    {
        // @TODO Use PHP 7 null coalescing operator.
        $apiConfig
            ->setSerializer(isset($configArray['serializer']) ? $configArray['serializer'] : null)
            ->setGroups(isset($configArray['serialize_groups']) && is_array($configArray['serialize_groups']) ? $configArray['serialize_groups'] : null)
            ->setCorsAllowHeaders(isset($configArray['cors_allow_headers']) ? $configArray['cors_allow_headers'] : null)
            ->setCorsAllowOriginRegex(isset($configArray['cors_allow_origin_regex']) ? $configArray['cors_allow_origin_regex'] : null)
            ->setCorsMaxAge(isset($configArray['cors_max_age']) ? $configArray['cors_max_age'] : null)
            ;
    }
}
