<?php

namespace App;

use Symfony\Component\DomCrawler\Crawler;

require 'vendor/autoload.php';

class Scrape
{
    private array $products = [];

    const  WEBSITE_URL = 'https://www.magpiehq.com/developer-challenge/smartphones';


    public function run(): void
    {
    
        $document = ScrapeHelper::fetchDocument(self::WEBSITE_URL);

        //Get the number of pages
        $numOfPages = $this->totalPages($document);

        // Initialize an empty associative array to store unique product options
        $uniqueProductOptions = [];

        for ($i = 1; $i <= $numOfPages; $i++) {
            $pageUrl = self::WEBSITE_URL . '/?page=' . $i;
            $document = ScrapeHelper::fetchDocument($pageUrl);
            $items = $document->filter(".product");

            foreach ($items as $element) {
                $elementCrawler = new Crawler($element, $pageUrl);
                $productOptions = Product::getProduct($elementCrawler);

                // Merge the product options into the uniqueProductOptions array
                foreach ($productOptions as $colorOption) {
                    $uniqueProductOptions[$colorOption['title']][$colorOption['colour']] = $colorOption;
                }
            }
        }

        // Convert the associative array of unique product options back to a flat array
        $this->products = array_merge(...array_values($uniqueProductOptions));

        // Reset array keys to ensure a sequential array
        $this->products = array_values($this->products);


        file_put_contents('output.json', json_encode($this->products, JSON_PRETTY_PRINT ));
    }

    
    private function totalPages($document) {
        $value = $document->filter('#products')->text();
        preg_match_all('!\d+!', $value, $matches);
        $numOfPages = (int) $matches[0][1];
        return $numOfPages;
    }

}


$scrape = new Scrape();
$scrape->run();
