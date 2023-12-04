<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Collections\ETFCollection;
use App\Collections\ETFHoldingCollection;
use App\Models\ETF;
use App\Models\ETFHolding;
use GuzzleHttp\Client;

class ETFScraperRepository
{
    private Client $client;
    private GoogleSpreadsheetRepository $sheetsRepository;

    public function __construct(GoogleSpreadsheetRepository $sheetsRepository)
    {
        $this->client = new Client();
        $this->sheetsRepository = $sheetsRepository;
    }

    public function scrapeETFData(
        string $url,
        string $spreadsheetID,
        string $range
    ): ETFCollection
    {
        $etfCollection = new ETFCollection();

        $response = $this->client->request('GET', $url);
        $html = $response->getBody()->getContents();

        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        $symbols = $xpath->query('//*[@id="mobile_table_pills"]/div[1]/div/div[1]/table/tbody/tr/td[1]/a');

        foreach ($symbols as $symbol) {
            $etfSymbol = $symbol->nodeValue;
            $symbolHref = $symbol->getAttribute('href');

            $symbolUrl = 'https://etfdb.com' . $symbolHref . '#holdings';

            $etfResponse = $this->client->request('GET', $symbolUrl);
            $etfHTML = $etfResponse->getBody()->getContents();
            $etfDom = new \DOMDocument;
            libxml_use_internal_errors(true);
            $etfDom->loadHTML($etfHTML);
            $etfXpath = new \DOMXPath($etfDom);

            $holdingCollection = new ETFHoldingCollection();

            for ($i = 1; $i <= 3; $i++) {
                $holdingElement = $etfXpath->query("//*[@id=\"etf-holdings\"]/tbody/tr[$i]");

                if ($holdingElement->item(0) !== null) {
                    $holdingSymbol = $holdingElement->item(0)->childNodes->item(1)->nodeValue ?? 'N/A';
                    $holdingName = $holdingElement->item(0)->childNodes->item(3)->nodeValue ?? 'N/A';
                    $holdingPercentage = $holdingElement->item(0)->childNodes->item(5)->nodeValue ?? 'N/A';

                    $holding = new ETFHolding($holdingSymbol, $holdingName, $holdingPercentage);
                    $holdingCollection->add($holding);
                }
            }
            $etf = new ETF($etfSymbol, $symbolUrl, $holdingCollection);
            $etfCollection->add($etf);
        }
        $this->sheetsRepository->saveToSheet($spreadsheetID, $range, $etfCollection);
        return $etfCollection;
    }
}