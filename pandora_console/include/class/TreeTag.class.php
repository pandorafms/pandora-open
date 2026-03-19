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
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
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

global $config;

require_once $config['homedir'].'/include/class/Tree.class.php';

class TreeTag extends Tree
{


    public function __construct($type, $rootType='', $id=-1, $rootID=-1, $serverID=false, $childrenMethod='on_demand', $access='AR')
    {
        global $config;

        parent::__construct($type, $rootType, $id, $rootID, $serverID, $childrenMethod, $access);

        $this->L1fieldName = 'id_tag';
        $this->L1fieldNameSql = 'ttm.id_tag';
        $this->L1innerInside = '
            INNER JOIN tagente_modulo tam 
                ON ta.id_agente = tam.id_agente
            INNER JOIN tagente_estado tae
                ON tae.id_agente_modulo = tam.id_agente_modulo
			INNER JOIN ttag_module ttm
				ON ttm.id_agente_modulo = tam.id_agente_modulo
		';
        $this->L1extraFields = [
            'tt.name',
            'tt.id_tag AS id',
        ];
        $this->L1inner = 'INNER JOIN ttag tt ON tt.id_tag = x2.g';
        $this->L1orderByFinal = 'tt.name';

        $this->L2condition = 'AND ttm.id_tag = '.$this->rootID;
        $this->L2inner = 'INNER JOIN ttag_module ttm
        ON ttm.id_agente_modulo = tam.id_agente_modulo';

        $this->L3forceTagCondition = true;
    }


    protected function getData()
    {
        if ($this->id == -1) {
            $this->getFirstLevel();
        } else if ($this->type == 'tag') {
            $this->getSecondLevel();
        } else if ($this->type == 'agent') {
            $this->getThirdLevel();
        }
    }


    protected function getTagJoin()
    {
        return '';
    }


}
