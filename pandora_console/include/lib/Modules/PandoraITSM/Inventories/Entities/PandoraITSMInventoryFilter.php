<?php

namespace PandoraFMS\Modules\PandoraITSM\Inventories\Entities;

use PandoraFMS\Modules\Shared\Core\FilterAbstract;
use PandoraFMS\Modules\Shared\Validators\Validator;

/**
 * @OA\Schema(
 *   schema="PandoraITSMInventoryFilter",
 *   type="object",
 *   allOf={
 *     @OA\Schema(ref="#/components/schemas/PandoraITSMInventory"),
 *     @OA\Schema(
 *       @OA\Property(
 *         property="idPandoraITSMInventory",
 *         default=null,
 *         readOnly=false
 *       ),
 *       @OA\Property(
 *         property="freeSearch",
 *         type="string",
 *         nullable=true,
 *         default=null,
 *         description="Find word in name field."
 *       )
 *     ),
 *     @OA\Schema(
 *       @OA\Property(
 *         property="multipleSearch",
 *         type="object",
 *         ref="#/components/schemas/multipleSearch",
 *         description="Multiple search object",
 *       )
 *     )
 *   }
 * )
 *
 * @OA\RequestBody(
 *   request="requestBodyPandoraITSMInventoryFilter",
 *   required=true,
 *   @OA\MediaType(
 *     mediaType="application/json",
 *     @OA\Schema(ref="#/components/schemas/PandoraITSMInventoryFilter")
 *   ),
 * )
 */
final class PandoraITSMInventoryFilter extends FilterAbstract
{

    private ?string $freeSearch = null;

    private ?array $multipleSearch = null;


    public function __construct()
    {
        $this->setDefaultFieldOrder('tagente.id_agente');
        $this->setDefaultDirectionOrder($this::ASC);
        $this->setEntityFilter(new PandoraITSMInventory());
    }


    public function fieldsTranslate(): array
    {
        return [
            'idPandoraITSMInventory' => 'tagente.id_agente',
            'agentAlias'             => 'tagente.alias',
            'osVersion'              => 'tagente.os_version',
            'agentAddress'           => 'tagente.direccion',
            'agentUrlAddress'        => 'tagente.url_address',
            'agentDisabled'          => 'tagente.disabled',
            'groupId'                => 'tgrupo.id_grupo',
            'groupName'              => 'tgrupo.nombre',
            'osName'                 => 'tconfig_os.name',
        ];
    }


    public function fieldsReadOnly(): array
    {
        return [];
    }


    public function jsonSerialize(): mixed
    {
        return [
            'freeSearch'     => $this->getFreeSearch(),
            'multipleSearch' => $this->getMultipleSearch(),
        ];
    }


    public function getValidations(): array
    {
        $validations = [];
        if ($this->getEntityFilter() !== null) {
            $validations = $this->getEntityFilter()->getValidations();
        }

        $validations['freeSearch'] = Validator::STRING;
        $validations['multipleSearch'] = Validator::ARRAY;
        return $validations;
    }


    public function validateFields(array $filters): array
    {
        return (new Validator())->validate($filters);
    }


    /**
     * Get the value of freeSearch.
     *
     * @return ?string
     */
    public function getFreeSearch(): ?string
    {
        return $this->freeSearch;
    }


    /**
     * Set the value of freeSearch.
     *
     * @param string $freeSearch
     */
    public function setFreeSearch(?string $freeSearch): self
    {
        $this->freeSearch = $freeSearch;
        return $this;
    }


    /**
     * Get the value of fieldsFreeSearch.
     *
     * @return ?array
     */
    public function getFieldsFreeSearch(): ?array
    {
        return [
            'tagente.alias',
            'tagente.id_agente',
        ];
    }


    /**
     * Get the value of multipleSearchString.
     *
     * @return ?array
     */
    public function getMultipleSearch(): ?array
    {
        return $this->multipleSearch;
    }


    /**
     * Set the value of multipleSearchString.
     *
     * @param array $multipleSearch
     */
    public function setMultipleSearch(?array $multipleSearch): self
    {
        $this->multipleSearch = $multipleSearch;
        return $this;
    }


}
