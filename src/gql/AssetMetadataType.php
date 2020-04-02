<?php

namespace carlcs\assetmetadata\gql;

use craft\gql\base\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

class AssetMetadataType extends ObjectType
{
    /**
     * @inheritdoc
     */
    protected function resolve($source, $arguments, $context, ResolveInfo $resolveInfo)
    {
        $fieldName = $resolveInfo->fieldName;

        return $source[$fieldName];
    }
}
