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

use PandoraFMS\Entity;
use PandoraFMS\Module;

/**
 * Represents AA and AP modules entity from a cluster.
 */
class ClusterModule extends Entity
{

    /**
     * Associated module.
     *
     * @var PandoraFMS\Module
     */
    private $module;


    /**
     * Builds a PandoraFMS\ClusterViewer\ClusterModule object from a id.
     *
     * @param integer $id ClusterModule Id.
     *
     * @throws \Exception On error.
     */
    public function __construct(?int $id=null)
    {
        if (is_numeric($id) === true && $id > 0) {
            try {
                parent::__construct('tcluster_item', ['id' => $id]);
            } catch (\Exception $e) {
                throw new \Exception('ClusterModule id not found.');
            }

            // Get module.
            $this->module = Module::search(
                [
                    'nombre'           => $this->name(),
                    'custom_integer_1' => $this->id_cluster(),
                ],
                1
            );
        } else {
            parent::__construct('tcluster_item');

            $this->module = new Module();
        }

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
     * Associates a module to this clusterModule.
     *
     * @param array $params Module parameters.
     *
     * @return void
     */
    public function setModule(array $params)
    {
        $this->module = new Module();
        foreach ($params as $k => $v) {
            $this->module->{$k}($v);
        }
    }


    /**
     * Associates a module to this clusterModule.
     *
     * @param PandoraFMS\Module $module Module definition.
     *
     * @return void
     */
    public function setModuleObject(Module $module)
    {
        $this->module = $module;
    }


    /**
     * Returns current module.
     *
     * @return PandoraFMS\Module Object.
     */
    public function getModule()
    {
        return $this->module;
    }


    /**
     * Saves or retrieves value of warning_limit.
     *
     * @param float|null $value Warning value.
     *
     * @return mixed Value or empty.
     */
    public function warning_limit(?float $value=null)
    {
        if ($value !== null) {
            $this->fields['warning_limit'] = $value;
            if ($this->module !== null) {
                $this->module->min_warning($value);
            }
        } else {
            return $this->fields['warning_limit'];
        }
    }


    /**
     * Saves or retrieves value of critical_limit.
     *
     * @param float|null $value Critical value.
     *
     * @return mixed Value or empty.
     */
    public function critical_limit(?float $value=null)
    {
        if ($value !== null) {
            $this->fields['critical_limit'] = $value;
            if ($this->module !== null) {
                $this->module->min_critical($value);
            }
        } else {
            return $this->fields['critical_limit'];
        }
    }


    /**
     * Save ClusterModule.
     *
     * @return boolean True if success, false if error.
     * @throws \Exception On db error.
     */
    public function save()
    {
        $values = $this->fields;

        if ($this->module === null) {
            return false;
        }

        if (method_exists($this->module, 'save') === false) {
            throw new \Exception(
                __METHOD__.' error: Cluster module "'.$this->name().'" invalid.'
            );
        }

        if (isset($values['id']) === true && $values['id'] > 0) {
            // Update.
            $rs = \db_process_sql_update(
                'tcluster_item',
                $values,
                ['id' => $this->fields['id']]
            );

            if ($rs === false) {
                global $config;
                throw new \Exception(
                    __METHOD__.' error: '.$config['dbconnection']->error
                );
            }

            if ($this->module === null) {
                throw new \Exception(
                    __METHOD__.' error: Cluster module "'.$this->name().'" is not defined'
                );
            }

            // Update reference.
            $this->module->custom_integer_2($this->fields['id']);

            // Update module.
            $this->module->save();

            return true;
        } else {
            // New.
            $rs = \db_process_sql_insert(
                'tcluster_item',
                $values
            );

            if ($rs === false) {
                global $config;
                throw new \Exception(
                    __METHOD__.' error: '.$config['dbconnection']->error
                );
            }

            $this->fields['id'] = $rs;

            // Update reference.
            $this->module->custom_integer_2($this->fields['id']);

            // Update module.
            $this->module->save();

            return true;
        }

        return false;
    }


    /**
     * Erases this object and its module.
     *
     * @return void
     */
    public function delete()
    {
        if (method_exists($this->module, 'delete') === true) {
            $this->module->delete();
        }

        unset($this->fields);
        unset($this->module);

    }


}
