<?php
declare(strict_types=1);

namespace GreenLinks\EavMySql;

use GreenLinks\Eav\Driver;
use GreenLinks\Eav\Entity;

use Psr\SimpleCache\CacheInterface as SimpleCache;

class MySqlDriver implements Driver
{
    private SimpleCache $cache;

    public function __construct(SimpleCache $simpleCache)
    {
        $this->cache = $simpleCache;
    }

    public function lookup(string $eql): array
    {
        //
    }

    public function store(Entity ...$entities): self
    {
        //

        return $this;
    }
}
