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
class Agent extends Entity
{

    /**
     * Agent's modules.
     *
     * @var array
     */
    private $modules = [];

    /**
     * Flag to verify if modules has been loaded.
     *
     * @var boolean
     */
    private $modulesLoaded = false;


    /**
     * Builds a PandoraFMS\Agent object from a agent id.
     *
     * @param integer $id_agent     Agent Id.
     * @param boolean $load_modules Load all modules of this agent.
     */
    public function __construct(
        ?int $id_agent=null,
        ?bool $load_modules=false
    ) {
        $table = 'tagente';
        $filter = ['id_agente' => $id_agent];

        if (is_numeric($id_agent) === true
            && $id_agent > 0
        ) {
            parent::__construct(
                $table,
                $filter,
                ''
            );
            if ($load_modules === true) {
                $rows = \db_get_all_rows_filter(
                    'tagente_modulo',
                    $filter
                );

                if (is_array($rows) === true) {
                    foreach ($rows as $row) {
                        $this->modules[] = Module::build($row);
                    }
                }

                $this->modulesLoaded = true;
            }
        } else {
            // Create empty skel.
            parent::__construct($table, null, '');

            // New agent has no modules.
            $this->modulesLoaded = true;
        }

        // Customize certain fields.
        $this->fields['group'] = new Group($this->fields['id_grupo']);

    }


    /**
     * Return last value (status) of the agent.
     *
     * @param boolean $force Force recalculation.
     *
     * @return integer Status of the agent.
     */
    public function lastStatus(bool $force=false)
    {
        if ($force === true) {
            return \agents_get_status(
                $this->id_agente()
            );
        }

        return \agents_get_status_from_counts(
            $this->toArray()
        );

    }


    /**
     * Return last value (status) of the agent.
     *
     * @return integer Status of the agent.
     */
    public function lastValue()
    {
        return $this->lastStatus();
    }


    /**
     * Overrides Entity method.
     *
     * @param integer $id_group Target group Id.
     *
     * @return integer|null Group Id or null.
     */
    public function id_grupo(?int $id_group=null)
    {
        if ($id_group === null) {
            return $this->fields['id_grupo'];
        } else {
            $this->fields['id_grupo'] = $id_group;
            $this->fields['group'] = new Group($this->fields['id_grupo']);
        }
    }


    /**
     * Saves current definition to database.
     *
     * @param boolean $alias_as_name Use alias as agent name.
     *
     * @return mixed Affected rows of false in case of error.
     * @throws \Exception On error.
     */
    public function save(bool $alias_as_name=false)
    {
        if (empty($this->fields['nombre']) === true) {
            if ($alias_as_name === true
                && (empty($this->fields['alias']) === true)
            ) {
                throw new \Exception(
                    get_class($this).' error, nor "alias" nor "nombre" are set'
                );
            } else {
                // Use alias instead.
                $this->fields['nombre'] = $this->fields['alias'];
            }
        }

        if ($this->fields['id_agente'] > 0) {
            // Agent update.
            $updates = $this->fields;

            // Remove shortcuts from values.
            unset($updates['group']);

            $rs = \db_process_sql_update(
                'tagente',
                $updates,
                ['id_agente' => $this->fields['id_agente']]
            );

            if ($rs === false) {
                global $config;
                throw new \Exception(
                    __METHOD__.' error: '.$config['dbconnection']->error
                );
            }
        } else {
            // Agent creation.
            $updates = $this->fields;

            // Remove shortcuts from values.
            unset($updates['group']);

            // Clean null fields.
            foreach ($updates as $k => $v) {
                if ($v === null) {
                    unset($updates[$k]);
                }
            }

            $rs = \agents_create_agent(
                $updates['nombre'],
                $updates['id_grupo'],
                $updates['intervalo'],
                $updates['direccion'],
                $updates,
                $alias_as_name
            );

            if ($rs === false) {
                global $config;
                $error = $config['dbconnection']->error;
                if (empty($error) === true) {
                    if (empty($updates['intervalo']) === true) {
                        $error = 'Missing agent interval';
                    } else if (empty($updates['id_group']) === true) {
                        $error = 'Missing id_group';
                    } else if (empty($updates['id_group']) === true) {
                        $error = 'Missing id_group';
                    }
                }

                throw new \Exception(
                    __METHOD__.' error: '.$error
                );
            }

            $this->fields['id_agente'] = $rs;
        }

        if ($this->fields['group']->id_grupo() === null) {
            // Customize certain fields.
            $this->fields['group'] = new Group($this->fields['id_grupo']);
        }

        return true;
    }


    /**
     * Creates a module in current agent.
     *
     * @param array $params Module definition (each db field).
     *
     * @return integer Id of new module.
     * @throws \Exception On error.
     */
    public function addModule(array $params)
    {
        $err = __METHOD__.' error: ';

        if (empty($params['nombre']) === true) {
            throw new \Exception(
                $err.' module name is mandatory'
            );
        }

        $params['id_agente'] = $this->fields['id_agente'];

        $id_module = modules_create_agent_module(
            $this->fields['id_agente'],
            $params['nombre'],
            $params
        );

        if ($id_module === false) {
            global $config;
            throw new \Exception(
                $err.$config['dbconnection']->error
            );
        }

        return $id_module;

    }


