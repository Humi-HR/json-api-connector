<?php

namespace Humi\JsonApiConnector\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class JsonResourceCollection extends ResourceCollection
{
    protected $page;
    protected $perPage;
    protected $total;

    public function __construct($resource, array $meta = null, bool $trueInstantiation = true)
    {
        $this->collection = collect();

        if ($trueInstantiation) {
            if ($resource->count()) {
                parent::__construct($resource);
            }

            $this->extractMeta($meta);
        }
    }

    protected function extractMeta(array $meta = null): void
    {
        // Paginated Collection (BaseModel)
        if (request()->pagination) {
            $this->page = request()->pagination['page'];
            $this->perPage = request()->pagination['perPage'];
            $this->total = request()->pagination['total'];
            return;
        }

        // Paginated JSON Proxy
        $this->page = (int) data_get($meta, 'page.number', 1);
        $this->perPage = (int) data_get($meta, 'page.size', 0);
        $this->total = (int) data_get($meta, 'page.total_size', 0);

        // Non Pagination Collection
        if (!$this->total && !is_null($this->collection) && $this->collection->count()) {
            $this->total = $this->collection->count();
            $this->perPage = $this->collection->count();
        }
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getTotal(): int
    {
        return $this->total;
    }
}
