<?php

declare(strict_types=1);

namespace Netgen\Bundle\EnhancedSelectionBundle\Core\FieldType\EnhancedSelection\EnhancedSelectionStorage;

use eZ\Publish\SPI\FieldType\StorageGateway;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

abstract class Gateway extends StorageGateway
{
    /**
     * Stores the identifiers in the database based on the given field data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    abstract public function storeFieldData(VersionInfo $versionInfo, Field $field);

    /**
     * Gets the identifiers stored in the field.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    abstract public function getFieldData(VersionInfo $versionInfo, Field $field);

    /**
     * Deletes field data for all $fieldIds in the version identified by
     * $versionInfo.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds
     */
    abstract public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds);
}
