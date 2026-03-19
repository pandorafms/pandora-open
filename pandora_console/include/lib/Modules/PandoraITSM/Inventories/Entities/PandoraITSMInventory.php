<?php

namespace PandoraFMS\Modules\PandoraITSM\Inventories\Entities;

use PandoraFMS\Modules\Shared\Entities\Entity;
use PandoraFMS\Modules\Shared\Validators\Validator;

/**
 * @OA\Schema(
 *   schema="PandoraITSMInventory",
 *   type="object",
 *   @OA\Property(
 *     property="idPandoraITSMInventory",
 *     type="integer",
 *     nullable=false,
 *     description="Id Agent pandoraITSMInventory",
 *     readOnly=true
 *   ),
 *   @OA\Property(
 *      property="agentAlias",
 *      type="string",
 *      nullable=true,
 *      default=null,
 *      description="Agent Name pandoraITSMInventory"
 *   ),
 *   @OA\Property(
 *      property="osVersion",
 *      type="string",
 *      nullable=true,
 *      default=null,
 *      description="Agent os version pandoraITSMInventory"
 *   ),
 *   @OA\Property(
 *      property="agentAddress",
 *      type="string",
 *      nullable=true,
 *      default=null,
 *      description="Agent address pandoraITSMInventory"
 *   ),
 *   @OA\Property(
 *      property="agentUrlAddress",
 *      type="string",
 *      nullable=true,
 *      default=null,
 *      description="Agent url address pandoraITSMInventory"
 *   ),
 *   @OA\Property(
 *      property="agentDisabled",
 *      type="boolean",
 *      nullable=true,
 *      default=null,
 *      description="Agent disable pandoraITSMInventory"
 *   ),
 *   @OA\Property(
 *      property="groupName",
 *      type="string",
 *      nullable=true,
 *      default=null,
 *      description="Agent group name pandoraITSMInventory"
 *   ),
 *   @OA\Property(
 *      property="groupId",
 *      type="integer",
 *      nullable=true,
 *      default=null,
 *      description="Agent group id pandoraITSMInventory"
 *   ),
 *   @OA\Property(
 *      property="osName",
 *      type="string",
 *      nullable=true,
 *      default=null,
 *      description="Agent OS name pandoraITSMInventory"
 *   )
 * )
 *
 * @OA\Response(
 *   response="ResponsePandoraITSMInventory",
 *   description="PandoraITSMInventory object",
 *   content={
 *     @OA\MediaType(
 *       mediaType="application/json",
 *       @OA\Schema(
 *         type="object",
 *         ref="#/components/schemas/PandoraITSMInventory",
 *         description="PandoraITSMInventory object"
 *       ),
 *     )
 *   }
 * )
 *
 * @OA\Parameter(
 *   parameter="parameterIdPandoraITSMInventory",
 *   name="idPandoraITSMInventory",
 *   in="path",
 *   description="PandoraITSMInventory id",
 *   required=true,
 *   @OA\Schema(
 *     type="integer",
 *     default=1
 *   )
 * )
 *
 * @OA\RequestBody(
 *   request="requestBodyPandoraITSMInventory",
 *   required=true,
 *   @OA\MediaType(
 *     mediaType="application/json",
 *     @OA\Schema(ref="#/components/schemas/PandoraITSMInventory")
 *   )
 * )
 */
final class PandoraITSMInventory extends Entity
{

    private ?int $idPandoraITSMInventory = null;

    private ?string $agentAlias = null;

    private ?string $osVersion = null;

    private ?string $agentAddress = null;

    private ?string $agentUrlAddress = null;

    private ?bool $agentDisabled = null;

    private ?string $groupName = null;

    private ?int $groupId = null;

    private ?string $osName = null;


    public function __construct()
    {
    }


    public function fieldsReadOnly(): array
    {
        return [];
    }


    public function jsonSerialize(): mixed
    {
        return [
            'idPandoraITSMInventory' => $this->getIdPandoraITSMInventory(),
            'agentAlias'             => $this->getAgentAlias(),
            'osVersion'              => $this->getOsVersion(),
            'agentAddress'           => $this->getAgentAddress(),
            'agentUrlAddress'        => $this->getAgentUrlAddress(),
            'agentDisabled'          => $this->getAgentDisabled(),
            'groupName'              => $this->getGroupName(),
            'groupId'                => $this->getGroupId(),
            'osName'                 => $this->getOsName(),
        ];
    }


    public function getValidations(): array
    {
        return [
            'idPandoraITSMInventory' => [
                Validator::INTEGER,
                Validator::GREATERTHAN,
            ],
            'agentAlias'             => Validator::STRING,
            'osVersion'              => Validator::STRING,
            'agentAddress'           => Validator::STRING,
            'agentUrlAddress'        => Validator::STRING,
            'agentDisabled'          => Validator::BOOLEAN,
            'groupName'              => Validator::STRING,
            'groupId'                => [
                Validator::INTEGER,
                Validator::GREATERTHAN,
            ],
            'osName'                 => Validator::STRING,
        ];
    }


    public function validateFields(array $filters): array
    {
        return (new Validator())->validate($filters);
    }


    public function getIdPandoraITSMInventory(): ?int
    {
        return $this->idPandoraITSMInventory;
    }


    public function setIdPandoraITSMInventory(?int $idPandoraITSMInventory): self
    {
        $this->idPandoraITSMInventory = $idPandoraITSMInventory;
        return $this;
    }


    public function getAgentAlias(): ?string
    {
        return $this->agentAlias;
    }


    public function setAgentAlias(?string $agentAlias): self
    {
        $this->agentAlias = $agentAlias;
        return $this;
    }


    public function getOsVersion(): ?string
    {
        return $this->osVersion;
    }


    public function setOsVersion(?string $osVersion): self
    {
        $this->osVersion = $osVersion;
        return $this;
    }


    public function getAgentAddress(): ?string
    {
        return $this->agentAddress;
    }


    public function setAgentAddress(?string $agentAddress): self
    {
        $this->agentAddress = $agentAddress;
        return $this;
    }


    public function getAgentUrlAddress(): ?string
    {
        return $this->agentUrlAddress;
    }


    public function setAgentUrlAddress(?string $agentUrlAddress): self
    {
        $this->agentUrlAddress = $agentUrlAddress;
        return $this;
    }


    public function getGroupName(): ?string
    {
        return $this->groupName;
    }


    public function setGroupName(?string $groupName): self
    {
        $this->groupName = $groupName;
        return $this;
    }


    public function getOsName(): ?string
    {
        return $this->osName;
    }


    public function setOsName(?string $osName): self
    {
        $this->osName = $osName;
        return $this;
    }


    public function getGroupId(): ?int
    {
        return $this->groupId;
    }


    public function setGroupId(?int $groupId): self
    {
        $this->groupId = $groupId;
        return $this;
    }


    public function getAgentDisabled(): ?bool
    {
        return $this->agentDisabled;
    }


    public function setAgentDisabled(?bool $agentDisabled): self
    {
        $this->agentDisabled = $agentDisabled;
        return $this;
    }


}
