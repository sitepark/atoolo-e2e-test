<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Service;

use InvalidArgumentException;
use JsonException;

class CheckStatus
{
    /**
     * @var array<string, array<string>>
     */
    private array $messages = [];

    /**
     * @var array<string,array<string,mixed>>
     */
    private array $reports = [];

    public function __construct(public bool $success) {}

    public static function createSuccess(): self
    {
        return new self(true);
    }

    public static function createFailure(): self
    {
        return new self(false);
    }

    public function addMessage(string $scope, string $message): self
    {
        return $this->addMessages($scope, [$message]);
    }

    /**
     * @param array<string> $messages
     */
    public function addMessages(string $scope, array $messages): self
    {
        $this->messages[$scope] = array_merge(
            $this->messages[$scope] ?? [],
            $messages,
        );
        return $this;
    }

    /**
     * @param array<string,mixed> $result
     */
    public function addReport(string $scope, array $result): self
    {
        if (isset($this->reports[$scope])) {
            throw new InvalidArgumentException("Scope $scope already exists");
        }
        $this->reports[$scope] = $result;
        return $this;
    }

    /**
     * @param array<string,mixed> $result
     */
    public function replaceReport(string $scope, array $result): self
    {
        $this->reports[$scope] = $result;
        return $this;
    }

    /**
     * @return array<string,mixed>
     */
    public function getReport(string $scope): array
    {
        return $this->reports[$scope] ?? [];
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    public function getReports(): array
    {
        return $this->reports;
    }

    /**
     * @return array<string,array<string>>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function apply(CheckStatus $status): self
    {
        $this->success = $this->success && $status->success;
        $this->messages = array_merge_recursive(
            $this->messages,
            $status->messages,
        );
        foreach ($status->reports as $scope => $result) {
            $this->addReport($scope, $result);
        }
        return $this;
    }

    /**
     * @return CheckStatusData
     */
    public function serialize(): array
    {
        $data = [
            'success' => $this->success,
            'reports' => $this->reports,
        ];
        if (!empty($this->messages)) {
            $data['messages'] = $this->messages;
        }
        return $data;
    }

    /**
     * @param CheckStatusData $data
     * @throws JsonException
     */
    public static function deserialize(array $data): CheckStatus
    {
        if (!isset($data['success'])) {
            $data = [
                'success' => false,
                'messages' => [
                    'deserialize' => [
                        json_encode(
                            $data,
                            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES,
                        ),
                    ],
                ],
            ];
        }
        $success = is_bool($data['success']) && $data['success'];

        $status = new self($success);
        $status->reports = $data['reports'] ?? [];
        $status->messages = $data['messages'] ?? [];
        return $status;
    }
}
