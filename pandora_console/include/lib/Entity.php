<?php
/**
 * Pandora FMS OpenSource
 * Copyright (c) 2004-2025 Pandora FMS Community
 * https://pandorafms.org
 *
 * Este programa es software libre; puedes redistribuirlo y/o modificarlo bajo
 * los términos de la Licencia Pública General de GNU publicada por la Free
 * Software Foundation para la versión 2. Este programa se distribuye con la
 * esperanza de que sea útil, pero SIN NINGUNA GARANTÍA; ni siquiera con la
 * garantía implícita de COMERCIABILIDAD o IDONEIDAD PARA UN PROPÓSITO
 * PARTICULAR. Consulta la Licencia Pública General de GNU para más detalles.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation for version 2. This program is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without any implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
 * Public License for more details.
 *
 * Эта программа является свободным программным обеспечением; вы можете
 * распространять и/или изменять её в соответствии с условиями Стандартной
 * общественной лицензии GNU (GPL), опубликованной Фондом свободного
 * программного обеспечения (Free Software Foundation) для версии 2. Эта
 * программа распространяется в надежде, что она будет полезной, НО БЕЗ
 * КАКИХ-ЛИБО ГАРАНТИЙ, даже без подразумеваемой гарантии КОММЕРЧЕСКОЙ
 * ПРИГОДНОСТИ или ПРИГОДНОСТИ ДЛЯ КОНКРЕТНОЙ ЦЕЛИ. Подробнее см. Стандартную
 * общественную лицензию GNU.
 *
 * Ce programme est un logiciel libre ; vous pouvez le redistribuer et/ou le
 * modifier selon les termes de la Licence Publique Générale GNU, publiée par
 * la Free Software Foundation pour la version 2. Ce programme est distribué
 * dans l'espoir qu'il sera utile, mais SANS AUCUNE GARANTIE, même sans la
 * garantie implicite de QUALITÉ MARCHANDE ou D'ADÉQUATION À UN USAGE
 * PARTICULIER. Consultez la Licence Publique Générale GNU pour plus de détails.
 *
 * このプログラムはフリーソフトウェアです。GNU一般公衆利用許諾書
 * （Free Software Foundationによって公開されたバージョン2）の条件の下で、
 * 自由に再配布および改変することができます。本プログラムは有用であることを
 * 願って配布されますが、いかなる保証もありません。商品性や特定目的への適合性の
 * 保証も含まれません。詳しくはGNU一般公衆利用許諾書をご覧ください。
 * ============================================================================
 */


// Begin.
namespace PandoraFMS;


/**
 * Defines common methods for all PandoraFMS entity objects.
 */
abstract class Entity
{

    /**
     * Load from DB or new one.
     *
     * @var boolean
     */
    protected $existsInDB;

    /**
     * Fields to identify register.
     *
     * @var array
     */
    protected $primaryKeys;

    /**
     * Entity fields (from table).
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Target table.
     *
     * @var string
     */
    protected $table = '';

    /**
     * MC Node id.
     *
     * @var integer|null
     */
    protected $nodeId = null;

    /**
     * Connected to external node.
     *
     * @var boolean
     */
    private $connected = false;


    /**
     * Instances a new object using array definition.
     *
     * @param array  $data      Fields data.
     * @param string $class_str Class name.
     *
     * @return object With current definition.
     */
    public static function build(array $data=[], string $class_str=__CLASS__)
    {
        $obj = new $class_str();
        // Set values.
        foreach ($data as $k => $v) {
            $obj->{$k}($v);
        }

        $obj->existsInDB = true;
        return $obj;
    }


    /**
     * Duplicates an object.
     *
     * @param array  $field_exceptions Fields to skip.
     * @param string $class_str        Class name.
     *
     * @return object
     */
    public function duplicate(
        array $field_exceptions=[],
        string $class_str=__CLASS__
    ) {
        $keys = array_keys($this->toArray());

        $new = new $class_str();
        foreach ($keys as $k) {
            if (in_array($k, $field_exceptions) === false) {
                $new->$k($this->$k());
            }
        }

        return $new;

    }


