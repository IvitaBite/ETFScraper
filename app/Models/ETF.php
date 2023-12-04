<?php

declare(strict_types=1);

namespace App\Models;

use App\Collections\ETFHoldingCollection;

class ETF
{
    private string $etfSymbol;
    private string $etfUrl;
    private ETFHoldingCollection $holdingCollection;

    public function __construct(
        string               $etfSymbol,
        string               $etfUrl,
        ETFHoldingCollection $holdingCollection
    )
    {
        $this->etfSymbol = $etfSymbol;
        $this->etfUrl = $etfUrl;
        $this->holdingCollection = $holdingCollection;
    }

    public function getETFSymbol(): string
    {
        return $this->etfSymbol;
    }

    public function getETFUrl(): string
    {
        return $this->etfUrl;
    }

    public function getHoldingCollection(): ETFHoldingCollection
    {
        return $this->holdingCollection;
    }
}