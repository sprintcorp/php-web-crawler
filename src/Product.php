<?php

namespace App;

class Product
{
    
    public static function getProduct($htmlElement) {

        //Get product name from html element
        $productName = $htmlElement->filter('.product-name')->text();  

        //Get product capacity from html element
        $productCapacity = $htmlElement->filter('.product-capacity')->text();

        //Set product title 
        $productTitle = $productName." ".$productCapacity;

        //Convert product capacity from GB to MB
        $capacityInMB = (strpos($productCapacity, 'MB') === false) ? (int)$productCapacity * 1000 
            : $productCapacity;


        

        //Get product price from html element
        $price = $htmlElement -> filter('div.my-8.block.text-center.text-lg') -> text();
        $price = filter_var($price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);


        //Get product image from html element and remove backslashes from code
        $imageUrl = stripslashes($htmlElement->filter('.my-8.mx-auto')->image()->getUri());

        //Get product availability from html text
        $productAvailability = $htmlElement->filter('div.my-4.text-sm.block.text-center');
        $productAvailabilityText = $productAvailability->eq(0)->text();
        $productAvailabilityText = str_replace("Availability: ", "", $productAvailabilityText);

        //Check if product is out of stock
        $isAvailable = $productAvailabilityText == "Out of Stock" ? false : true;


        //If avaiblable extract shipping text + shipping date(if present)
        if(count($productAvailability) == 2){
            
            $shippingInformtion = $productAvailability->eq(1)->text();
            $shippingDate =  ScrapeHelper::getDate($shippingInformtion);
        } else {

            $shippingInformtion = null;
            $shippingDate = null;
        }

        

        $colors = $htmlElement->filter("span[data-colour]")->extract(['data-colour']);

        $productOptions = [];
        foreach ($colors as $color) {
            $productOptions[] = [
                'title' => $productTitle,
                'price' => $price,
                'imageUrl' => $imageUrl,
                'capacityMB' => $capacityInMB,
                'colour' => $color,
                'availabilityText' => $productAvailabilityText,
                'isAvailable' => $isAvailable,
                'shippingText' => $shippingInformtion,
                'shippingDate' => $shippingDate,
            ];
        }
        
        return $productOptions;
    }

}