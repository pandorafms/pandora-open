<?php

namespace PandoraFMS\Modules\PandoraITSM\Inventories\Repositories;

use InvalidArgumentException;
use PandoraFMS\Modules\PandoraITSM\Inventories\Entities\PandoraITSMInventory;
use PandoraFMS\Modules\PandoraITSM\Inventories\Entities\PandoraITSMInventoryFilter;
use PandoraFMS\Modules\Shared\Core\FilterAbstract;
use PandoraFMS\Modules\Shared\Enums\HttpCodesEnum;
use PandoraFMS\Modules\Shared\Exceptions\NotFoundException;
use PandoraFMS\Modules\Shared\Repositories\RepositoryMySQL;
use PandoraFMS\Modules\Shared\Services\Config;

class PandoraITSMInventoryRepositoryMySQL extends RepositoryMySQL implements PandoraITSMInventoryRepository
{


    public function __construct(
        private Config $config
    ) {
    }


    /**
     * @return PandoraITSMInventory[],
     */
    public function list(PandoraITSMInventoryFilter $pandoraITSMInventoryFilter): array
    {
        try {
            $result = $this->getPandoraITSMInventoriesQuery($pandoraITSMInventoryFilter);
        } catch (\Throwable $th) {
            // Capture errors mysql.
            throw new InvalidArgumentException(
                strip_tags($th->getMessage()),
                HttpCodesEnum::INTERNAL_SERVER_ERROR
            );
        }

        if (is_array($result) === false) {
            throw new NotFoundException(__('Pandora itsm inventory not found'));
        }

        return $result;
    }


    public function count(PandoraITSMInventoryFilter $pandoraITSMInventoryFilter): int
    {
        $result = $this->getPandoraITSMInventoriesQuery($pandoraITSMInventoryFilter, true);
        try {
            $count = 0;
            if (empty($result) === false && isset($result[0]) === true) {
                $count = $result[0]['count'];
            }
        } catch (\Throwable $th) {
            // Capture errors mysql.
            throw new InvalidArgumentException(
                strip_tags($th->getMessage()),
                HttpCodesEnum::INTERNAL_SERVER_ERROR
            );
        }

        return (int) $count;
    }


    public function getOne(PandoraITSMInventoryFilter $pandoraITSMInventoryFilter): array
    {
        try {
            $result_array = $this->getPandoraITSMInventoriesQuery($pandoraITSMInventoryFilter);

            $result = [];
            if (empty($result_array) === false) {
                $result = array_shift($result_array);
            }
        } catch (\Throwable $th) {
            // Capture errors mysql.
            throw new InvalidArgumentException(
                strip_tags($th->getMessage()),
                HttpCodesEnum::INTERNAL_SERVER_ERROR
            );
        }

        if (empty($result) === true) {
            throw new NotFoundException(__('Pandora itsm inventory not found'));
        }

        return $result;
    }


    public function create(PandoraITSMInventory $pandoraITSMInventory): PandoraITSMInventory
    {
        return $pandoraITSMInventory;
    }


    public function update(PandoraITSMInventory $pandoraITSMInventory): PandoraITSMInventory
    {
        return $pandoraITSMInventory;
    }


    public function delete(int $id): void
    {
    }


    private function getPandoraITSMInventoriesQuery(
        FilterAbstract $filter,
        bool $count=false
    ): array {
        $pagination = '';
        $orderBy = '';
        $fields = 'COUNT(DISTINCT tagente.id_agente) as count';
        $filters = $this->buildQueryFilters($filter);
        $groupBy = '';

        if ($count === false) {
            $pagination = $this->buildQueryPagination($filter);
            $orderBy = $this->buildQueryOrderBy($filter);
            $groupBy = 'GROUP BY tagente.id_agente';

            $custom_fields = \db_get_all_fields_in_table('tagent_custom_fields');
            if ($custom_fields === false) {
                $custom_fields = [];
            }

            $count_custom_fields = count($custom_fields);
            $custom_field_sql = '';
            $index_name_custom_fields = [];
            foreach ($custom_fields as $key => $field) {
                $index_name_custom_fields[$field['name']] = $field;
                if ($key !== $count_custom_fields) {
                    $custom_field_sql .= ', ';
                }

                $custom_field_sql .= sprintf(
                    'MAX(CASE WHEN tagent_custom_fields.name = "%s" THEN tagent_custom_data.description END) AS "%s"',
                    $field['name'],
                    $field['name']
                );
            }

            $fields = sprintf(
                'tagente.alias,
                tagente.id_agente AS "ID Agent",
                tagente.os_version AS "OS Version",
                tagente.direccion AS "IP Address",
                tagente.url_address AS "URL Address",
                tgrupo.nombre AS "Group",
                tconfig_os.name AS "OS"
                %s',
                $custom_field_sql
            );
        }

        $sql = sprintf(
            'SELECT %s
            FROM tagente
            LEFT JOIN tagent_custom_data
                ON tagent_custom_data.id_agent = tagente.id_agente
            LEFT JOIN tagent_custom_fields
                ON tagent_custom_data.id_field = tagent_custom_fields.id_field
            INNER JOIN tgrupo
                ON tgrupo.id_grupo = tagente.id_grupo
            INNER JOIN tconfig_os
                ON tconfig_os.id_os = tagente.id_os
            LEFT JOIN tagent_secondary_group
                ON tagente.id_agente = tagent_secondary_group.id_agent
            WHERE %s
            %s
            %s
            %s',
            $fields,
            $filters,
            $groupBy,
            $orderBy,
            $pagination
        );

        $data = $this->dbGetAllRowsSql($sql);
        if ($data === false) {
            $data = [];
        }

        $result = [];
        if ($count === false) {
            foreach ($data as $key => $agent_fields) {
                foreach ($agent_fields as $name_field => $value_field) {
                    $type = 'text';
                    if (isset($index_name_custom_fields[$name_field]) === true) {
                        if ($index_name_custom_fields[$name_field]['is_password_type']) {
                            $type = 'password';
                        } else if ($index_name_custom_fields[$name_field]['is_link_enabled']) {
                            $type = 'link';
                        }
                    }

                    $result[$agent_fields['ID Agent']][$name_field] = [
                        'data' => $value_field,
                        'type' => $type,
                    ];
                }
            }
        } else {
            $result = $data;
        }

        return $result;
    }


}