    /**
     * Alias for field 'nombre'.
     *
     * @param string|null $name Name or empty if get operation.
     *
     * @return string|null Name or empty if set operation.
     */
    public function name(?string $name=null)
    {
        if ($name === null) {
            return $this->nombre();
        }

        $this->nombre($name);
    }


    /**
     * Return a list of interfaces.
     *
     * @param array $filter Filter interfaces by name in array.
     *
     * @return array Of interfaces and modules PandoraFMS\Modules.
     */
    public function getInterfaces(array $filter=[])
    {
        $modules = $this->searchModules(
            ['nombre' => '%ifOperStatus%']
        );

        $interfaces = [];
        foreach ($modules as $module) {
            $matches = [];
            if (preg_match(
                '/^(.*?)_ifOperStatus$/',
                $module->name(),
                $matches
            ) > 0
            ) {
                $interface = $matches[1];
            }

            if (empty($interface) === true) {
                continue;
            }

            if (empty($filter) === false
                && in_array($interface, $filter) !== true
            ) {
                continue;
            }

            $name_filters = [
                'ifOperStatus'  => ['nombre' => $interface.'_ifOperStatus'],
                'ifInOctets'    => ['nombre' => $interface.'_ifInOctets'],
                'ifOutOctets'   => ['nombre' => $interface.'_ifOutOctets'],
                'ifHCInOctets'  => ['nombre' => $interface.'_ifHCInOctets'],
                'ifHCOutOctets' => ['nombre' => $interface.'_ifHCOutOctets'],
            ];

            $ifOperStatus = $this->searchModules(
                $name_filters['ifOperStatus']
            );
            $ifInOctets = $this->searchModules(
                $name_filters['ifInOctets']
            );
            $ifOutOctets = $this->searchModules(
                $name_filters['ifOutOctets']
            );
            $ifHCInOctets = $this->searchModules(
                $name_filters['ifHCInOctets']
            );
            $ifHCOutOctets = $this->searchModules(
                $name_filters['ifHCOutOctets']
            );

            $interfaces[$interface] = [
                'ifOperStatus'  => array_shift($ifOperStatus),
                'ifInOctets'    => array_shift($ifInOctets),
                'ifOutOctets'   => array_shift($ifOutOctets),
                'ifHCInOctets'  => array_shift($ifHCInOctets),
                'ifHCOutOctets' => array_shift($ifHCOutOctets),
            ];
        }

        return $interfaces;
    }


    /**
     * Retrieves status, in and out modules from given interface name.
     *
     * @param string $interface Interface name.
     *
     * @return array|null With status, in and out modules. Null if no iface.
     */
    public function getInterfaceMetrics(string $interface):?array
    {
        $modules = $this->getInterfaces([$interface]);
        if (empty($modules) === true) {
            return null;
        }

        $modules = $modules[$interface];

        $in = null;
        $out = null;
        $status = $modules['ifOperStatus'];

        if (empty($modules['ifHCInOctets']) === false) {
            $in = $modules['ifHCInOctets'];
        } else if (empty($modules['ifInOctets']) === false) {
            $in = $modules['ifInOctets'];
        }

        if (empty($modules['ifHCOutOctets']) === false) {
            $out = $modules['ifHCOutOctets'];
        } else if (empty($modules['ifOutOctets']) === false) {
            $out = $modules['ifOutOctets'];
        }

        return [
            'in'     => $in,
            'out'    => $out,
            'status' => $status,
        ];

    }


    /**
     * Search for modules into this agent.
     *
     * @param array   $filter Filters.
     * @param integer $limit  Limit search results.
     *
     * @return array|Module Of PandoraFMS\Module Modules
     * found or Module found is limit 1.
     */
    public function searchModules(array $filter, int $limit=0)
    {
        $filter['id_agente'] = $this->id_agente();

        if ($this->modulesLoaded === true) {
            // Search in $this->modules.
            $results = [];

            foreach ($this->modules as $module) {
                $found = true;
                foreach ($filter as $field => $value) {
                    if ($module->{$field}() != $value) {
                        $found = false;
                        break;
                    }
                }

                if ($found === true) {
                    $results[] = $module;
                }
            }

            return $results;
        } else {
            // Search in db.
            $return = Module::search($filter, $limit);

            if (is_array($return) === false
                && is_object($return) === false
            ) {
                return [];
            }

            return $return;
        }

    }


    /**
     * Delete agent from db.
     *
     * @return boolean
     */
    public function delete()
    {
        // This function also mark modules for deletion.
        $res = (bool) \agents_delete_agent(
            $this->fields['id_agente']
        );

        if ($res === false) {
            return false;
        }

        // Delete modules.
        if ($this->modules !== null) {
            foreach ($this->modules as $module) {
                $module->delete();
            }
        }

        unset($this->fields);
        unset($this->modules);

        return $res;
    }


}
