<?php
$xpdo_meta_map['ufUserFile']= array (
  'package' => 'uploadify',
  'version' => '1.1',
  'table' => 'uploadify_files',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'uid' => 0,
    'timestamp' => NULL,
    'path' => '',
    'url' => '',
    'file' => '',
    'size' => 0,
    'ip' => '0.0.0.0',
    'host' => NULL,
  ),
  'fieldMeta' => 
  array (
    'uid' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'attributes' => 'unsigned',
      'null' => false,
      'default' => 0,
    ),
    'timestamp' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => false,
    ),
    'path' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'url' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'file' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '50',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'size' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'ip' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '16',
      'phptype' => 'string',
      'null' => false,
      'default' => '0.0.0.0',
    ),
    'host' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '50',
      'phptype' => 'string',
      'null' => true,
    ),
  ),
  'indexes' => 
  array (
    'uid' => 
    array (
      'alias' => 'uid',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'uid' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'timestamp' => 
    array (
      'alias' => 'timestamp',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'timestamp' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'path' => 
    array (
      'alias' => 'path',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'path' => 
        array (
          'length' => '50',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'host' => 
    array (
      'alias' => 'host',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'host' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
  'aggregates' => 
  array (
    'User' => 
    array (
      'class' => 'modUser',
      'local' => 'uid',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
