<?php

namespace carlcs\assetmetadata\gql;

use carlcs\assetmetadata\fields\AssetMetadata as AssetMetadataField;
use Craft;
use craft\gql\base\GeneratorInterface;
use craft\gql\GqlEntityRegistry;
use GraphQL\Type\Definition\Type;

class AssetMetadataTypeGenerator implements GeneratorInterface
{
    public static function generateTypes(mixed $context = null): array
    {
        /** @var AssetMetadataField $context */
        $typeName = self::getName($context);

        $contentFields = [];

        foreach ($context->subfields as $columnDefinition) {
            $contentFields[$columnDefinition['handle']] = Type::string();
        }

        $contentFields = Craft::$app->getGql()->prepareFieldDefinitions($contentFields, $typeName);

        $tableRowType = GqlEntityRegistry::getEntity($typeName) ?: GqlEntityRegistry::createEntity($typeName, new AssetMetadataType([
            'name' => $typeName,
            'fields' => function() use ($contentFields) {
                return $contentFields;
            }
        ]));

        return [$tableRowType];
    }

    public static function getName($context = null): string
    {
        /** @var AssetMetadataField $context */
        return $context->handle . '_Subfields';
    }
}
