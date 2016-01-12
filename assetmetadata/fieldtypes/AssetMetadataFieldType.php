<?php
namespace Craft;

class AssetMetadataFieldType extends BaseFieldType
{
        // Public Methods
        // =========================================================================

        /**
         * Returns the component's name.
         *
         * @return string
         */
        public function getName()
        {
                return Craft::t('Asset Metadata');
        }

        /**
         * Returns the field's content attribute config.
         *
         * @return mixed
         */
        public function defineContentAttribute()
        {
                return array(AttributeType::Mixed, 'column' => ColumnType::Text);
        }

        /**
         * Returns the component's settings HTML.
         *
         * @return string|null
         */
        public function getSettingsHtml()
        {
                $properties = $this->getSettings()->properties;

                if (!$properties)
                {
                        $properties = array(array('name' => '', 'handle' => '', 'defaultValue' => ''));
                }

                return craft()->templates->render('assetmetadata/fieldtypes/assetmetadata/settings', array(
                        'settings' => $this->getSettings(),
                        'properties' => $properties
                ));
        }

        /**
         * Returns the field's input HTML.
         *
         * @param string $name
         * @param mixed  $value
         *
         * @return string
         */
        public function getInputHtml($name, $values)
        {
                $properties = $this->getSettings()->properties;

                craft()->templates->includeCssResource('assetmetadata/assetmetadata.css');

                return craft()->templates->render('assetmetadata/fieldtypes/assetmetadata/input', array(
                        'name'       => $name,
                        'properties' => $properties,
                        'values'     => $values
                ));
        }

        /**
         * Prepares the field's value for use.
         *
         * @param mixed $values
         *
         * @return mixed
         */
        public function prepValue($values)
        {
                if (is_array($values) && ($properties = $this->getSettings()->properties))
                {
                        // Make properties available via their handles
                        foreach ($values as $id => $value)
                        {
                                if (isset($properties[$id]))
                                {
                                        $values[$properties[$id]['handle']] = $value;
                                }
                        }

                        return $values;
                }
        }

        /**
         * Performs any additional actions after the element has been saved.
         *
         * @return null
         */
        public function onAfterElementSave()
        {
                $properties = $this->getSettings()->properties;
                $defaultValues = $this->_getDefaultValues($properties);

                $isNewElement = $this->_isNewElement($this->element);

                if ($isNewElement || (isset($this->element->refreshMetadata) && $this->element->refreshMetadata === true))
                {
                        $fieldHandle = $this->model->handle;

                        if ($this->element->getContent()->getAttribute($fieldHandle) !== $defaultValues)
                        {
                                $this->element->getContent()->setAttribute($fieldHandle, $defaultValues);

                                $success = craft()->elements->saveElement($this->element);

                                if (!$success)
                                {
                                        Craft::log('(Asset Metadata) Couldn’t save the element “'.$this->element->title.'”', LogLevel::Error);
                                }
                        }
                }
        }

        // Protected Methods
        // =========================================================================

        /**
         * Defines the settings.
         *
         * @return array
         */
        protected function defineSettings()
        {
                return array(
                        'properties' => array(AttributeType::Mixed, 'default' => array()),
                        'customMetadataVariable' => array(AttributeType::Bool, 'default' => false),
                        'metadataVariable' => array(AttributeType::String),
                );
        }

        // Private Methods
        // =========================================================================

        /**
         * Returns the default values for metadata properties.
         *
         * Gets an asset's metadata object and assigns each property configured in the field settings
         * its value by rendering the property's default value Twig.
         *
         * @param mixed $properties
         *
         * @return array
         */
        private function _getDefaultValues($properties)
        {
                $oldPath = craft()->path->getTemplatesPath();
                craft()->path->setTemplatesPath(craft()->path->getSiteTemplatesPath());

                if ($this->getSettings()->customMetadataVariable)
                {
                        $twig = $this->getSettings()->metadataVariable;
                        $twig .= '{{ metadata|json_encode|raw }}';

                        $metadata = craft()->templates->renderString($twig, array('object' => $this->element));
                        $metadata = json_decode($metadata, true);
                }
                else
                {
                        $metadata = craft()->assetMetadata->getAssetMetadata($this->element);
                }

                $defaultValues = array();

                foreach ($properties as $id => $property)
                {
                        $variables = array('object' => $this->element, 'metadata' => $metadata);

                        $defaultValues[$id] = craft()->templates->renderString($property['defaultValue'], $variables);
                }

                craft()->path->setTemplatesPath($oldPath);

                return $defaultValues;
        }

        /**
         * Returns whether this is a new element.
         *
         * @param mixed $element
         * @param mixed $tolerance
         *
         * @return bool
         */
        private function _isNewElement($element, $tolerance = 2)
        {
                if (!$element->dateCreated)
                {
                        return true;
                }

                $now = new DateTime();
                $diff = $now->getTimestamp() - $element->dateCreated->getTimestamp();

                return $diff <= $tolerance;
        }
}
