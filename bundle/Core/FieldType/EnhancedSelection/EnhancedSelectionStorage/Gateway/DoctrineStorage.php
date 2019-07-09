<?php

declare(strict_types=1);

namespace Netgen\Bundle\EnhancedSelectionBundle\Core\FieldType\EnhancedSelection\EnhancedSelectionStorage\Gateway;

use Doctrine\DBAL\Connection;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use Netgen\Bundle\EnhancedSelectionBundle\Core\FieldType\EnhancedSelection\EnhancedSelectionStorage\Gateway;
use PDO;

class DoctrineStorage extends Gateway
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function storeFieldData(VersionInfo $versionInfo, Field $field): void
    {
        foreach ($field->value->externalData as $identifier) {
            $insertQuery = $this->connection->createQueryBuilder();
            $insertQuery
                ->insert($this->connection->quoteIdentifier('sckenhancedselection'))
                ->values(
                    [
                        'contentobject_attribute_id' => ':contentobject_attribute_id',
                        'contentobject_attribute_version' => ':contentobject_attribute_version',
                        'identifier' => ':identifier',
                    ]
                )
                ->setParameter(':contentobject_attribute_id', $field->id, PDO::PARAM_INT)
                ->setParameter(':contentobject_attribute_version', $versionInfo->versionNo, PDO::PARAM_INT)
                ->setParameter(':identifier', $identifier, PDO::PARAM_STR);

            $insertQuery->execute();
        }
    }

    public function getFieldData(VersionInfo $versionInfo, Field $field): void
    {
        $field->value->externalData = $this->loadFieldData($field->id, $versionInfo->versionNo);
    }

    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete($this->connection->quoteIdentifier('sckenhancedselection'))
            ->where(
                $query->expr()->andX(
                    $query->expr()->in('contentobject_attribute_id', [':contentobject_attribute_id']),
                    $query->expr()->eq('contentobject_attribute_version', ':contentobject_attribute_version')
                )
            )
            ->setParameter(':contentobject_attribute_id', $fieldIds, Connection::PARAM_INT_ARRAY)
            ->setParameter(':contentobject_attribute_version', $versionInfo->versionNo, PDO::PARAM_INT);

        $query->execute();
    }

    /**
     * Returns the data for the given $fieldId and $versionNo.
     *
     * @param mixed $fieldId
     * @param mixed $versionNo
     *
     * @return array
     */
    protected function loadFieldData($fieldId, $versionNo): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('DISTINCT identifier')
            ->from($this->connection->quoteIdentifier('sckenhancedselection'))
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('contentobject_attribute_id', ':contentobject_attribute_id'),
                    $query->expr()->eq('contentobject_attribute_version', ':contentobject_attribute_version')
                )
            )
            ->setParameter(':contentobject_attribute_id', $fieldId, PDO::PARAM_INT)
            ->setParameter(':contentobject_attribute_version', $versionNo, PDO::PARAM_INT);

        $statement = $query->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            static function (array $row) {
                return $row['identifier'];
            },
            $rows
        );
    }
}
