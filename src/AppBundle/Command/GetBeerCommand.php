<?php

namespace AppBundle\Command;

use AppBundle\Entity\Beer;
use AppBundle\Entity\Brewery;
use AppBundle\Entity\Geocode;
use AppBundle\Service\DistanceService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetBeerCommand extends ContainerAwareCommand
{

    /** @var  DistanceService */
    private $distanceService;

    private $homeLat;
    private $homeLong;

    protected function configure()
    {
        $this
            ->setName('app:get-beer')
            ->setDescription('Finds nearby beer')
            ->setHelp("This command attempts to find as much beer as possible within 2000km of given lat/long.")
            ->addOption(
                'lat',
                null,
                InputOption::VALUE_REQUIRED,
                'Home base latitude coordinate',
                54.898157
            )
            ->addOption(
                'long',
                null,
                InputOption::VALUE_REQUIRED,
                'Home base longitude coordinate',
                23.886641
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startTime = microtime(true);
        $this->distanceService = $this->get('distance_service');

        $this->homeLat = $input->getOption('lat');
        $this->homeLong = $input->getOption('long');

        if(!is_numeric($this->homeLat) || !is_numeric($this->homeLong)) {
            $output->writeln('Please enter valid lat and long');
            return;
        }

        $fuel = 2000;
        $breweriesVisited = [];

        $breweryRoute = $this->recursiveSearch($this->homeLat, $this->homeLong, $fuel, $breweriesVisited);
        $this->drawPrettyOutput($output, $breweryRoute);

        $executionTime = (microtime(true) - $startTime) * 1000;
        $output->writeln("\n\nProgram took: ".$executionTime."ms");
    }

    /**
     * Uses a recursive greedy search to find breweries with the most beer in ever expanding squares from the given lat/long
     *
     * @param $lat
     * @param $long
     * @param $fuel
     * @param $breweriesVisited
     * @return array
     */
    private function recursiveSearch($lat, $long, $fuel, &$breweriesVisited)
    {
        $breweries = [];
        $recursiveBreweries = [];

        //cycle determines size of search area, start and increment values chosen by manual tweaking for what seems
        //to return the best results
        for($i = $fuel/2; $i < $fuel; $i += $fuel/4) {
            $searchSpace = $this->distanceService->getPointsWithinDistance($lat, $long, $i);

            if(!$searchSpace) {
                continue;
            }

            //find next best brewery from all breweries within range
            //if brewery found, continue recursive search from new location
            $newBrewery = $this->findNextBrewery($searchSpace, $lat, $long, $fuel, $breweriesVisited);
            if($newBrewery) {
                $breweries[] = $newBrewery;
                $recursiveBreweries = $this->recursiveSearch($lat, $long, $fuel, $breweriesVisited);
            }
            $breweries = array_merge($breweries, $recursiveBreweries);
            //current location already found best brewery within range, no need to check other options
            break;
        }

        return $breweries;
    }

    /**
     * Picks out the brewery with the most beers, that still leaves enough fuel for a trip home
     *
     * @param $searchSpace
     * @param $lat
     * @param $long
     * @param $fuel
     * @param $breweriesVisited
     * @return array
     */
    private function findNextBrewery($searchSpace, &$lat, &$long, &$fuel, &$breweriesVisited)
    {
        $brewery = null;

        foreach($searchSpace as $index => $geocode) {
            if(!$brewery) {
                $oldBeers = 0;
            } else {
                $oldBeers = count($brewery->getBeers());
            }
            $newBeers = count($geocode->getBrewery()->getBeers());

            if($newBeers > $oldBeers && !in_array($geocode->getBrewery()->getId(), $breweriesVisited)) {
                $newLat = $geocode->getLatitude();
                $newLong = $geocode->getLongitude();

                $distanceToBrewery = $this->distanceService->getHaversineDistance(
                    $newLat,
                    $newLong,
                    $lat,
                    $long
                );
                $distanceToHomeFromBrewery = $this->distanceService->getHaversineDistance(
                    $newLat,
                    $newLong,
                    $this->homeLat,
                    $this->homeLong
                );
                $newFuel = $fuel - $distanceToBrewery - $distanceToHomeFromBrewery;

                if($newFuel < 0) {
                    continue;
                }

                $brewery = $geocode->getBrewery();
            }
        }

        if($brewery) {
            //Next destination is set, update all parameters
            $newLat = $brewery->getGeocode()->getLatitude();
            $newLong = $brewery->getGeocode()->getLongitude();
            $distanceToBrewery = $this->distanceService->getHaversineDistance(
                $newLat,
                $newLong,
                $lat,
                $long
            );
            $lat = $newLat;
            $long = $newLong;
            $fuel = $fuel - $distanceToBrewery;
            $breweriesVisited[] = $brewery->getId();

            return ['brewery' => $brewery, 'distance' => $distanceToBrewery];
        } else {
            return [];
        }
    }

    private function drawPrettyOutput($output, $breweryRoute)
    {
        $beers = [];
        $count = count($breweryRoute);
        $totalDistance = 0;

        if(!$count) {
            $output->writeln("No breweries were in range :(");
        } else {
            $output->writeln("Found ".$count." breweries:");
            $output->writeln("\t-> HOME: ".$this->homeLat.", ".$this->homeLong." distance 0km");
            foreach($breweryRoute as $breweryItem) {
                $brewery = $breweryItem['brewery'];
                $lat = $brewery->getGeocode()->getLatitude();
                $long = $brewery->getGeocode()->getLongitude();
                $output->writeln("\t-> [".$brewery->getId()."] ".$brewery->getName().": ".$lat.", ".$long." distance ".$breweryItem['distance']."km");
                $totalDistance += $breweryItem['distance'];
                foreach($brewery->getBeers() as $beer) {
                    $beers[] = $beer->getName();
                }
            }
            $homeDistance = $this->distanceService->getHaversineDistance($lat, $long, $this->homeLat, $this->homeLong);
            $totalDistance += $homeDistance;
            $output->writeln("\t<- HOME: ".$this->homeLat.", ".$this->homeLong." distance ".$homeDistance."km");
            $output->writeln("\nTotal distance travelled: ".$totalDistance."km");
            $output->writeln("\n\nCollected ".count($beers)." beer types:");
            foreach($beers as $beer) {
                $output->writeln("\t-> ".$beer);
            }
        }
    }

    private function get($name)
    {
        return $this->getContainer()->get($name);
    }
}