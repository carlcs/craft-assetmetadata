<?php

namespace carlcs\assetmetadata\gql;

use carlcs\assetmetadata\fields\AssetMetadata as AssetMetadataField;
use craft\gql\base\GeneratorInterface;
use craft\gql\GqlEntityRegistry;
use craft\gql\TypeManager;
use GraphQL\Type\Definition\Type;

class AssetMetadataTypeGenerator implements GeneratorInterface
{
    /**
     * @inheritdoc
     */
    public static function generateTypes($context = null): array
    {
        /** @var AssetMetadataField $context */
        $typeName = self::getName($context);

        $contentFields = [];

        foreach ($context->subfields as $columnKey => $columnDefinition) {
            $contentFields[$columnDefinition['handle']] = Type::string();
        }

        $contentFields = TypeManager::prepareFieldDefinitions($contentFields, $typeName);

        $tableRowType = GqlEntityRegistry::getEntity($typeName) ?: GqlEntityRegistry::createEntity($typeName, new AssetMetadataType([
            'name' => $typeName,
            'fields' => function() use ($contentFields) {
                return $contentFields;
            }
        ]));

        return [$tableRowType];
    }

    /**
     * @inheritdoc
     */
    public static function getName($context = null): string
    {
        /** @var AssetMetadataField $context */
        return $context->handle . '_Subfields';
    }
}
