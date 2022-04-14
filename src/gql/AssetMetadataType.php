<?php

namespace carlcs\assetmetadata\gql;

use craft\gql\base\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

class AssetMetadataType extends ObjectType
{
    protected function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): mixed
    {
        $fieldName = $resolveInfo->fieldName;

        return $source[$fieldName];
    }
}
