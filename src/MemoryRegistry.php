<?php
namespace Core\Registry;

final class MemoryRegistry implements RegistryInterface
{
    private $data;

    private $separator;

    public function __construct(array $arr = [], string $separator = ".")
    {
        $this->data = $arr;
        $this->separator = $separator;
    }

    public function create() : RegistryInterface
    {
        return new self([], $this->separator);
    }

    public function isEmpty(): bool
    {
        return count($this->data) == 0;
    }

    public function export(): array
    {
        return $this->data;
    }

    public function remove(string $key, bool $collapseEmpty = false) : RegistryInterface
    {
        $this->data = $this->delete(explode($this->separator(), $key), $this->data, $collapseEmpty);
        return $this;
    }

    public function extract(string $key, $default = null)
    {
        if (($edge =& $this->getEdge($key)) === null) {
            return $default;
        }
        return $edge;
    }

    public function store(string $key, $val) : RegistryInterface
    {
        $edge =& $this->getEdge($key, []);
        $edge = $val;
        return $this;
    }

    public function append(string $key, $val) : RegistryInterface
    {
        $edge =& $this->getEdge($key, []);
        if (!is_array($edge)) {
            throw new \UnexpectedValueException("key refers to a value that is not an array");
        }
        $edge[] = $val;
        return $this;
    }

    public function isExists(string $key) : bool
    {
        $parent =& $this->data;
        if (($path = explode($this->separator(), $key)) > 1) {
            $key = array_pop($path);
            foreach ($path as $s) {
                if (!isset($parent[$s]) || !is_array($parent[$s])) {
                    return false;
                }
                $parent =& $parent[$s];
            }
        }
        return isset($parent[$key]);
    }

    private function separator()
    {
        if (mb_strlen($this->separator) > 1) {
            throw new \RuntimeException("separator=`{$this->separator}` is invalid");
        }
        return $this->separator;
    }

    private function &getEdge(string $key, $createIfNotExists = null)
    {
        $null = null;
        $parent =& $this->data;
        if (($path = explode($this->separator(), $key)) > 1) {
            $key = array_pop($path);
            foreach ($path as $s) {
                if (!isset($parent[$s])) {
                    if ($createIfNotExists === null) {
                        return $null;
                    }
                    $parent[$s] = [];
                } elseif (!is_array($parent[$s])) {
                    throw new \OutOfBoundsException();
                }
                $parent =& $parent[$s];
            }
        }
        if (!isset($parent[$key])) {
            if ($createIfNotExists === null) {
                return $null;
            }
            $parent[$key] = $createIfNotExists;
        }
        return $parent[$key];
    }

    private function delete(array $path, &$data, bool $collapseEmpty)
    {
        $res = [];
        $cc = array_shift($path);
        foreach ($data as $k => $v) {
            if ($k != $cc) {
                $res[$k] = $data[$k];
                continue;
            }
            if (count($path) > 0) {
                if (!empty($r = $this->delete($path, $data[$k], $collapseEmpty)) || !$collapseEmpty) {
                    $res[$k] = $r;
                }
            }
        }
        return $res;
    }
}
