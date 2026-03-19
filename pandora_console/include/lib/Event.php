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

global $config;
require_once $config['homedir'].'/include/functions_events.php';

/**
 * PandoraFMS event entity.
 */
class Event extends Entity
{

    /**
     * Agent related to this event.
     *
     * @var \PandoraFMS\Agent
     */
    private $linkedAgent;

    /**
     * Module related to this event.
     *
     * @var \PandoraFMS\Module
     */
    private $linkedModule;


    /**
     * Builds a PandoraFMS\Event object from given event id.
     *
     * @param integer $event_id Event Id.
     */
    public function __construct(?int $event_id=null)
    {
        $this->table = 'tevento';

        if ($event_id === 0) {
            parent::__construct($this->table);
        } else if (is_numeric($event_id) === true) {
            parent::__construct($this->table, ['id_evento' => $event_id]);
        } else {
            // Empty skel.
            parent::__construct($this->table);
        }

        if ($this->id_agente() !== null) {
            $this->linkedAgent = new Agent((int) $this->id_agente());
        }

        if ($this->id_agentmodule() !== null) {
            $this->linkedModule = new Module((int) $this->id_agentmodule());
        }
    }


    /**
     * Get/set linked agent.
     *
     * @param Agent|null $agent New agent to link.
     *
     * @return Agent|null Agent or null if set operation.
     */
    public function agent(?Agent $agent=null) : ?Agent
    {
        if ($agent === null) {
            return $this->linkedAgent;
        }

        $this->linkedAgent = $agent;
        $this->id_agentmodule($agent->id_agentmodule());
    }


    /**
     * Get/set linked agent.
     *
     * @param Module|null $module New module to link.
     *
     * @return Module|null module or null if set operation.
     */
    public function module(?Module $module=null) : ?Module
    {
        if ($module === null) {
            return $this->linkedModule;
        }

        $this->linkedModule = $module;
        $this->id_agentmodule($module->id_agentmodule());
    }


    /**
     * Retrieves all events matching given filters.
     *
     * @param array   $fields     Fields to retrieve.
     * @param array   $filter     Filter.
     * @param integer $offset     Offset.
     * @param integer $limit      Limit.
     * @param string  $order      Order (asc or desc).
     * @param string  $sort_field Sort field.
     * @param boolean $history    Search history.
     * @param boolean $return_sql Return sql or execute it.
     * @param string  $having     Having.
     *
     * @return array Found events or SQL query or error.
     * @throws \Exception On error.
     */
    public static function search(
        array $fields,
        array $filter,
        ?int $offset=null,
        ?int $limit=null,
        ?string $order=null,
        ?string $sort_field=null,
        bool $history=false,
        bool $return_sql=false,
        string $having=''
    ):array {
        $result = \events_get_all(
            $fields,
            $filter,
            $offset,
            $limit,
            $order,
            $sort_field,
            $history,
            $return_sql,
            $having
        );

        // Always return an array.
        if (empty($result) === true) {
            $result = [];
        }

        return $result;
    }


    /**
     * Saves current group definition to database.
     *
     * @return mixed Affected rows of false in case of error.
     * @throws \Exception On error.
     */
    public function save()
    {
        $values = $this->fields;
        // Clean null fields.
        foreach ($values as $k => $v) {
            if ($v === null) {
                unset($values[$k]);
            }
        }

        if ($this->id_evento === null) {
            // New.
            return db_process_sql_insert(
                $this->table,
                $values
            );
        } else if ($this->fields['id_evento'] > 0) {
            // Update.
            return db_process_sql_update(
                $this->table,
                $values,
                ['id_evento' => $values['id_evento']]
            );
        }

        return false;
    }


}
