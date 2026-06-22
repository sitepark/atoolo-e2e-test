<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Service;

class RuntimeStatus
{
    /**
     * @var array<string, CheckStatus>
     */
    private array $status = [];

    public function addStatus(RuntimeType $type, CheckStatus $status): void
    {
        $this->status[$type->value] = $status;
    }

    public function getStatus(RuntimeType $type): ?CheckStatus
    {
        return $this->status[$type->value] ?? null;
    }

    /**
     * @return array<RuntimeType>
     */
    public function getTypes(): array
    {
        return array_map(
            function (string $type) {
                return RuntimeType::from($type);
            },
            array_keys($this->status),
        );
    }

    public function isSuccess(): bool
    {
        foreach ($this->status as $status) {
            if (!$status->success) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array<string>
     */
    public function getMessages(): array
    {
        $messages = [];
        foreach ($this->status as $type => $status) {
            foreach ($status->getMessages() as $scope => $scopeMessages) {
                foreach ($scopeMessages as $message) {
                    $prefix = $type === $scope ? $type : $type . '/' . $scope;
                    $messages[] = sprintf('%s: %s', $prefix, $message);
                }
            }
        }
        return $messages;
    }

    /**
     * @return RuntimeStatusData $data
     */
    public function serialize(): array
    {
        $data = [];
        foreach ($this->status as $type => $status) {
            $data[$type] = $status->serialize();
        }
        $data['success'] = $this->isSuccess();
        $messages = $this->getMessages();
        if (!empty($messages)) {
            $data['messages'] = $messages;
        }
        /** @var RuntimeStatusData $data */
        return $data;
    }

    /**
     * @param RuntimeStatusData $data
     */
    public static function deserialize(array $data): RuntimeStatus
    {
        $runtimeStatus = new RuntimeStatus();
        foreach (RuntimeType::cases() as $type) {
            if (!array_key_exists($type->value, $data)) {
                continue;
            }
            $status = CheckStatus::deserialize($data[$type->value]);
            $runtimeStatus->addStatus($type, $status);
        }
        return $runtimeStatus;
    }
}
