<?php

namespace Netgen\Bundle\EzFormsBundle\Form\Type;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use Symfony\Component\Form\FormBuilderInterface;
use Netgen\Bundle\EzFormsBundle\Form\DataWrapper;
use RuntimeException;

/**
 * Class UpdateContentType
 *
 * @package Netgen\EzFormsBundle\Form\Type
 */
class UpdateContentType extends AbstractContentType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "ezforms_update_content";
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm( FormBuilderInterface $builder, array $options )
    {
        /** @var $dataWrapper \Netgen\Bundle\EzFormsBundle\Form\DataWrapper */
        $dataWrapper = $options["data"];

        if ( !$dataWrapper instanceof DataWrapper )
        {
            throw new RuntimeException(
                "Data must be an instance of Netgen\\EzFormsBundle\\Form\\DataWrapper"
            );
        }

        $content = $dataWrapper->target;

        if ( !$content instanceof Content )
        {
            throw new RuntimeException(
                "Data payload must be an instance of eZ\\Publish\\API\\Repository\\Values\\Content\\Content"
            );
        }

        $contentUpdateStruct = $dataWrapper->payload;

        if ( !$contentUpdateStruct instanceof ContentUpdateStruct )
        {
            throw new RuntimeException(
                "Data payload must be an instance of eZ\\Publish\\API\\Repository\\Values\\Content\\ContentUpdateStruct"
            );
        }

        $contentType = $dataWrapper->definition;

        if ( !$contentType instanceof ContentType )
        {
            throw new RuntimeException(
                "Data definition must be an instance of eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType"
            );
        }

        if ( $content->contentInfo->contentTypeId !== $contentType->id )
        {
            throw new RuntimeException(
                "Data definition (ContentType) does not correspond to the data target (Content)"
            );
        }

        $builder->setDataMapper( $this->dataMapper );

        foreach ( $contentType->getFieldDefinitions() as $fieldDefinition )
        {
            // Users can't be created through Content, if ezuser field is found we simply skip it
            if ( $fieldDefinition->fieldTypeIdentifier === "ezuser" )
            {
                continue;
            }

            $languageCode = $contentUpdateStruct->initialLanguageCode;
            $handler = $this->fieldTypeHandlerRegistry->get( $fieldDefinition->fieldTypeIdentifier );

            $handler->buildFieldUpdateForm( $builder, $fieldDefinition, $content, $languageCode );
        }

        // Intentionally omitting submit buttons, set them manually as needed
    }
}
