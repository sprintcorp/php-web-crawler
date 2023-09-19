<?php

namespace App;

use Symfony\Component\DomCrawler\Crawler;

require 'vendor/autoload.php';

class Scrape
{
    private array $products = [];
    private const WEBSITE_URL = 'https://www.magpiehq.com/developer-challenge/smartphones';

    public function run(): void
    {
        // Fetch the initial document
        $document = ScrapeHelper::fetchDocument(self::WEBSITE_URL);

        // Get the number of pages
        $numOfPages = $this->getNumOfPages($document);

        // Iterate through the pages of the website
        for ($i = 1; $i <= $numOfPages; $i++) {
            // Fetch the document for the current page
            $pageUrl = self::WEBSITE_URL . '/?page=' . $i;
            $pageDocument = ScrapeHelper::fetchDocument($pageUrl);

            // Filter products on the current page
            $items = $pageDocument->filter('div.product.px-4');

            // For each product, get its details
            foreach ($items as $element) {
                $elementCrawler = new Crawler($element, $pageUrl);
                $productOptions = Product::getProduct($elementCrawler);

                // Check each color variant and add it to the products array if not present
                foreach ($productOptions as $colorOption) {
                    if (!in_array($colorOption, $this->products)) {
                        $this->products[] = $colorOption;
                    }
                }
            }
        }

        file_put_contents('output.json', json_encode($this->products, JSON_PRETTY_PRINT));
    }

    // Method returns the total number of pages
    private function getNumOfPages($document)
    {
        $value = $document->filter('#products')->text();
        preg_match_all('!\d+!', $value, $matches);

        $numOfPages = (int)$matches[0][1];

        return $numOfPages;
    }
}

$scrape = new Scrape();
$scrape->run();
