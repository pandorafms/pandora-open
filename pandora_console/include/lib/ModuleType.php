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
class ModuleType extends Entity
{


    /**
     * Builds a PandoraFMS\ModuleType object from given id.
     *
     * @param integer $id_tipo_modulo Id tipo modulo.
     */
    public function __construct(?int $id_tipo_modulo=null)
    {
        if (is_numeric($id_tipo_modulo) === true
            && $id_tipo_modulo > 0
        ) {
            parent::__construct(
                'ttipo_modulo',
                ['id_tipo' => $id_tipo_modulo]
            );
        } else {
            // Create empty skel.
            parent::__construct('ttipo_modulo');
        }
    }


    /**
     * Verifies if module type is local or not.
     * Beware, plugins also use this kind of modules..
     *
     * @return boolean Is a local candidate! or not (false).
     */
    public function is_local_datatype()
    {
        if ((int) $this->id_tipo() === MODULE_TYPE_GENERIC_DATA
            || (int) $this->id_tipo() === MODULE_TYPE_GENERIC_PROC
            || (int) $this->id_tipo() === MODULE_TYPE_GENERIC_DATA_STRING
            || (int) $this->id_tipo() === MODULE_TYPE_GENERIC_DATA_INC
            || (int) $this->id_tipo() === MODULE_TYPE_GENERIC_DATA_INC_ABS
        ) {
            return true;
        }

        return false;
    }


    /**
     * Saves current definition to database.
     *
     * @return void No return.
     * @throws \Exception On error.
     */
    public function save()
    {
        throw new \Exception('Read only component');
    }


    /**
     * Validate id_module and id_module_type pair.
     *
     * @param integer $id_module_type Id module_type.
     * @param integer $id_modulo      Id modulo.
     *
     * @return boolean True success, false if not.
     */
    public static function validate(int $id_module_type, int $id_modulo)
    {
        switch ($id_modulo) {
            case MODULE_PLUGIN:
            case MODULE_PREDICTION:
            case MODULE_DATA:
            case MODULE_WMI:
                if (($id_module_type < 6 || $id_module_type > 18) === false
                    && ($id_module_type < 29 || $id_module_type > 34) === false
                    && ($id_module_type === 25)
                ) {
                    return false;
                }
            break;

            case MODULE_NETWORK:
            case MODULE_SNMP:
                if ($id_module_type < 6 || $id_module_type > 18) {
                    return false;
                }
            break;

            case MODULE_WEB:
                if ($id_module_type !== 25) {
                    return false;
                }
            break;

            case MODULE_WUX:
                if ($id_module_type < 29 || $id_module_type > 34) {
                    return false;
                }
            break;

            default:
            return false;
        }

        return true;
    }


}
