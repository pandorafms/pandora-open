<?php
// phpcs:disable Squiz.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
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
 * PandoraFMS agent entity.
 */
class Calendar extends Entity
{


    /**
     * Search Calendar.
     *
     * @param array $filter Filters.
     *
     * @return array Rows.
     */
    public static function search(array $filter)
    {
        $table = '`talert_calendar`';
        $rows = \db_get_all_rows_filter(
            $table,
            $filter,
            ['`talert_calendar`.*']
        );

        return $rows;
    }


    /**
     * Builds a PandoraFMS\Calendar object from given id.
     *
     * @param integer $id Id special day.
     */
    public function __construct(?int $id=null)
    {
        $table = 'talert_calendar';
        $filter = ['id' => $id];

        $this->existsInDB = false;

        if (is_numeric($id) === true
            && $id > 0
        ) {
            parent::__construct(
                $table,
                $filter,
                null,
                false
            );
            $this->existsInDB = true;
        } else {
            // Create empty skel.
            parent::__construct($table, null);
        }
    }


    /**
     * Saves current definition to database.
     *
     * @return mixed Affected rows of false in case of error.
     * @throws \Exception On error.
     */
    public function save()
    {
        if ($this->fields['id'] > 0) {
            // Update.
            $updates = $this->fields;

            $rs = \db_process_sql_update(
                $this->table,
                $updates,
                ['id' => $this->fields['id']]
            );

            if ($rs === false) {
                global $config;
                throw new \Exception(
                    __METHOD__.' error: '.$config['dbconnection']->error
                );
            }
        } else {
            // Creation.
            $inserts = $this->fields;

            // Clean null fields.
            foreach ($inserts as $k => $v) {
                if ($v === null) {
                    unset($inserts[$k]);
                }
            }

            $rs = \db_process_sql_insert(
                $this->table,
                $inserts
            );

            if ($rs === false) {
                global $config;
                throw new \Exception(
                    __METHOD__.' error: '.$config['dbconnection']->error
                );
            }

            $this->fields['id'] = $rs;
        }

        return true;
    }


    /**
     * Remove this calendar.
     *
     * @return void
     */
    public function delete()
    {
        if ($this->existsInDB === true) {
            \db_process_delete_temp(
                $this->table,
                'id',
                $this->fields['id']
            );
        }
    }


    /**
     * Returns an array with all calendar filtered.
     *
     * @param array   $fields         Fields array or 'count' keyword to retrieve count.
     * @param array   $filter         Filters to be applied.
     * @param boolean $count          Retrieve count of items instead results.
     * @param integer $offset         Offset (pagination).
     * @param integer $limit          Limit (pagination).
     * @param string  $order          Sort order.
     * @param string  $sort_field     Sort field.
     * @param boolean $select_options Array options for select.
     *
     * @return array With all results.
     * @throws \Exception On error.
     */
    public static function calendars(
        array $fields=[ '`talert_calendar`.*' ],
        array $filter=[],
        bool $count=false,
        ?int $offset=null,
        ?int $limit=null,
        ?string $order=null,
        ?string $sort_field=null,
        ?bool $select_options=false
    ) {
        $sql_filters = [];
        $order_by = '';
        $pagination = '';

        $user_groups = users_get_groups();
        $user_groups_ids = implode(',', array_keys($user_groups));

        if (isset($filter['free_search']) === true
            && empty($filter['free_search']) === false
        ) {
            $sql_filters[] = vsprintf(
                ' AND (`talert_calendar`.`name` like "%%%s%%"
                    OR `talert_calendar`.`description` like "%%%s%%")',
                array_fill(0, 2, $filter['free_search'])
            );
        }

        $sql_filters[] = ' AND id_group IN ('.$user_groups_ids.')';

        if (isset($order) === true) {
            $dir = 'asc';
            if ($order === 'desc') {
                $dir = 'desc';
            };

            if (in_array(
                $sort_field,
                [ 'name' ]
            ) === true
            ) {
                $order_by = sprintf(
                    'ORDER BY `talert_calendar`.`%s` %s',
                    $sort_field,
                    $dir
                );
            } else {
                // Custom field order.
                $order_by = sprintf(
                    'ORDER BY `%s` %s',
                    $sort_field,
                    $dir
                );
            }
        }

        if (isset($limit) === true && $limit > 0
            && isset($offset) === true && $offset >= 0
        ) {
            $pagination = sprintf(
                ' LIMIT %d OFFSET %d ',
                $limit,
                $offset
            );
        }

        $sql = sprintf(
            'SELECT %s
            FROM `talert_calendar`
            WHERE 1=1
            %s
            %s
            %s',
            join(',', $fields),
            join(' ', $sql_filters),
            $order_by,
            $pagination
        );

        if ($count === true) {
            $sql = sprintf('SELECT count(*) as n FROM ( %s ) tt', $sql);

            return ['count' => \db_get_value_sql($sql)];
        }

        $return = \db_get_all_rows_sql($sql);

        if (is_array($return) === false) {
            return [];
        }

        if ($select_options === true) {
            $return = array_reduce(
                $return,
                function ($carry, $item) {
                    $carry[$item['id']] = $item['name'];
                    return $carry;
                }
            );
        }

        return $return;
    }


}
