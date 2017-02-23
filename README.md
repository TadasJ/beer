# Beer Helicopter

Test assignment for DataDog by Tadas Juščius

## Task description
Given any valid lat/long as a starting point, plot out a route to retrieve as many beer samples from breweries as possible.
Your magic perfect helicopter has enough fuel to travel 2000km. Use [this repository](https://github.com/brewdega/open-beer-database-dumps) for
beer types and brewery locations.

##Implementation
Search is performed using a simple greedy algorithm. The search area is expanded from the starting position gradually,
 until one or more breweries are found. The brewery with the most beers is selected as the new destination, and search
 continues from there, taking into account the remaining fuel.
 
 The search ends, when no other breweries can be reached that also leave enough fuel for a return to home (initial lat/long).
 At this point the results (visited breweries and beers collected) are displayed.
 
##Possible improvements
A better heuristic for brewery selection could be devised. Instead of just selecting a brewery with the most beers, other nearby
breweries could also be inspected. For example, a brewery with 5 beer types that is close is a worse option than a brewery that has
4 more breweries in its proximity, but is farther away.

Additionally, calculation results for distances between breweries could be stored into database, for faster retrieval in future
searches.

## Requirements to run

* a mysql server
* PHP 5.6 or higher
* composer

## Installation

Composer:

    composer install
    
Run database migrations:
    
    app/console doctrine:migrations:migrate

Import data from CSV to database:

    app/console app:import-data
    
## Usage

Find beers for a given example lat/long:

    app/console app:get-beer --lat=51.355468 --long=11.10790
    
If no lat/long parameters are provided, coordinates for DataDog HQ will be used.