    /**
     * Defines a generic constructor to extract information of the object.
     *
     * @param string      $table   Table.
     * @param array|null  $filters Filters, for instance ['id' => $id].
     * @param string|null $trash   Deprecated
     * @param boolean     $cache   Use cache or not.
     *
     * @throws \Exception On error.
     */
    public function __construct(
        string $table,
        ?array $filters=null,
        ?string $trash=null,
        bool $cache=true
    ) {
        if (empty($table) === true) {
            throw new \Exception(
                get_class($this).' error, table name is not defined'
            );
        }

        $this->table = $table;

        if (is_array($filters) === true) {
            // New one.
            $this->primaryKeys = array_keys($filters);

            $data = \db_get_row_filter(
                $this->table,
                $filters,
                false,
                'AND',
                false,
                $cache
            );

            if ($data === false) {
                throw new \Exception(
                    get_class($this).' error, entity not found'
                );
            }

            // Map fields.
            foreach ($data as $k => $v) {
                $this->fields[$k] = $v;
            }

            // Mark as existing object.
            $this->existsInDB = true;
        } else {
            // Empty one.
            $data = \db_get_all_rows_sql(
                sprintf(
                    'SHOW COLUMNS FROM %s',
                    $this->table
                )
            );

            foreach ($data as $row) {
                $this->fields[$row['Field']] = null;
            }

            // Mark as virtual object.
            $this->existsInDB = false;
        }

        
    }


    /**
     * Dynamically call methods in this object.
     *
     * @param string $methodName Name of target method or attribute.
     * @param array  $params     Arguments for target method.
     *
     * @return mixed Return of method.
     * @throws \Exception On error.
     */
    public function __call(string $methodName, ?array $params=null)
    {
        if (method_exists($this, $methodName) === false) {
            if (array_key_exists($methodName, $this->fields) === true) {
                if (empty($params) === true) {
                    return $this->fields[$methodName];
                } else {
                    $this->fields[$methodName] = $params[0];
                }

                return null;
            }

            throw new \Exception(
                get_class($this).' error, method '.$methodName.' does not exist'
            );
        }

        // Do not return nor throw exceptions after this point, allow php
        // default __call behaviour to continue working with object method
        // defined.
        // If you're receiving NULL as result of the method invocation, ensure
        // it is not private, take in mind this method will mask any access
        // level error or notification since it is public and has limited access
        // to the object (public|protected).
    }


    /**
     * Returns current object as array.
     *
     * @return array Of fields.
     */
    public function toArray()
    {
        return $this->fields;
    }


    /**
     * Saves current object definition to database.
     *
     * @return boolean Success or not.
     * @throws \Exception On error.
     */
    public function save()
    {
        $updates = $this->fields;
        // Clean null fields.
        foreach ($updates as $k => $v) {
            if ($v === null) {
                unset($updates[$k]);
            }
        }

        if ($this->existsInDB === true) {
            // Update.
            $where = [];

            foreach ($this->primaryKeys as $key) {
                $where[$key] = $this->fields[$key];
            }

            if (empty($where) === true) {
                throw new \Exception(
                    __METHOD__.' error: Cannot identify object'
                );
            }

            $rs = \db_process_sql_update(
                $this->table,
                $updates,
                $where
            );

            if ($rs === false) {
                global $config;
                throw new \Exception(
                    __METHOD__.' error: '.$config['dbconnection']->error
                );
            }
        } else {
            // New register.
            $rs = \db_process_sql_insert(
                $this->table,
                $updates
            );

            if ($rs === false) {
                global $config;

                throw new \Exception(
                    __METHOD__.' error: '.$config['dbconnection']->error
                );
            }

            $this->existsInDB = true;
        }

        return true;

    }


    /**
     * Remove this entity.
     *
     * @return void
     * @throws \Exception If no primary keys are defined.
     */
    public function delete()
    {
        if ($this->existsInDB === true) {
            $where = [];

            foreach ($this->primaryKeys as $key) {
                $where[$key] = $this->fields[$key];
            }

            if (empty($where) === true) {
                throw new \Exception(
                    __METHOD__.' error: Cannot identify object on deletion'
                );
            }

            \db_process_sql_delete(
                $this->table,
                $where
            );
        }
    }


}
