<?php
namespace Core\Registry;

interface RegistryInterface
{
    public function create(array $arr = [], string $separator = ".") : RegistryInterface;

    public function remove(string $key, bool $collapseEmpty = true) : RegistryInterface;

    public function extract(string $key, $default = null);

    public function store(string $key, $val) : RegistryInterface;

    public function append(string $key, $val) : RegistryInterface;

    public function isEmpty(): bool;

    public function isExists(string $key): bool;

    public function export(): array;
}
