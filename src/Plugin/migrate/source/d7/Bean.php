<?php

namespace Drupal\d7migrate\Plugin\migrate\source\d7;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drupal 7 Bean source from database.
 *
 * @MigrateSource(
 *   id = "d7_bean",
 *   source_module = "bean"
 * )
 */
class Bean extends FieldableEntity {
    /**
     * The module handler.
     *
     * @var \Drupal\Core\Extension\ModuleHandlerInterface
     */
    protected $moduleHandler;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityManagerInterface $entity_manager, ModuleHandlerInterface $module_handler) {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_manager);
        $this->moduleHandler = $module_handler;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $migration,
            $container->get('state'),
            $container->get('entity.manager'),
            $container->get('module_handler')
        );
    }

    /**
     * The join options between the bean and the bean_revisions table.
     */
    const JOIN = 'b.vid = br.vid';

    /**
     * {@inheritdoc}
     */
    public function query() {
        // Select bean in its last revision.
        $query = $this->select('bean_revision', 'br')
            ->fields('b', [
                'bid',
                'title',
                'label',
                'type',
                'uid',
                'created',
                'changed',
            ])
            ->fields('br', [
                'vid',
                'title',
                'delta',
                'log',
            ]);
        $query->addField('b', 'uid', 'block_uid');
        $query->addField('br', 'uid', 'revision_uid');
        $query->innerJoin('bean', 'b', static::JOIN);


        if (isset($this->configuration['bean_type'])) {
            $query->condition('b.type', $this->configuration['bean_type']);
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareRow(Row $row) {
        // Get Field API field values.
        foreach (array_keys($this->getFields('bean', $row->getSourceProperty('type'))) as $field) {
            $bid = $row->getSourceProperty('bid');
            $vid = $row->getSourceProperty('vid');
            $row->setSourceProperty($field, $this->getFieldValues('bean', $field, $bid, $vid));
        }

        return parent::prepareRow($row);
    }

    /**
     * {@inheritdoc}
     */
    public function fields() {
        $fields = [
            'bid' => $this->t('Block ID'),
            'type' => $this->t('Type'),
            'title' => $this->t('Title'),
            'uid' => $this->t('Authored by (uid)'),
            'revision_uid' => $this->t('Revision authored by (uid)'),
            'created' => $this->t('Created timestamp'),
            'changed' => $this->t('Modified timestamp'),
            'revision' => $this->t('Create new revision')];
        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getIds() {
        $ids['bid']['type'] = 'integer';
        $ids['bid']['alias'] = 'b';
        return $ids;
    }

}
